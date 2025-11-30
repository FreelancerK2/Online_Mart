<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shop Page - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Admin Header -->
    <div class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="admin.php" class="text-white hover:text-blue-100">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">Manage Shop Page</h1>
                        <p class="text-blue-100 text-sm">Edit vendors, best sellers, and new arrivals</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="index.php?page=shop" target="_blank" class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-semibold flex items-center gap-2">
                        <i class="fas fa-eye"></i>
                        <span>Preview</span>
                    </a>
                    <a href="admin.php" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors font-semibold">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Hero Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-store text-blue-600"></i>
                            Shop Hero Section
                        </h2>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                    </div>
                    <p class="text-gray-600 mb-4">Manage the shop hero banner and welcome message</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">Welcome message and link to view all products</p>
                    </div>
                </div>

                <!-- Vendors Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-users text-blue-600"></i>
                            Vendors Section
                        </h2>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                    </div>
                    <p class="text-gray-600 mb-4">Manage vendor cards and vendor information</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">8 vendors: NestFood, Stouffer, Old El Paso, Foster, Yoplait, Tasty, Country Crock, Canada Dry</p>
                    </div>
                </div>

                <!-- Best Sellers Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-trophy text-yellow-600"></i>
                            Best Sellers
                        </h2>
                        <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                    </div>
                    <p class="text-gray-600 mb-4">Manage best seller products displayed on the shop page</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">6 best seller products with vendor filtering</p>
                    </div>
                </div>

                <!-- New Arrivals Section -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-star text-green-600"></i>
                            New Arrivals
                        </h2>
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                    </div>
                    <p class="text-gray-600 mb-4">Manage new arrival products with vendor filtering</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">3 new arrival products with vendor filtering</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="index.php?page=shop" target="_blank" class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center font-semibold">
                            <i class="fas fa-eye mr-2"></i>View Shop Page
                        </a>
                        <button class="w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-center font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </div>

                <!-- Page Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Page Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">File:</span>
                            <span class="font-semibold">shop.php</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sections:</span>
                            <span class="font-semibold">4</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated:</span>
                            <span class="font-semibold"><?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

