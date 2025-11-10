<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Inventory health metrics
$avgPriceResult = mysqli_query($conn, "SELECT AVG(price) AS avg_price FROM products WHERE price > 0");
$averagePrice = 0;
if ($avgPriceResult) {
    $averagePrice = (float) (mysqli_fetch_assoc($avgPriceResult)['avg_price'] ?? 0);
    mysqli_free_result($avgPriceResult);
}

$inventoryValueResult = mysqli_query($conn, "SELECT SUM(price * quantity) AS total_value FROM products");
$inventoryValue = 0;
if ($inventoryValueResult) {
    $inventoryValue = (float) (mysqli_fetch_assoc($inventoryValueResult)['total_value'] ?? 0);
    mysqli_free_result($inventoryValueResult);
}

$lowStockResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE quantity < 10");
$lowStockCount = 0;
if ($lowStockResult) {
    $lowStockCount = (int) (mysqli_fetch_assoc($lowStockResult)['total'] ?? 0);
    mysqli_free_result($lowStockResult);
}

$outOfStockResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE quantity = 0");
$outOfStockCount = 0;
if ($outOfStockResult) {
    $outOfStockCount = (int) (mysqli_fetch_assoc($outOfStockResult)['total'] ?? 0);
    mysqli_free_result($outOfStockResult);
}

$discountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE discount_percentage > 0");
$discountProducts = 0;
if ($discountResult) {
    $discountProducts = (int) (mysqli_fetch_assoc($discountResult)['total'] ?? 0);
    mysqli_free_result($discountResult);
}

$lowStockProducts = [];
$lowStockProductsQuery = "SELECT id, name, quantity, price, image_path FROM products WHERE quantity > 0 AND quantity < 10 ORDER BY quantity ASC LIMIT 20";
$lowStockProductsResult = mysqli_query($conn, $lowStockProductsQuery);
if ($lowStockProductsResult) {
    while ($row = mysqli_fetch_assoc($lowStockProductsResult)) {
        $lowStockProducts[] = $row;
    }
    mysqli_free_result($lowStockProductsResult);
}

$outOfStockProducts = [];
$outOfStockProductsQuery = "SELECT id, name, quantity, price, image_path FROM products WHERE quantity = 0 ORDER BY id DESC LIMIT 20";
$outOfStockProductsResult = mysqli_query($conn, $outOfStockProductsQuery);
if ($outOfStockProductsResult) {
    while ($row = mysqli_fetch_assoc($outOfStockProductsResult)) {
        $outOfStockProducts[] = $row;
    }
    mysqli_free_result($outOfStockProductsResult);
}

// Top products by quantity
$topProductsQuery = "SELECT name, quantity, price FROM products ORDER BY quantity DESC LIMIT 10";
$topProductsResult = mysqli_query($conn, $topProductsQuery);

