<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
$paymentOptions = ['unpaid', 'paid', 'refunded'];

$message = '';
$messageType = '';

// Orders overview stats
$ordersTotal = 0;
$ordersActive = 0;
$ordersPending = 0;
$ordersGrossRevenue = 0.0;
$ordersPaidRevenue = 0.0;

$orderStatsQuery = "SELECT 
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS active_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
        SUM(total_amount) AS gross_revenue,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) AS paid_revenue
    FROM orders";
$orderStatsResult = mysqli_query($conn, $orderStatsQuery);
if ($orderStatsResult) {
    $statsRow = mysqli_fetch_assoc($orderStatsResult);
    if ($statsRow) {
        $ordersTotal = (int) ($statsRow['total_orders'] ?? 0);
        $ordersActive = (int) ($statsRow['active_orders'] ?? 0);
        $ordersPending = (int) ($statsRow['pending_orders'] ?? 0);
        $ordersGrossRevenue = (float) ($statsRow['gross_revenue'] ?? 0);
        $ordersPaidRevenue = (float) ($statsRow['paid_revenue'] ?? 0);
    }
    mysqli_free_result($orderStatsResult);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $newPayment = $_POST['payment_status'] ?? '';

    if ($orderId > 0 && in_array($newStatus, $statusOptions, true) && in_array($newPayment, $paymentOptions, true)) {
        mysqli_begin_transaction($conn);

        try {
            $currentStatusStmt = mysqli_prepare($conn, "SELECT status FROM orders WHERE id = ? FOR UPDATE");
            if (!$currentStatusStmt) {
                throw new Exception('Unable to fetch current order status.');
            }

            mysqli_stmt_bind_param($currentStatusStmt, 'i', $orderId);
            mysqli_stmt_execute($currentStatusStmt);
            mysqli_stmt_bind_result($currentStatusStmt, $currentStatus);

            if (!mysqli_stmt_fetch($currentStatusStmt)) {
                mysqli_stmt_close($currentStatusStmt);
                throw new Exception('Order not found.');
            }
            mysqli_stmt_close($currentStatusStmt);

            $shouldReduceStock = ($newStatus === 'shipped' && $currentStatus !== 'shipped');
            $stockAdjusted = false;

            $updateStmt = mysqli_prepare($conn, "UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
            if (!$updateStmt) {
                throw new Exception('Unable to prepare the update statement.');
            }

            mysqli_stmt_bind_param($updateStmt, 'ssi', $newStatus, $newPayment, $orderId);
            if (!mysqli_stmt_execute($updateStmt)) {
                mysqli_stmt_close($updateStmt);
                throw new Exception('Failed to update order. Please try again.');
            }
            mysqli_stmt_close($updateStmt);

            if ($shouldReduceStock) {
                $itemsStmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                if (!$itemsStmt) {
                    throw new Exception('Unable to fetch order items for inventory update.');
                }

                mysqli_stmt_bind_param($itemsStmt, 'i', $orderId);
                mysqli_stmt_execute($itemsStmt);
                $itemsResult = mysqli_stmt_get_result($itemsStmt);

                $items = [];
                if ($itemsResult) {
                    while ($row = mysqli_fetch_assoc($itemsResult)) {
                        $items[] = $row;
                    }
                    mysqli_free_result($itemsResult);
                }
                mysqli_stmt_close($itemsStmt);

                if (!empty($items)) {
                    $stockStmt = mysqli_prepare($conn, "UPDATE products SET quantity = CASE WHEN quantity >= ? THEN quantity - ? ELSE 0 END WHERE id = ?");
                    if (!$stockStmt) {
                        throw new Exception('Unable to prepare inventory adjustment statement.');
                    }

                    $qtyParam = 0;
                    $qtyParamDuplicate = 0;
                    $productIdParam = 0;
                    mysqli_stmt_bind_param($stockStmt, 'iii', $qtyParam, $qtyParamDuplicate, $productIdParam);

                    foreach ($items as $item) {
                        $qtyParam = (int) ($item['quantity'] ?? 0);
                        if ($qtyParam <= 0) {
                            continue;
                        }
                        $qtyParamDuplicate = $qtyParam;
                        $productIdParam = (int) ($item['product_id'] ?? 0);

                        if ($productIdParam <= 0) {
                            continue;
                        }

                        if (!mysqli_stmt_execute($stockStmt)) {
                            mysqli_stmt_close($stockStmt);
                            throw new Exception('Failed to adjust product inventory.');
                        }
                    }

                    mysqli_stmt_close($stockStmt);
                    $stockAdjusted = true;
                }
            }

            mysqli_commit($conn);

            $message = "Order #{$orderId} updated successfully.";
            if ($stockAdjusted) {
                $message .= ' Inventory adjusted for shipment.';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $message = $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid order or status selection.';
        $messageType = 'error';
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$paymentFilter = $_GET['payment'] ?? 'all';

$whereClauses = [];

if ($statusFilter !== 'all' && in_array($statusFilter, $statusOptions, true)) {
    $escaped = mysqli_real_escape_string($conn, $statusFilter);
    $whereClauses[] = "o.status = '{$escaped}'";
}

if ($paymentFilter !== 'all' && in_array($paymentFilter, $paymentOptions, true)) {
    $escaped = mysqli_real_escape_string($conn, $paymentFilter);
    $whereClauses[] = "o.payment_status = '{$escaped}'";
}

$filterSql = '';
if (!empty($whereClauses)) {
    $filterSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

$orders = [];
$ordersQuery = "SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id {$filterSql} ORDER BY o.created_at DESC";
$ordersResult = mysqli_query($conn, $ordersQuery);
if ($ordersResult) {
    while ($row = mysqli_fetch_assoc($ordersResult)) {
        $orders[] = $row;
    }
    mysqli_free_result($ordersResult);
}

$orderItemsMap = [];
if (!empty($orders)) {
    $orderIds = array_map(function ($order) {
        return (int) $order['id'];
    }, $orders);
    $idList = implode(',', array_map('intval', $orderIds));
    $itemsQuery = "SELECT oi.*, p.name, p.image_path FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ({$idList})";
    $itemsResult = mysqli_query($conn, $itemsQuery);
    if ($itemsResult) {
        while ($row = mysqli_fetch_assoc($itemsResult)) {
            $orderItemsMap[$row['order_id']][] = $row;
        }
        mysqli_free_result($itemsResult);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Dashboard - Mini Mart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0F172A;
        }
        .sidebar {
            background-color: #1E293B;
        }
        .card {
            background-color: #1E293B;
            border: 1px solid #334155;
        }
        .nav-item:hover {
            background-color: #334155;
        }
        .nav-item.active {
            background-color: #22C55E;
            color: white;
        }
    </style>
</head>
<body class="min-h-screen text-white">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-64 min-h-screen fixed left-0 top-0 p-6">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-green-500 bg-slate-900/60 flex items-center justify-center">
                        <img src="image/logo.webp" alt="Mini Mart Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Mini Mart</h1>
                        <p class="text-gray-400 text-xs">Admin Panel</p>
                    </div>
                </div>
                <div class="relative">
                    <input type="text" placeholder="Search for..." class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 pl-10 text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <nav class="space-y-2">
                <a href="admin.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin-orders.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <i class="fas fa-receipt w-5"></i>
                    <span>Orders</span>
                </a>
                <a href="admin-reports.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Reports</span>
                </a>
                <a href="admin-categories.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-tags w-5"></i>
                    <span>Categories</span>
                </a>
                <a href="admin-products.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-box w-5"></i>
                    <span>Products</span>
                </a>
                <a href="admin-vendors.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-truck w-5"></i>
                    <span>Vendors</span>
                </a>

                <div class="pt-4 mt-4 border-t border-slate-700">
                    <p class="text-gray-400 text-xs font-semibold uppercase px-4 mb-2">Features</p>
                    <a href="admin-home.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <i class="fas fa-home w-5"></i>
                        <span>Home Page</span>
                    </a>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-700">
                    <p class="text-gray-400 text-xs font-semibold uppercase px-4 mb-2">Settings</p>
                    <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                        <i class="fas fa-cog w-5"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </nav>

            <div class="absolute bottom-6 left-6 right-6">
                <div class="card rounded-lg p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-green-500 bg-slate-900 flex items-center justify-center">
                            <img src="image/logo.webp" alt="Admin Avatar" class="w-full h-full object-contain">
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-semibold text-sm"><?php echo htmlspecialchars($_SESSION['admin']); ?></p>
                            <p class="text-gray-400 text-xs">Admin Account</p>
                        </div>
                    </div>
                    <a href="?logout=1" class="w-full border border-red-500 text-red-500 hover:text-red-600 hover:bg-red-500/10 py-2 rounded-lg transition-colors font-medium flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Order Management</h1>
                        <p class="text-gray-400">Review orders, check order history, and update fulfilment states.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="admin.php" class="px-4 py-2 border border-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-emerald-500 bg-opacity-20 border border-emerald-500' : 'bg-red-500 bg-opacity-20 border border-red-500'; ?>">
                    <p class="text-white text-sm"><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Orders Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card card rounded-xl p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Active Orders</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($ordersActive); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Pending or processing</p>
                        </div>
                        <div class="bg-blue-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-truck-loading text-blue-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card card rounded-xl p-6 border-l-4 border-amber-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Pending Orders</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($ordersPending); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Awaiting confirmation</p>
                        </div>
                        <div class="bg-amber-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-clock text-amber-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card card rounded-xl p-6 border-l-4 border-emerald-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Paid Revenue</p>
                            <p class="text-3xl font-bold text-white">$<?php echo number_format($ordersPaidRevenue, 2); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Payments captured</p>
                        </div>
                        <div class="bg-emerald-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-credit-card text-emerald-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card card rounded-xl p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Total Orders</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($ordersTotal); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Gross revenue $<?php echo number_format($ordersGrossRevenue, 2); ?></p>
                        </div>
                        <div class="bg-indigo-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-receipt text-indigo-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-xl p-6 mb-6">
                <form method="GET" class="flex flex-col md:flex-row gap-4 md:items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Order Status</label>
                        <select name="status" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                            <?php foreach ($statusOptions as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Payment Status</label>
                        <select name="payment" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                            <option value="all" <?php echo $paymentFilter === 'all' ? 'selected' : ''; ?>>All payments</option>
                            <?php foreach ($paymentOptions as $payment): ?>
                                <option value="<?php echo $payment; ?>" <?php echo $paymentFilter === $payment ? 'selected' : ''; ?>><?php echo ucfirst($payment); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="px-5 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors text-sm font-semibold">Apply Filters</button>
                    </div>
                </form>
            </div>

            <?php if (empty($orders)): ?>
                <div class="card rounded-xl p-6 text-gray-400">No orders found for the selected criteria.</div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($orders as $order): ?>
                        <div class="card rounded-xl p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-white">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                                    <p class="text-gray-400 text-sm">Placed on <?php echo date('M d, Y \a\t h:i A', strtotime($order['created_at'])); ?><?php echo $order['username'] ? ' by ' . htmlspecialchars($order['username']) : ' (Guest checkout)'; ?></p>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 text-sm">
                                    <span class="px-3 py-1 rounded-full <?php echo match($order['status']) {
                                        'pending' => 'bg-amber-500/20 text-amber-300',
                                        'processing' => 'bg-blue-500/20 text-blue-300',
                                        'shipped' => 'bg-indigo-500/20 text-indigo-300',
                                        'delivered' => 'bg-emerald-500/20 text-emerald-300',
                                        'cancelled' => 'bg-red-500/20 text-red-300',
                                        default => 'bg-slate-700/60 text-slate-300'
                                    }; ?>">Status: <?php echo ucfirst($order['status']); ?></span>
                                    <span class="px-3 py-1 rounded-full <?php echo $order['payment_status'] === 'paid' ? 'bg-emerald-500/20 text-emerald-300' : ($order['payment_status'] === 'refunded' ? 'bg-slate-500/20 text-slate-300' : 'bg-amber-500/20 text-amber-300'); ?>">Payment: <?php echo ucfirst($order['payment_status']); ?></span>
                                    <span class="px-3 py-1 rounded-full bg-slate-700/60 text-gray-200">Total: $<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2">
                                    <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wide">Items</h3>
                                    <div class="bg-slate-800/60 rounded-lg divide-y divide-slate-700">
                                        <?php if (!empty($orderItemsMap[$order['id']])): ?>
                                            <?php foreach ($orderItemsMap[$order['id']] as $item): ?>
                                                <div class="flex items-center justify-between px-4 py-3 gap-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-700 flex items-center justify-center">
                                                            <?php if (!empty($item['image_path'])): ?>
                                                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\'fas fa-box text-slate-500\'></i>'">
                                                            <?php else: ?>
                                                                <i class="fas fa-box text-slate-500"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <p class="text-white font-medium text-sm"><?php echo htmlspecialchars($item['name'] ?? 'Unknown Product'); ?></p>
                                                            <p class="text-gray-400 text-xs">Qty: <?php echo (int) $item['quantity']; ?> &middot; $<?php echo number_format($item['price'], 2); ?> each</p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right text-gray-200 font-semibold">$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="px-4 py-3 text-gray-400 text-sm">No items recorded for this order.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wide">Update Order</h3>
                                    <form method="POST" class="space-y-4">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <div class="flex-1">
                                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Order Status</label>
                                                <select name="status" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                                    <?php foreach ($statusOptions as $status): ?>
                                                        <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Payment Status</label>
                                                <select name="payment_status" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                                    <?php foreach ($paymentOptions as $payment): ?>
                                                        <option value="<?php echo $payment; ?>" <?php echo $order['payment_status'] === $payment ? 'selected' : ''; ?>><?php echo ucfirst($payment); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-3">
                                            <a href="invoice.php?order_id=<?php echo (int)$order['id']; ?>" target="_blank" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-center text-white rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                                                <i class="fas fa-file-invoice"></i>
                                                <span>View Invoice</span>
                                            </a>
                                            <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg text-sm font-semibold transition-colors">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

