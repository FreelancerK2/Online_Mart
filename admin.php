<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Store-wide statistics
// Total products
$productsResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
$totalProducts = 0;
if ($productsResult) {
    $totalProducts = (int) (mysqli_fetch_assoc($productsResult)['total'] ?? 0);
    mysqli_free_result($productsResult);
}

// Total categories
$categoriesResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
$totalCategories = 0;
if ($categoriesResult) {
    $totalCategories = (int) (mysqli_fetch_assoc($categoriesResult)['total'] ?? 0);
    mysqli_free_result($categoriesResult);
}

// Total vendors
$vendorsResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM vendors");
$totalVendors = 0;
if ($vendorsResult) {
    $totalVendors = (int) (mysqli_fetch_assoc($vendorsResult)['total'] ?? 0);
    mysqli_free_result($vendorsResult);
}

// Registered users
$usersResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalUsers = 0;
if ($usersResult) {
    $totalUsers = (int) (mysqli_fetch_assoc($usersResult)['total'] ?? 0);
    mysqli_free_result($usersResult);
}

// Orders statistics
$orderStatsQuery = "SELECT 
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS active_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
        SUM(total_amount) AS gross_revenue,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) AS paid_revenue
    FROM orders";
$orderStatsResult = mysqli_query($conn, $orderStatsQuery);
$totalOrders = 0;
$activeOrders = 0;
$pendingOrders = 0;
$grossRevenue = 0.0;
$paidRevenue = 0.0;
if ($orderStatsResult) {
    $statsRow = mysqli_fetch_assoc($orderStatsResult);
    $totalOrders = (int) ($statsRow['total_orders'] ?? 0);
    $activeOrders = (int) ($statsRow['active_orders'] ?? 0);
    $pendingOrders = (int) ($statsRow['pending_orders'] ?? 0);
    $grossRevenue = (float) ($statsRow['gross_revenue'] ?? 0);
    $paidRevenue = (float) ($statsRow['paid_revenue'] ?? 0);
    mysqli_free_result($orderStatsResult);
}

// Recent orders
$recentOrders = [];
$recentOrdersQuery = "SELECT o.id, o.order_number, o.status, o.payment_status, o.total_amount, o.created_at, u.username,
                             p.image_path AS first_product_image, p.name AS first_product_name
                      FROM orders o
                      LEFT JOIN users u ON o.user_id = u.id
                      LEFT JOIN order_items oi ON oi.id = (
                          SELECT oi2.id FROM order_items oi2 WHERE oi2.order_id = o.id ORDER BY oi2.id ASC LIMIT 1
                      )
                      LEFT JOIN products p ON p.id = oi.product_id
                      ORDER BY o.created_at DESC
                      LIMIT 6";
$recentOrdersResult = mysqli_query($conn, $recentOrdersQuery);
if ($recentOrdersResult) {
    while ($row = mysqli_fetch_assoc($recentOrdersResult)) {
        $recentOrders[] = $row;
    }
    mysqli_free_result($recentOrdersResult);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mini Mart</title>
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
        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(34, 197, 94, 0.2);
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
            <!-- Logo -->
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

            <!-- Navigation -->
            <nav class="space-y-2">
                <a href="admin.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin-orders.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
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

            <!-- User Profile -->
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
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['admin']); ?></h1>
                        <p class="text-gray-400">Measure your website performance and manage your content.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="index.php" class="px-4 py-2 border border-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-external-link-alt"></i>
                            <span>View Store</span>
                        </a>
                        <button class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors flex items-center gap-2">
                            <i class="fas fa-plus"></i>
                            <span>Create Report</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Products -->
                <div class="stat-card card rounded-xl p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Total Products</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($totalProducts); ?></p>
                        </div>
                        <div class="bg-green-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-box text-green-500 text-2xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs">Current catalog size</p>
                </div>

                <!-- Total Categories -->
                <div class="stat-card card rounded-xl p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Product Categories</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($totalCategories); ?></p>
                        </div>
                        <div class="bg-green-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-layer-group text-green-500 text-2xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs">Active categories in the store</p>
                </div>

                <!-- Total Vendors -->
                <div class="stat-card card rounded-xl p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Vendors</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($totalVendors); ?></p>
                        </div>
                        <div class="bg-green-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-truck text-green-500 text-2xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs">Suppliers contributing inventory</p>
                </div>

                <!-- Registered Users -->
                <div class="stat-card card rounded-xl p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Registered Customers</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($totalUsers); ?></p>
                        </div>
                        <div class="bg-green-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-users text-green-500 text-2xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-500 text-xs">Accounts created in the store</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card rounded-xl p-6 mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">Latest Orders</h2>
                    <a href="admin-orders.php" class="text-sm text-green-400 hover:text-green-300 transition-colors">View all orders &rsaquo;</a>
                </div>
                <?php if (!empty($recentOrders)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700 text-sm">
                            <thead class="bg-slate-800/60 text-gray-300 uppercase tracking-wide text-xs">
                                <tr>
                                    <th class="px-4 py-3 text-left">Order</th>
                                    <th class="px-4 py-3 text-left">Customer</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Payment</th>
                                    <th class="px-4 py-3 text-right">Placed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700 text-gray-200">
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-md overflow-hidden bg-slate-800 flex items-center justify-center">
                                                    <?php if (!empty($order['first_product_image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($order['first_product_image']); ?>" alt="Product" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\'fas fa-box text-slate-500\'></i>'">
                                                    <?php else: ?>
                                                        <i class="fas fa-box text-slate-500"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-xs text-gray-400 uppercase tracking-wide">#<?php echo htmlspecialchars($order['order_number']); ?></p>
                                                    <p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($order['first_product_name'] ?? 'Order'); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
                                        <td class="px-4 py-3 text-right">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs <?php echo match($order['status']) {
                                                'pending' => 'bg-amber-500/20 text-amber-300',
                                                'processing' => 'bg-blue-500/20 text-blue-300',
                                                'shipped' => 'bg-indigo-500/20 text-indigo-300',
                                                'delivered' => 'bg-emerald-500/20 text-emerald-300',
                                                'cancelled' => 'bg-red-500/20 text-red-300',
                                                default => 'bg-slate-700/60 text-slate-300'
                                            }; ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs <?php echo $order['payment_status'] === 'paid' ? 'bg-emerald-500/20 text-emerald-300' : ($order['payment_status'] === 'refunded' ? 'bg-slate-500/20 text-slate-300' : 'bg-amber-500/20 text-amber-300'); ?>"><?php echo ucfirst($order['payment_status']); ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-400"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400 text-sm">No orders have been placed yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