// Recent products (last 10)
$recentProductsQuery = "SELECT name, created_at FROM products ORDER BY created_at DESC LIMIT 10";
$recentProductsResult = mysqli_query($conn, $recentProductsQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard - Mini Mart</title>
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
                <a href="admin.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin-orders.php" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-receipt w-5"></i>
                    <span>Orders</span>
                </a>
                <a href="admin-reports.php" class="nav-item active flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
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
                        <h1 class="text-3xl font-bold text-white mb-2">Reports & Analytics</h1>
                        <p class="text-gray-400">Comprehensive statistics and insights for your store.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="index.php" class="px-4 py-2 border border-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-external-link-alt"></i>
                            <span>View Store</span>
                        </a>
                        <button onclick="window.print()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors flex items-center gap-2">
                            <i class="fas fa-print"></i>
                            <span>Print Report</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Health Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card card rounded-xl p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Low Stock Items</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($lowStockCount); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Quantity &lt; 10 units</p>
                        </div>
                        <div class="bg-orange-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-exclamation-triangle text-orange-500 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card card rounded-xl p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Out of Stock</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($outOfStockCount); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Requires immediate restock</p>
                        </div>
                        <div class="bg-red-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card card rounded-xl p-6 border-l-4 border-pink-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Products on Sale</p>
                            <p class="text-3xl font-bold text-white"><?php echo number_format($discountProducts); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Discount percentage &gt; 0</p>
                        </div>
                        <div class="bg-pink-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-percent text-pink-500 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card card rounded-xl p-6 border-l-4 border-cyan-500">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">Average Price</p>
                            <p class="text-3xl font-bold text-white">$<?php echo number_format($averagePrice, 2); ?></p>
                            <p class="text-gray-400 text-xs mt-1">Across all active products</p>
                        </div>
                        <div class="bg-cyan-500 bg-opacity-20 rounded-xl p-4">
                            <i class="fas fa-dollar-sign text-cyan-500 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-xl p-6 mb-10">
                <div class="flex items-center gap-4">
                    <div class="bg-green-500 bg-opacity-20 rounded-xl p-6">
                        <i class="fas fa-warehouse text-green-500 text-4xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm font-medium mb-1">Inventory Value</p>
                        <p class="text-4xl font-bold text-white">$<?php echo number_format($inventoryValue, 2); ?></p>
                        <p class="text-gray-400 text-xs mt-1">Calculated from price × quantity for each product</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <div class="card rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-white">Low Stock Items</h2>
                            <p class="text-gray-400 text-sm">Quantity below threshold &lt; 10 units</p>
                        </div>
                        <a href="admin-products.php?filter=low-stock" class="text-xs uppercase tracking-wide text-green-400 hover:text-green-300">Manage</a>
                    </div>
                    <div class="divide-y divide-slate-700">
                        <?php if (!empty($lowStockProducts)): ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <div class="flex items-center justify-between py-3 gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-800 flex items-center justify-center">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\'fas fa-box text-slate-500\'></i>'">
                                            <?php else: ?>
                                                <i class="fas fa-box text-slate-500"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($product['name']); ?></p>
                                            <p class="text-xs text-gray-400">ID #<?php echo (int)$product['id']; ?> &middot; $<?php echo number_format($product['price'], 2); ?></p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-semibold">Qty <?php echo (int)$product['quantity']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-400 py-6">All stocked items are healthy right now.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-white">Out of Stock</h2>
                            <p class="text-gray-400 text-sm">Products requiring immediate restock</p>
                        </div>
                        <a href="admin-products.php?filter=out-of-stock" class="text-xs uppercase tracking-wide text-green-400 hover:text-green-300">Manage</a>
                    </div>
                    <div class="divide-y divide-slate-700">
                        <?php if (!empty($outOfStockProducts)): ?>
                            <?php foreach ($outOfStockProducts as $product): ?>
                                <div class="flex items-center justify-between py-3 gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-800 flex items-center justify-center">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\'fas fa-box-open text-slate-500\'></i>'">
                                            <?php else: ?>
                                                <i class="fas fa-box-open text-slate-500"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($product['name']); ?></p>
                                            <p class="text-xs text-gray-400">ID #<?php echo (int)$product['id']; ?> &middot; $<?php echo number_format($product['price'], 2); ?></p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-red-500/20 text-red-300 text-xs font-semibold">Qty 0</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-400 py-6">Great news! Nothing is out of stock.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top Products and Recent Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Top Products by Quantity -->
                <div class="card rounded-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Top Products by Stock</h2>
                    <div class="space-y-3">
                        <?php
                        if (mysqli_num_rows($topProductsResult) > 0) {
                            $rank = 1;
                            while ($product = mysqli_fetch_assoc($topProductsResult)) {
                                echo '<div class="flex items-center justify-between p-3 bg-slate-800 rounded-lg">';
                                echo '<div class="flex items-center gap-3">';
                                echo '<span class="text-gray-400 font-bold">#' . $rank . '</span>';
                                echo '<div>';
                                echo '<p class="text-white font-semibold">' . htmlspecialchars($product['name']) . '</p>';
                                echo '<p class="text-gray-400 text-xs">$' . number_format($product['price'], 2) . '</p>';
                                echo '</div>';
                                echo '</div>';
                                echo '<span class="text-green-500 font-bold">' . number_format($product['quantity']) . ' units</span>';
                                echo '</div>';
                                $rank++;
                            }
                        } else {
                            echo '<p class="text-gray-400 text-center py-4">No products found.</p>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="card rounded-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Recent Products</h2>
                    <div class="space-y-3">
                        <?php
                        if (mysqli_num_rows($recentProductsResult) > 0) {
                            while ($product = mysqli_fetch_assoc($recentProductsResult)) {
                                $date = date('M d, Y', strtotime($product['created_at']));
                                echo '<div class="flex items-center justify-between p-3 bg-slate-800 rounded-lg">';
                                echo '<div>';
                                echo '<p class="text-white font-semibold">' . htmlspecialchars($product['name']) . '</p>';
                                echo '<p class="text-gray-400 text-xs">' . $date . '</p>';
                                echo '</div>';
                                echo '<i class="fas fa-arrow-right text-gray-400"></i>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-gray-400 text-center py-4">No recent products found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>

