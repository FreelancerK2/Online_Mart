<?php
session_start();
include('config.php');
include('nav-gradient.php');
$isAdmin = isset($_SESSION['admin']);
$isUser = isset($_SESSION['user_id']);
$currentUser = $isAdmin ? $_SESSION['admin'] : ($isUser ? $_SESSION['username'] : 'Guest User');
$currentEmail = $isAdmin ? ($_SESSION['admin'] . '@admin.com') : ($isUser ? $_SESSION['user_email'] : 'guest@example.com');
$currentFirstName = $isUser ? ($_SESSION['user_first_name'] ?? 'Guest') : ($isAdmin ? $_SESSION['admin'] : 'Guest');
$currentLastName = $isUser ? ($_SESSION['user_last_name'] ?? 'User') : ($isAdmin ? 'Admin' : 'User');

if (!function_exists('js_str')) {
    function js_str($value) {
        return htmlspecialchars(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    }
}
?>

<!-- Main Header -->
<div class="fixed top-0 left-0 right-0 z-50 backdrop-blur-xl" style="background: transparent;">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-center">
            <!-- Navigation Menu Bar - Pill Style -->
            <div class="flex justify-center">
                <div class="bg-gray-900 rounded-full px-6 py-3 shadow-lg flex items-center gap-8 max-w-4xl w-full">
                    <!-- Logo Icon in Circular Green Element -->
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-green-600 border border-green-700 flex items-center justify-center overflow-hidden">
                            <img src="image/logo.webp" alt="Mini Mart" class="h-8 w-8 object-contain">
                        </div>
                    </div>
                    
                    <!-- Browse All Categories Button -->
                    <div class="relative flex-shrink-0">
                        <button id="categories-toggle" onclick="toggleCategoriesMenu()" class="text-white hover:text-green-300 transition-colors font-medium text-sm whitespace-nowrap">
                            Browse All Categories
                            <i id="categories-chevron" class="fas fa-chevron-down ml-1 text-xs transition-transform"></i>
                        </button>
                        <!-- Categories Dropdown -->
                        <div id="categories-dropdown" class="absolute top-full left-0 mt-2 backdrop-blur-xl text-white rounded-lg shadow-lg border border-gray-700/30 py-2 min-w-[250px] hidden z-[60]" style="background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
                            <?php
                            // Fetch categories from database
                            $categoriesQuery = "SELECT * FROM categories ORDER BY name ASC";
                            $categoriesResult = mysqli_query($conn, $categoriesQuery);
                            
                            if (mysqli_num_rows($categoriesResult) > 0) {
                                while ($cat = mysqli_fetch_assoc($categoriesResult)) {
                                    $iconClass = $cat['icon'] ? $cat['icon'] : 'fas fa-tag';
                                    echo '<a href="index.php?page=all-products&category=' . urlencode($cat['id']) . '" class="flex items-center gap-3 px-4 py-2 hover:bg-white/10 transition-colors text-white">';
                                    if (!empty($cat['image_path'])) {
                                        echo '<img src="' . htmlspecialchars($cat['image_path']) . '" alt="' . htmlspecialchars($cat['name']) . '" class="w-8 h-8 object-cover rounded" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-block\';"><i class="' . htmlspecialchars($iconClass) . ' text-green-400 w-5" style="display:none;"></i>';
                                    } else {
                                        echo '<i class="' . htmlspecialchars($iconClass) . ' text-green-400 w-5"></i>';
                                    }
                                    echo '<span>' . htmlspecialchars($cat['name']) . '</span>';
                                    echo '</a>';
                                }
                            } else {
                                echo '<div class="px-4 py-2 text-gray-400 text-sm">No categories available</div>';
                            }
                            ?>
                        </div>
            </div>
            
                    <!-- Navigation Links -->
                    <div class="flex items-center gap-6 flex-1">
                        <a href="index.php?page=home" class="text-white hover:text-green-300 transition-colors font-medium text-sm relative group">
                            Hot Deals
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        <a href="index.php?page=all-products" class="text-white hover:text-green-300 transition-colors font-medium text-sm relative group">
                            Home
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        <a href="index.php?page=about" class="text-white hover:text-green-300 transition-colors font-medium text-sm relative group">
                            About
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        <a href="index.php?page=shop" class="text-white hover:text-green-300 transition-colors font-medium text-sm relative group">
                            Shop
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        <a href="index.php?page=contact" class="text-white hover:text-green-300 transition-colors font-medium text-sm relative group">
                            Contact
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-green-400 transition-all duration-300 group-hover:w-full"></span>
                        </a>
                    </div>
                    
                    <!-- Search Button -->
                    <div class="relative flex-shrink-0">
                        <button onclick="toggleSearchPanel()" class="text-white hover:text-green-300 transition-colors font-medium text-lg">
                            <i class="fas fa-search"></i>
                        </button>
                        <!-- Search Dropdown Panel -->
                        <div id="search-panel" class="absolute top-full right-0 mt-3 min-w-[500px] max-w-[600px] hidden z-50 transform transition-all duration-300 ease-out opacity-0 scale-95 translate-y-[-10px]">
                            <form onsubmit="handleSearchSubmit(event)">
                                <div class="relative">
                                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300 pointer-events-none transition-colors z-10"></i>
                                    <input id="search-input" type="text" placeholder="Search products, brands, categories..." class="w-full pl-12 pr-16 py-3.5 backdrop-blur-xl border-2 border-gray-700/30 rounded-xl focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all duration-300 text-white placeholder-gray-400 font-medium shadow-lg" style="background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(48px); -webkit-backdrop-filter: blur(48px);" autocomplete="off" oninput="showSearchSuggestions(this.value)" onfocus="showSearchSuggestions(this.value)" onblur="setTimeout(() => hideSearchSuggestions(), 200)">
                                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white w-10 h-10 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center justify-center z-10">
                                        <i class="fas fa-search text-base"></i>
                                    </button>
                                        <!-- Search Suggestions Dropdown -->
                                    <div id="search-suggestions" class="absolute top-full left-0 right-0 mt-2 backdrop-blur-xl rounded-xl shadow-xl border border-gray-700/30 max-h-[300px] overflow-y-auto z-50 hidden" style="background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
                                            <div id="suggestions-list" class="py-2">
                                                <!-- Suggestions will be dynamically populated here -->
                                            </div>
                                        </div>
                                    </div>
                                </form>
                        </div>
                    </div>
                    
                    <!-- Contact/Email Box -->
                    <div class="flex-shrink-0">
                        <a href="index.php?page=contact" class="bg-white border border-gray-300 rounded-full px-4 py-2 text-gray-900 hover:bg-gray-50 transition-colors font-medium text-sm whitespace-nowrap">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
            
<!-- Right Side Actions - Fixed Position -->
<div class="fixed top-9 right-8 z-50 flex items-center gap-7">
                <!-- Compare, Wishlist, Cart Group -->
    <div class="flex items-center gap-4">
                    <button onclick="toggleCompareModal()" class="text-gray-600 hover:text-green-600 transition-colors relative group">
                        <i class="fas fa-balance-scale text-xl"></i>
                        <span id="compare-count" class="absolute -top-2 -right-2 bg-green-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Compare</span>
                    </button>
                    <button onclick="toggleWishlistModal()" class="text-gray-600 hover:text-green-600 transition-colors relative group">
                        <i class="far fa-heart text-xl"></i>
                        <span id="wishlist-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Wishlist</span>
                    </button>
                    <button onclick="toggleCartModal()" class="text-gray-600 hover:text-green-600 transition-colors relative group">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Cart</span>
                    </button>
                </div>
                
                <!-- Divider -->
                <div class="h-8 w-px bg-gray-300"></div>
                
                <!-- User Account and Language Group -->
                <div class="flex items-center gap-4">
                    <?php if ($isAdmin || $isUser): ?>
                    <button id="user-menu-button" onclick="toggleUserMenu()" class="text-gray-600 hover:text-green-600 transition-colors relative group">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center overflow-hidden">
                            <i id="header-user-icon" class="fas fa-user-circle text-xl"></i>
                            <img id="header-user-photo" src="" alt="User Photo" class="w-full h-full object-cover hidden rounded-full">
                        </div>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Account</span>
                    </button>
                    <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-green-600 transition-colors relative group">
                        <i class="fas fa-sign-in-alt text-xl"></i>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Login</span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Language Selector -->
                        <button id="language-button" class="cursor-pointer hover:opacity-80 transition-opacity duration-200 flex items-center" onclick="toggleLanguageDropdown(event)">
                            <img src="image/UK.png" alt="Language" class="w-7 h-5 object-contain rounded-sm" id="language-flag" style="min-width: 28px; min-height: 20px;">
                        </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Language Dropdown Menu -->
<div id="language-dropdown" class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-[120px] opacity-0 invisible transition-all duration-200" style="display: none; z-index: 9999;">
    <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition-colors" onclick="changeLanguage('UK.png'); return false;">
        <img src="image/UK.png" alt="English" class="w-7 h-5 object-contain rounded-sm" style="min-width: 28px; min-height: 20px;">
        <span class="text-sm text-gray-700">English</span>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition-colors" onclick="changeLanguage('KH.png'); return false;">
        <img src="image/KH.png" alt="Khmer" class="w-7 h-5 object-contain rounded-sm" style="min-width: 28px; min-height: 20px;">
        <span class="text-sm text-gray-700">ខ្មែរ</span>
    </a>
</div>

<!-- User Dropdown Menu -->
    <div id="user-dropdown" class="fixed bg-white/40 backdrop-blur-xl rounded-lg shadow-lg border border-gray-200/20 py-3 min-w-[220px] opacity-0 invisible transition-all duration-200" style="display: none; z-index: 9999;">
        <div class="px-4 py-3 border-b border-gray-200/30 bg-transparent backdrop-blur-md rounded-t-lg">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center overflow-hidden">
                    <i id="dropdown-user-icon" class="fas fa-user text-green-600 text-xl"></i>
                    <img id="dropdown-user-photo" src="" alt="User Photo" class="w-full h-full object-cover hidden rounded-full">
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($currentUser); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($currentEmail); ?></p>
                </div>
            </div>
        </div>
        <div class="py-2 bg-transparent">
            <button onclick="openProfileModal()" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-white/50 transition-colors text-left">
                <i class="fas fa-user text-gray-500 w-5"></i>
                <span>My Profile</span>
            </button>
            <?php if ($isAdmin): ?>
            <a href="admin.php" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-white/50 transition-colors text-left">
                <i class="fas fa-tachometer-alt text-green-600 w-5"></i>
                <span>Access Dashboard</span>
            </a>
            <?php endif; ?>
            <button onclick="toggleWishlistModal(); toggleUserMenu();" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-white/50 transition-colors text-left">
                <i class="far fa-heart text-gray-500 w-5"></i>
                <span>Wishlist</span>
            </button>
            <button onclick="openAddressesModal()" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-white/50 transition-colors text-left">
                <i class="fas fa-map-marker-alt text-gray-500 w-5"></i>
                <span>Addresses</span>
            </button>
            <button onclick="openSettingsModal()" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-white/50 transition-colors text-left">
                <i class="fas fa-cog text-gray-500 w-5"></i>
                <span>Settings</span>
            </button>
            <div class="border-t border-gray-200/30 my-2"></div>
            <button onclick="handleLogout()" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-500/20 transition-colors text-left">
                <i class="fas fa-sign-out-alt text-red-500 w-5"></i>
                <span>Logout</span>
            </button>
        </div>
    </div>
</div>

<!-- Page content spacing below fixed header+nav -->
<div class="pt-[100px]"></div>

<!-- Hero -->
<section class="relative overflow-hidden">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="rounded-2xl bg-gradient-to-br from-green-50 to-emerald-100 border border-emerald-200/50 p-10 flex flex-col lg:flex-row items-center gap-10">
            <div class="flex-1">
                <p class="text-green-700 font-semibold mb-2">About Mini Mart</p>
                <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight mb-4">Freshness, Fair Prices, and Fast Delivery</h1>
                <p class="text-gray-600 text-lg">We’re on a mission to make quality groceries accessible to everyone, anywhere. Shop thousands of products with transparent pricing and delightful service.</p>
                <div class="mt-6 flex gap-3">
                    <a href="index.php?page=all-products" class="px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">Start Shopping</a>
                    <a href="index.php?page=contact" class="px-5 py-3 bg-white border border-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-50">Contact Us</a>
                </div>
            </div>
            <div class="flex-1 grid grid-cols-2 gap-4 w-full">
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl font-bold text-green-700">10k+</div>
                    <p class="text-gray-500">Happy Customers</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl font-bold text-green-700">2k+</div>
                    <p class="text-gray-500">Products</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl font-bold text-green-700">99.9%</div>
                    <p class="text-gray-500">On-time Delivery</p>
                </div>
                <div class="p-6 bg-white rounded-xl shadow-sm border">
                    <div class="text-3xl font-bold text-green-700">24/7</div>
                    <p class="text-gray-500">Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section class="py-14">
    <div class="max-w-screen-2xl mx-auto px-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Our Values</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center mb-4"><i class="fas fa-leaf"></i></div>
                <h3 class="font-semibold text-gray-800 mb-2">Fresh & Sustainable</h3>
                <p class="text-gray-600">We work with trusted suppliers to deliver fresh, high‑quality produce with minimal waste.</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center mb-4"><i class="fas fa-tags"></i></div>
                <h3 class="font-semibold text-gray-800 mb-2">Honest Pricing</h3>
                <p class="text-gray-600">Transparent prices and regular deals so you always get the best value.</p>
            </div>
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center mb-4"><i class="fas fa-truck"></i></div>
                <h3 class="font-semibold text-gray-800 mb-2">Fast Delivery</h3>
                <p class="text-gray-600">Reliable delivery windows and updates—so groceries arrive when you need them.</p>
            </div>
        </div>
    </div>
    </section>

<!-- Team / CTA -->
<section class="pb-16">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="rounded-2xl bg-white border shadow-sm p-8 flex flex-col md:flex-row items-center gap-8">
            <div class="flex-1">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Built by a small team who cares</h3>
                <p class="text-gray-600">From engineering to fulfillment, our team is focused on one thing: a delightful shopping experience.</p>
            </div>
            <a href="index.php?page=all-products" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">Browse Products</a>
        </div>
    </div>
</section>

<!-- Compare Modal -->
<div id="compare-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'compare-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Compare Products</h2>
            <button onclick="toggleCompareModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div id="compare-items" class="space-y-4">
                <p class="text-gray-500 text-center py-8">No products to compare yet.</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-4">
            <button onclick="toggleCompareModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Wishlist Modal -->
<div id="wishlist-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'wishlist-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">My Wishlist</h2>
            <button onclick="toggleWishlistModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div id="wishlist-items" class="space-y-4">
                <p class="text-gray-500 text-center py-8">Your wishlist is empty.</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end">
            <button onclick="toggleWishlistModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Cart Modal -->
<div id="cart-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'cart-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Shopping Cart</h2>
            <button onclick="toggleCartModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div id="cart-items" class="space-y-4">
                <p class="text-gray-500 text-center py-8">Your cart is empty.</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-600">Total:</p>
                <p id="cart-total" class="text-2xl font-bold text-gray-800">$0.00</p>
            </div>
            <div class="flex gap-4">
                <button onclick="clearCart()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Clear Cart</button>
                <button onclick="checkout()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Checkout</button>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'logout-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">Confirm Logout</h2>
            <p class="text-gray-600 text-center mb-6">Are you sure you want to logout? You will need to log in again to access your account.</p>
            <div class="flex justify-end gap-4">
                <button onclick="closeLogoutModal()" class="px-6 py-2.5 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmLogout()" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Logout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div id="profile-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'profile-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">My Profile</h2>
            <button onclick="closeModal('profile-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div class="space-y-6">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div id="profile-photo-container" class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center overflow-hidden">
                            <i id="profile-photo-icon" class="fas fa-user text-green-600 text-4xl"></i>
                            <img id="profile-photo-preview" src="" alt="Profile Photo" class="w-full h-full object-cover hidden">
                        </div>
                        <input type="file" id="profile-photo-input" accept="image/*" class="hidden" onchange="handlePhotoUpload(event)">
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($currentUser); ?></h3>
                        <p class="text-gray-500"><?php echo htmlspecialchars($currentEmail); ?></p>
                        <button onclick="document.getElementById('profile-photo-input').click()" class="mt-2 text-green-600 hover:text-green-700 text-sm font-medium cursor-pointer">Change Photo</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($currentFirstName); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($currentLastName); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($currentEmail); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" placeholder="Enter phone number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-4">
            <button onclick="closeModal('profile-modal')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
            <button onclick="saveProfile()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Save Changes</button>
        </div>
    </div>
</div>

<!-- Addresses Modal -->
<div id="addresses-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'addresses-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">My Addresses</h2>
            <button onclick="closeModal('addresses-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div id="addresses-list" class="space-y-4">
                <!-- Addresses will be dynamically loaded here -->
            </div>
            <button onclick="openAddressForm()" class="w-full border-2 border-dashed border-gray-300 rounded-lg p-6 text-gray-500 hover:border-green-600 hover:text-green-600 transition-colors mt-4">
                <i class="fas fa-plus text-2xl mb-2"></i>
                <p class="font-medium">Add New Address</p>
            </button>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end">
            <button onclick="closeModal('addresses-modal')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Address Form Modal -->
<div id="address-form-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'address-form-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 id="address-form-title" class="text-2xl font-bold text-gray-800">Add New Address</h2>
            <button onclick="closeAddressForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <form id="address-form" onsubmit="saveAddress(event)" class="space-y-4">
                <input type="hidden" id="address-id" name="id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address Label</label>
                    <input type="text" id="address-label" name="label" placeholder="e.g., Home, Office" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <input type="text" id="address-street" name="street" placeholder="123 Main Street" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <input type="text" id="address-city" name="city" placeholder="City" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                        <input type="text" id="address-state" name="state" placeholder="State" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ZIP/Postal Code</label>
                        <input type="text" id="address-zip" name="zip" placeholder="12345" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <input type="text" id="address-country" name="country" placeholder="United States" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="address-default" name="isDefault" class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        <span class="text-sm text-gray-700">Set as default address</span>
                    </label>
                </div>
                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" onclick="closeAddressForm()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="change-password-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'change-password-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Change Password</h2>
            <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="password-form" onsubmit="updatePassword(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="current-password" name="currentPassword" placeholder="Enter current password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="new-password" name="newPassword" placeholder="Enter new password" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="confirm-password" name="confirmPassword" placeholder="Confirm new password" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>
                
                <div id="password-error" class="hidden text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="error-message"></span>
                </div>
                
                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" onclick="closePasswordModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div id="settings-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'settings-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Settings</h2>
            <button onclick="closeModal('settings-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Notifications</h3>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-gray-700">Email Notifications</span>
                            <input type="checkbox" checked class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-gray-700">SMS Notifications</span>
                            <input type="checkbox" class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-gray-700">Order Updates</span>
                            <input type="checkbox" checked class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Security</h3>
                    <div class="space-y-4">
                        <button onclick="changePassword()" class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-left">
                            <i class="fas fa-key text-gray-500 mr-3"></i>
                            Change Password
                        </button>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Privacy</h3>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-gray-700">Show Profile to Public</span>
                            <input type="checkbox" checked class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-gray-700">Allow Product Recommendations</span>
                            <input type="checkbox" checked class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-4">
            <button onclick="closeModal('settings-modal')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
            <button onclick="saveSettings()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Save Settings</button>
        </div>
    </div>
</div>

<script>
// Toggle user menu dropdown
function toggleUserMenu() {
    const dropdown = document.getElementById('user-dropdown');
    const userButton = document.getElementById('user-menu-button');
    
    if (dropdown && userButton) {
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            // Calculate position relative to user button (fixed positioning uses viewport coordinates)
            const buttonRect = userButton.getBoundingClientRect();
            const dropdownTop = buttonRect.bottom + 8; // 8px gap (mt-2)
            const dropdownRight = window.innerWidth - buttonRect.right;
            
            dropdown.style.top = dropdownTop + 'px';
            dropdown.style.right = dropdownRight + 'px';
            dropdown.style.display = 'block';
            dropdown.classList.remove('opacity-0', 'invisible');
            dropdown.classList.add('opacity-100', 'visible');
        } else {
            dropdown.style.display = 'none';
            dropdown.classList.remove('opacity-100', 'visible');
            dropdown.classList.add('opacity-0', 'invisible');
        }
    }
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userButton = event.target.closest('#user-menu-button');
    const dropdown = document.getElementById('user-dropdown');
    
    if (!userButton && dropdown && dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    }
});

// Language dropdown functions
function showLanguageDropdown() {
    const dropdown = document.getElementById('language-dropdown');
    const languageButton = document.getElementById('language-button');
    
    if (dropdown && languageButton) {
        // Calculate position relative to language button (fixed positioning uses viewport coordinates)
        const buttonRect = languageButton.getBoundingClientRect();
        const dropdownTop = buttonRect.bottom + 8; // 8px gap (mt-2)
        const dropdownRight = window.innerWidth - buttonRect.right;
        
        dropdown.style.top = dropdownTop + 'px';
        dropdown.style.right = dropdownRight + 'px';
        dropdown.style.display = 'block';
        dropdown.classList.remove('opacity-0', 'invisible');
        dropdown.classList.add('opacity-100', 'visible');
    }
}

function toggleLanguageDropdown(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const dropdown = document.getElementById('language-dropdown');
    if (!dropdown) return;
    const isVisible = dropdown.style.display === 'block' || dropdown.style.opacity === '1';
    if (isVisible) {
        hideLanguageDropdown();
    } else {
        showLanguageDropdown();
    }
}

function hideLanguageDropdown() {
    const dropdown = document.getElementById('language-dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    }
}

function changeLanguage(flagImage) {
    // Update the flag image if needed
    const flagImg = document.getElementById('language-flag');
    if (flagImg) {
        flagImg.src = 'image/' + flagImage;
    }
    
    // Store the selected language in localStorage
    localStorage.setItem('selectedLanguage', flagImage);
    
    // Hide dropdown after selection
    hideLanguageDropdown();
    
    console.log('Language changed to: ' + flagImage);
}

// Load saved language preference on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedLanguage = localStorage.getItem('selectedLanguage');
    const flagImg = document.getElementById('language-flag');
    if (savedLanguage && flagImg) {
        flagImg.src = 'image/' + savedLanguage;
    }
});

// Close language dropdown when clicking outside
document.addEventListener('click', function(event) {
    const languageButton = event.target.closest('#language-button');
    const dropdown = document.getElementById('language-dropdown');
    
    if (!languageButton && dropdown && dropdown.style.display === 'block') {
        hideLanguageDropdown();
    }
});

// Compare Modal Functions (placeholder, overridden below with shared logic)
function toggleCompareModal() {
    const modal = document.getElementById('compare-modal');
    if (modal) {
        modal.classList.toggle('hidden');
    }
}

// Wishlist Modal Functions (placeholder, overridden below with shared logic)
function toggleWishlistModal() {
    const modal = document.getElementById('wishlist-modal');
    if (modal) {
        modal.classList.toggle('hidden');
    }
}

// Profile Modal Functions
function openProfileModal() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    }
    const modal = document.getElementById('profile-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function saveProfile() {
    alert('Profile updated successfully!');
    closeModal('profile-modal');
}

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size must be less than 5MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const photoData = e.target.result;
            const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
            localStorage.setItem('profilePhoto_' + userId, photoData);
            updateUserIcons(photoData);
            alert('Photo updated successfully!');
        };
        reader.readAsDataURL(file);
    }
}

function updateUserIcons(photoData) {
    const profilePreview = document.getElementById('profile-photo-preview');
    const profileIcon = document.getElementById('profile-photo-icon');
    if (profilePreview && profileIcon) {
        profilePreview.src = photoData;
        profilePreview.classList.remove('hidden');
        profileIcon.classList.add('hidden');
    }
    const headerPhoto = document.getElementById('header-user-photo');
    const headerIcon = document.getElementById('header-user-icon');
    if (headerPhoto && headerIcon) {
        headerPhoto.src = photoData;
        headerPhoto.classList.remove('hidden');
        headerIcon.classList.add('hidden');
    }
    const dropdownPhoto = document.getElementById('dropdown-user-photo');
    const dropdownIcon = document.getElementById('dropdown-user-icon');
    if (dropdownPhoto && dropdownIcon) {
        dropdownPhoto.src = photoData;
        dropdownPhoto.classList.remove('hidden');
        dropdownIcon.classList.add('hidden');
    }
}

// Addresses Modal Functions
let addresses = JSON.parse(localStorage.getItem('addresses')) || [];

function openAddressesModal() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    }
    const modal = document.getElementById('addresses-modal');
    if (modal) {
        modal.classList.remove('hidden');
        loadAddresses();
    }
}

function loadAddresses() {
    const container = document.getElementById('addresses-list');
    if (!container) return;
    if (addresses.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No addresses saved yet.</p>';
        return;
    }
    container.innerHTML = addresses.map(address => `
        <div class="border border-gray-200 rounded-lg p-4 ${address.isDefault ? 'border-green-500 bg-green-50' : ''}">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-gray-800">${address.label}</p>
                        ${address.isDefault ? '<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-medium">Default</span>' : ''}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">${address.street}<br>${address.city}, ${address.state} ${address.zip}<br>${address.country}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="editAddress('${address.id}')" class="text-green-600 hover:text-green-700 text-sm transition-colors">Edit</button>
                    <button onclick="deleteAddress('${address.id}')" class="text-red-600 hover:text-red-700 text-sm transition-colors">Delete</button>
                </div>
            </div>
            ${!address.isDefault ? `<button onclick="setDefaultAddress('${address.id}')" class="w-full mt-3 px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors text-sm">Set as Default</button>` : ''}
        </div>
    `).join('');
}

function openAddressForm(addressId = null) {
    const modal = document.getElementById('address-form-modal');
    const title = document.getElementById('address-form-title');
    if (modal && title) {
        if (addressId) {
            title.textContent = 'Edit Address';
            const address = addresses.find(a => a.id === addressId);
            if (address) {
                document.getElementById('address-id').value = address.id;
                document.getElementById('address-label').value = address.label;
                document.getElementById('address-street').value = address.street;
                document.getElementById('address-city').value = address.city;
                document.getElementById('address-state').value = address.state;
                document.getElementById('address-zip').value = address.zip;
                document.getElementById('address-country').value = address.country;
                document.getElementById('address-default').checked = address.isDefault;
            }
        } else {
            title.textContent = 'Add New Address';
            document.getElementById('address-form').reset();
            document.getElementById('address-id').value = '';
        }
        modal.classList.remove('hidden');
    }
}

function closeAddressForm() {
    const modal = document.getElementById('address-form-modal');
    if (modal) {
        modal.classList.add('hidden');
        const form = document.getElementById('address-form');
        if (form) form.reset();
    }
}

function saveAddress(event) {
    event.preventDefault();
    const addressData = {
        id: document.getElementById('address-id').value || Date.now().toString(),
        label: document.getElementById('address-label').value,
        street: document.getElementById('address-street').value,
        city: document.getElementById('address-city').value,
        state: document.getElementById('address-state').value,
        zip: document.getElementById('address-zip').value,
        country: document.getElementById('address-country').value,
        isDefault: document.getElementById('address-default').checked
    };
    if (addressData.isDefault) {
        addresses.forEach(addr => {
            if (addr.id !== addressData.id) {
                addr.isDefault = false;
            }
        });
    }
    const existingIndex = addresses.findIndex(a => a.id === addressData.id);
    if (existingIndex !== -1) {
        addresses[existingIndex] = addressData;
        alert('Address updated successfully!');
    } else {
        addresses.push(addressData);
        alert('Address added successfully!');
    }
    localStorage.setItem('addresses', JSON.stringify(addresses));
    closeAddressForm();
    loadAddresses();
}

function editAddress(addressId) {
    openAddressForm(addressId);
}

function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        addresses = addresses.filter(a => a.id !== addressId);
        localStorage.setItem('addresses', JSON.stringify(addresses));
        loadAddresses();
        alert('Address deleted successfully!');
    }
}

function setDefaultAddress(addressId) {
    addresses.forEach(addr => {
        addr.isDefault = (addr.id === addressId);
    });
    localStorage.setItem('addresses', JSON.stringify(addresses));
    loadAddresses();
    alert('Default address updated!');
}

// Settings Modal Functions
function openSettingsModal() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        dropdown.classList.remove('opacity-100', 'visible');
        dropdown.classList.add('opacity-0', 'invisible');
    }
    const modal = document.getElementById('settings-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function saveSettings() {
    alert('Settings saved successfully!');
    closeModal('settings-modal');
}

function changePassword() {
    const modal = document.getElementById('change-password-modal');
    const form = document.getElementById('password-form');
    const errorDiv = document.getElementById('password-error');
    
    if (modal && form) {
        form.reset();
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        modal.classList.remove('hidden');
    }
}

function closePasswordModal() {
    const modal = document.getElementById('change-password-modal');
    const form = document.getElementById('password-form');
    const errorDiv = document.getElementById('password-error');
    
    if (modal) {
        modal.classList.add('hidden');
        if (form) {
            form.reset();
        }
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
    }
}

function updatePassword(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const errorDiv = document.getElementById('password-error');
    const errorMessage = document.getElementById('error-message');
    
    // Hide error initially
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
    
    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        if (errorMessage) {
            errorMessage.textContent = 'All fields are required.';
        }
        if (errorDiv) {
            errorDiv.classList.remove('hidden');
        }
        return;
    }
    
    if (newPassword.length < 6) {
        if (errorMessage) {
            errorMessage.textContent = 'New password must be at least 6 characters long.';
        }
        if (errorDiv) {
            errorDiv.classList.remove('hidden');
        }
        return;
    }
    
    if (newPassword !== confirmPassword) {
        if (errorMessage) {
            errorMessage.textContent = 'New password and confirm password do not match.';
        }
        if (errorDiv) {
            errorDiv.classList.remove('hidden');
        }
        return;
    }
    
    if (currentPassword === newPassword) {
        if (errorMessage) {
            errorMessage.textContent = 'New password must be different from current password.';
        }
        if (errorDiv) {
            errorDiv.classList.remove('hidden');
        }
        return;
    }
    
    // Here you would typically verify the current password with the server
    // For now, we'll use localStorage to simulate password storage
    // In a real application, you would make an API call to verify and update the password
    
    // Get user-specific password key
    const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
    const savedPassword = localStorage.getItem('userPassword_' + userId) || '';
    
    // If no saved password exists, allow setting initial password
    if (savedPassword && currentPassword !== savedPassword) {
        if (errorMessage) {
            errorMessage.textContent = 'Current password is incorrect.';
        }
        if (errorDiv) {
            errorDiv.classList.remove('hidden');
        }
        return;
    }
    
    // Save new password (in production, this would be sent to server)
    localStorage.setItem('userPassword_' + userId, newPassword);
    
    // Show success and close modal
    alert('Password changed successfully!');
    closePasswordModal();
}

// Logout Functions
function handleLogout() {
    // Close user menu if open
    toggleUserMenu();
    // Show logout confirmation modal
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function confirmLogout() {
    // Clear localStorage
    localStorage.removeItem('cart');
    localStorage.removeItem('wishlist');
    localStorage.removeItem('compareList');
    localStorage.removeItem('selectedLanguage');
    // Close modal
    closeLogoutModal();
    // Redirect to logout page
    window.location.href = 'logout.php';
}

// Modal helper functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

function closeModalOnBackdrop(event, modalId) {
    if (event.target.id === modalId || event.target.classList.contains('bg-black')) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }
}

// Load saved profile photo on page load
document.addEventListener('DOMContentLoaded', function() {
    const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
    const savedPhoto = localStorage.getItem('profilePhoto_' + userId);
    if (savedPhoto) {
        updateUserIcons(savedPhoto);
    }
});

function handleSearchSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('search-input');
    if (input && input.value.trim()) {
        window.location.href = `index.php?page=all-products&search=${encodeURIComponent(input.value.trim())}`;
    }
}

function toggleSearchPanel() {
    const searchPanel = document.getElementById('search-panel');
    if (searchPanel) {
        if (searchPanel.classList.contains('hidden')) {
            // Show panel with animation
            searchPanel.classList.remove('hidden');
            setTimeout(() => {
                searchPanel.classList.remove('opacity-0', 'scale-95', 'translate-y-[-10px]');
                searchPanel.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                // Focus on input
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            }, 10);
        } else {
            // Hide panel with animation
            searchPanel.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            searchPanel.classList.add('opacity-0', 'scale-95', 'translate-y-[-10px]');
            setTimeout(() => {
                searchPanel.classList.add('hidden');
            }, 300);
        }
        // Close categories dropdown if open
        const categoriesDropdown = document.getElementById('categories-dropdown');
        if (categoriesDropdown && !categoriesDropdown.classList.contains('hidden')) {
            categoriesDropdown.classList.add('hidden');
            const chevron = document.getElementById('categories-chevron');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }
}

function quickSearch(term) {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = term;
        handleSearchSubmit({ preventDefault: () => {} });
    }
}

// Search Suggestions - Generated from database
const searchSuggestionsData = <?php
    $suggestions = [];
    
    // Fetch products from database
    if (isset($conn)) {
        $productsQuery = "SELECT p.name, p.id, p.image_path, p.price, p.discount_price, p.discount_percentage, c.name as category_name, v.name as vendor_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         LEFT JOIN vendors v ON p.vendor_id = v.id 
                         ORDER BY p.name ASC 
                         LIMIT 50";
        $productsResult = mysqli_query($conn, $productsQuery);
        if ($productsResult) {
            while ($row = mysqli_fetch_assoc($productsResult)) {
                $suggestions[] = [
                    'name' => $row['name'],
                    'type' => 'product',
                    'id' => $row['id'],
                    'image' => $row['image_path'] ? $row['image_path'] : '',
                    'price' => floatval($row['price']),
                    'discount_price' => $row['discount_price'] ? floatval($row['discount_price']) : null,
                    'discount_percentage' => $row['discount_percentage'] ? floatval($row['discount_percentage']) : 0,
                    'category' => $row['category_name'] ? $row['category_name'] : '',
                    'brand' => $row['vendor_name'] ? $row['vendor_name'] : '',
                    'vendor_name' => $row['vendor_name'] ? $row['vendor_name'] : ''
                ];
            }
        }
        
        // Fetch categories from database
        $categoriesQuery = "SELECT name FROM categories ORDER BY name ASC LIMIT 20";
        $categoriesResult = mysqli_query($conn, $categoriesQuery);
        if ($categoriesResult) {
            while ($row = mysqli_fetch_assoc($categoriesResult)) {
                $suggestions[] = [
                    'name' => $row['name'],
                    'type' => 'category',
                    'category' => $row['name']
                ];
            }
        }
        
        // Fetch vendors from database
        $vendorsQuery = "SELECT name FROM vendors ORDER BY name ASC LIMIT 20";
        $vendorsResult = mysqli_query($conn, $vendorsQuery);
        if ($vendorsResult) {
            while ($row = mysqli_fetch_assoc($vendorsResult)) {
                $suggestions[] = [
                    'name' => $row['name'],
                    'type' => 'brand',
                    'brand' => $row['name']
                ];
            }
        }
    }
    
    // Output as JSON
    echo json_encode($suggestions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>;

function showSearchSuggestions(query) {
    const suggestionsContainer = document.getElementById('search-suggestions');
    const suggestionsList = document.getElementById('suggestions-list');
    
    if (!suggestionsContainer || !suggestionsList) return;
    
    if (!query || query.trim().length < 1) {
        suggestionsContainer.classList.add('hidden');
        return;
    }
    
    const queryLower = query.toLowerCase().trim();
    const filtered = searchSuggestionsData.filter(item => {
        const nameMatch = item.name.toLowerCase().includes(queryLower);
        const categoryMatch = item.category && item.category.toLowerCase().includes(queryLower);
        const brandMatch = item.brand && item.brand.toLowerCase().includes(queryLower);
        return nameMatch || categoryMatch || brandMatch;
    }).slice(0, 8); // Limit to 8 suggestions
    
    if (filtered.length === 0) {
        suggestionsContainer.classList.add('hidden');
        return;
    }
    
    suggestionsList.innerHTML = filtered.map(item => {
        if (item.type === 'product') {
            // Product suggestion with image, prices, and vendor
            const displayPrice = item.discount_price && item.discount_price < item.price ? item.discount_price : item.price;
            const hasDiscount = item.discount_price && item.discount_price < item.price;
            const imageHtml = item.image ? 
                `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<i class=\\'fas fa-box text-green-600\\'></i>';">` : 
                `<div class="w-full h-full bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center"><i class="fas fa-box text-green-600"></i></div>`;
            
            return `
                <button onclick="selectSuggestion('${item.name.replace(/'/g, "\\'")}')" class="w-full px-4 py-3 hover:bg-white/10 transition-colors text-left flex items-center gap-3 group text-white">
                    <div class="w-16 h-16 rounded-lg bg-gray-800 overflow-hidden flex-shrink-0">
                        ${imageHtml}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate mb-1">${item.name}</p>
                        ${item.vendor_name ? `<p class="text-xs text-gray-400 mb-1">By <span class="text-green-400 font-semibold">${item.vendor_name}</span></p>` : ''}
                        <div class="flex items-center gap-2">
                            ${hasDiscount ? 
                                `<span class="text-sm font-bold text-green-400">$${displayPrice.toFixed(2)}</span>
                                 <span class="text-xs text-gray-500 line-through">$${item.price.toFixed(2)}</span>
                                 ${item.discount_percentage > 0 ? `<span class="text-xs bg-red-500/80 text-white px-1.5 py-0.5 rounded">${Math.round(item.discount_percentage)}% OFF</span>` : ''}` :
                                `<span class="text-sm font-bold text-green-400">$${displayPrice.toFixed(2)}</span>`
                            }
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-green-400 transition-colors"></i>
                </button>
            `;
        } else if (item.type === 'category') {
            return `
                <button onclick="selectSuggestion('${item.name.replace(/'/g, "\\'")}')" class="w-full px-4 py-3 hover:bg-white/10 transition-colors text-left flex items-center gap-3 group text-white">
                    <div class="w-8 h-8 rounded-lg bg-gray-800 group-hover:bg-green-500/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-layer-group text-blue-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">${item.name}</p>
                        <span class="text-xs text-gray-400">Category</span>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-green-400 transition-colors"></i>
                </button>
            `;
        } else if (item.type === 'brand') {
        return `
            <button onclick="selectSuggestion('${item.name.replace(/'/g, "\\'")}')" class="w-full px-4 py-3 hover:bg-white/10 transition-colors text-left flex items-center gap-3 group text-white">
                <div class="w-8 h-8 rounded-lg bg-gray-800 group-hover:bg-green-500/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-tag text-purple-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">${item.name}</p>
                        <span class="text-xs text-gray-400">Brand</span>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-green-400 transition-colors"></i>
            </button>
        `;
        }
        return '';
    }).join('');
    
    suggestionsContainer.classList.remove('hidden');
}

function hideSearchSuggestions() {
    const suggestionsContainer = document.getElementById('search-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.classList.add('hidden');
    }
}

function selectSuggestion(term) {
    hideSearchSuggestions();
    // Navigate directly to all-products page with search term
    window.location.href = `index.php?page=all-products&search=${encodeURIComponent(term)}`;
}

// Close search panel when clicking outside
document.addEventListener('click', function(event) {
    const searchPanel = document.getElementById('search-panel');
    const searchButton = event.target.closest('button[onclick="toggleSearchPanel()"]');
    const suggestionsContainer = document.getElementById('search-suggestions');
    
    // Don't close if clicking on suggestions
    if (suggestionsContainer && suggestionsContainer.contains(event.target)) {
        return;
    }
    
    if (searchPanel && !searchPanel.contains(event.target) && !searchButton && !searchPanel.classList.contains('hidden')) {
        searchPanel.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        searchPanel.classList.add('opacity-0', 'scale-95', 'translate-y-[-10px]');
        setTimeout(() => {
            searchPanel.classList.add('hidden');
        }, 300);
    }
});

// Categories Dropdown Functions
function toggleCategoriesMenu() {
    const dropdown = document.getElementById('categories-dropdown');
    const chevron = document.getElementById('categories-chevron');
    
    if (dropdown && chevron) {
        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }
}

function filterByCategory(event, category) {
    // Prevent default navigation
    if (event) {
        event.preventDefault();
    }
    // Store the category filter in localStorage for the all-products page
    localStorage.setItem('selectedCategory', category);
    // Navigate to all-products page with category filter as URL parameter
    window.location.href = 'index.php?page=all-products&category=' + encodeURIComponent(category);
}

// Close categories menu when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('categories-dropdown');
    const toggle = document.getElementById('categories-toggle');
    
    if (dropdown && toggle && !dropdown.contains(event.target) && !toggle.contains(event.target)) {
        dropdown.classList.add('hidden');
        const chevron = document.getElementById('categories-chevron');
        if (chevron) chevron.classList.remove('rotate-180');
    }
});
</script>
<script>
(function () {
    var cart = Array.isArray(window.cart) ? window.cart : (window.cart = JSON.parse(localStorage.getItem('cart')) || []);
    var wishlist = Array.isArray(window.wishlist) ? window.wishlist : (window.wishlist = JSON.parse(localStorage.getItem('wishlist')) || []);
    var compareList = Array.isArray(window.compareList) ? window.compareList : (window.compareList = JSON.parse(localStorage.getItem('compareList')) || []);

    const saveCart = () => localStorage.setItem('cart', JSON.stringify(cart));
    const saveWishlist = () => localStorage.setItem('wishlist', JSON.stringify(wishlist));
    const saveCompare = () => localStorage.setItem('compareList', JSON.stringify(compareList));

    const fallbackImage = 'image/logo.webp';
    const resolveImage = (source, trigger) => {
        let resolved = '';

        if (typeof source === 'string') {
            resolved = source.trim();
        }

        if (typeof window.resolveProductImage === 'function') {
            resolved = window.resolveProductImage(resolved);
        }

        let button = trigger || null;
        if (!button) {
            const evt = window.event || null;
            if (evt && evt.target) {
                button = evt.target.closest('button');
            }
        }

        if (!resolved || resolved === fallbackImage) {
            if (button) {
                const buttonData = button.getAttribute('data-product-image');
                if (buttonData && buttonData.trim() !== '') {
                    resolved = buttonData.trim();
                }
            }

            if ((!resolved || resolved === fallbackImage) && button) {
                const cardWithData = button.closest('[data-product-image]');
                if (cardWithData && cardWithData.dataset.productImage && cardWithData.dataset.productImage.trim() !== '') {
                    resolved = cardWithData.dataset.productImage.trim();
                }
            }

            if ((!resolved || resolved === fallbackImage) && button) {
                const fallbackImg = button.closest('div')?.querySelector('img');
                if (fallbackImg && fallbackImg.src) {
                    resolved = fallbackImg.src;
                }
            }
        }

        return resolved && resolved.trim() !== '' ? resolved : fallbackImage;
    };

    const notify = (message, type = 'success') => {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else if (type === 'error') {
            console.error(message);
        } else {
            alert(message);
        }
    };

    const renderImage = (src, alt) => {
        if (src && src.trim() !== '') {
            return `<img src="${src}" alt="${alt}" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center\'><i class=\'fas fa-image text-gray-400\'></i></div>'">`;
        }
        return `<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400"></i></div>`;
    };

    window.toggleCartModal = function() {
        const modal = document.getElementById('cart-modal');
        if (modal) {
            modal.classList.toggle('hidden');
            window.updateCartDisplay();
        }
    };

    window.addToCart = function() {
        let trigger = null;
        let productId, productName, productPrice, productImage, quantity = 1;

        if (arguments.length && typeof arguments[0] === 'object' && arguments[0] !== null) {
            trigger = arguments[0];
            productId = arguments[1];
            productName = arguments[2];
            productPrice = arguments[3];
            productImage = arguments[4];
            quantity = arguments.length > 5 ? arguments[5] : 1;
        } else {
            productId = arguments[0];
            productName = arguments[1];
            productPrice = arguments[2];
            productImage = arguments[3];
            quantity = arguments.length > 4 ? arguments[4] : 1;
        }

        productImage = resolveImage(productImage, trigger);
        const existingItem = cart.find(item => item.id === productId);
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({ id: productId, name: productName, price: productPrice, image: productImage, quantity: quantity });
        }
        saveCart();
        window.updateCartCount();
        window.updateCartDisplay();
        notify(`${productName} added to cart`, 'success');
    };

    window.removeFromCart = function(productId) {
        cart = cart.filter(item => item.id !== productId);
        window.cart = cart;
        saveCart();
        window.updateCartCount();
        window.updateCartDisplay();
    };

    window.updateCartQuantity = function(productId, quantity) {
        const item = cart.find(item => item.id === productId);
        if (!item) return;
        item.quantity = parseInt(quantity, 10);
        if (item.quantity <= 0 || isNaN(item.quantity)) {
            window.removeFromCart(productId);
        } else {
            saveCart();
            window.updateCartCount();
            window.updateCartDisplay();
        }
    };

    window.clearCart = function() {
        cart = [];
        window.cart = cart;
        saveCart();
        window.updateCartCount();
        window.updateCartDisplay();
    };

    window.updateCartCount = function() {
        const badge = document.getElementById('cart-count');
        if (!badge) return;
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    };

    window.updateCartDisplay = function() {
        const container = document.getElementById('cart-items');
        const totalElement = document.getElementById('cart-total');
        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty.</p>';
            if (totalElement) totalElement.textContent = '$0.00';
            return;
        }

        cart = cart.map(item => {
            if (!item.image || typeof item.image !== 'string' || item.image.trim() === '') {
                return { ...item, image: fallbackImage };
            }
            return item;
        });
        window.cart = cart;
        saveCart();

        container.innerHTML = cart.map(item => {
            const price = parseFloat(item.price.replace('$', '').replace(',', ''));
            const subtotal = price * item.quantity;
            const imageHtml = renderImage(item.image, item.name);
            return `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                            ${imageHtml}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800">${item.name}</h3>
                            <p class="text-green-600 font-bold">${item.price}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="number" value="${item.quantity}" min="1" onchange="updateCartQuantity('${item.id}', this.value)" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                        <p class="text-gray-700 font-semibold w-20 text-right">$${subtotal.toFixed(2)}</p>
                        <button onclick="removeFromCart('${item.id}')" class="text-red-500 hover:text-red-700 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        if (totalElement) {
            const total = cart.reduce((sum, item) => {
                const price = parseFloat(item.price.replace('$', '').replace(',', ''));
                return sum + (price * item.quantity);
            }, 0);
            totalElement.textContent = '$' + total.toFixed(2);
        }
    };

    window.checkout = function() {
        if (cart.length === 0) {
            notify('Your cart is empty!', 'error');
            return;
        }
        window.toggleCartModal();
        window.location.href = 'index.php?page=checkout';
    };

    window.toggleWishlistModal = function() {
        const modal = document.getElementById('wishlist-modal');
        if (modal) {
            modal.classList.toggle('hidden');
            window.updateWishlistDisplay();
        }
    };

    window.toggleWishlist = function(productId, productName, productPrice, productImage, productBrand = '', originalPrice = '', inStock = true) {
        wishlist = Array.isArray(window.wishlist) ? window.wishlist : wishlist;
        productImage = resolveImage(productImage);

        const index = wishlist.findIndex(item => item.id === productId);
        if (index === -1) {
            wishlist.push({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
                brand: productBrand,
                original_price: originalPrice,
                in_stock: inStock
            });
            window.wishlist = wishlist;
            saveWishlist();

            const icon = document.querySelector('.wishlist-icon-' + productId);
            if (icon) {
                icon.classList.remove('far', 'text-gray-600');
                icon.classList.add('fas', 'text-red-500');
            }

            window.updateWishlistCount();
            notify('Added to wishlist!', 'success');
        } else {
            wishlist.splice(index, 1);
            window.wishlist = wishlist;
            saveWishlist();

            const icon = document.querySelector('.wishlist-icon-' + productId);
            if (icon) {
                icon.classList.remove('fas', 'text-red-500');
                icon.classList.add('far', 'text-gray-600');
            }

            window.updateWishlistCount();
            notify('Removed from wishlist!', 'info');
        }

        window.updateWishlistDisplay();
    };

    window.addToWishlist = function(productId, productName, productPrice, productImage) {
        window.toggleWishlist(productId, productName, productPrice, productImage);
    };

    window.removeFromWishlist = function(productId) {
        wishlist = (Array.isArray(window.wishlist) ? window.wishlist : wishlist).filter(item => item.id !== productId);
        window.wishlist = wishlist;
        saveWishlist();

        const icon = document.querySelector('.wishlist-icon-' + productId);
        if (icon) {
            icon.classList.remove('fas', 'text-red-500');
            icon.classList.add('far', 'text-gray-600');
        }

        window.updateWishlistCount();
        window.updateWishlistDisplay();
    };

    window.updateWishlistCount = function() {
        const badge = document.getElementById('wishlist-count');
        if (!badge) return;
        badge.textContent = wishlist.length;
        badge.classList.toggle('hidden', wishlist.length === 0);
    };

    window.updateWishlistDisplay = function() {
        const container = document.getElementById('wishlist-items');
        if (!container) return;

        if (wishlist.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">Your wishlist is empty.</p>';
            return;
        }

        container.innerHTML = wishlist.map(item => {
            const displayPrice = item.price && item.price.toString().startsWith('$') ? item.price : '$' + item.price;
            const imageHtml = renderImage(item.image, item.name);
            const dataImage = (item.image || '').replace(/"/g, '&quot;');
            return `
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow gap-4" data-product-image="${dataImage}">
                    <div class="flex items-center gap-4 flex-1 w-full sm:w-auto">
                        <div class="w-24 h-24 sm:w-20 sm:h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${imageHtml}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 mb-1 text-sm sm:text-base">${item.name}</h3>
                            <p class="text-green-600 font-bold text-lg">${displayPrice}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end sm:justify-start">
                        <button onclick="addToCart(this, '${item.id}', '${item.name.replace(/'/g, "\\'")}', '${item.price}', '${item.image || ''}')" class="px-4 py-2 text-green-600 rounded border border-green-600 hover:text-green-700 hover:border-green-700 transition-colors text-sm flex items-center gap-2 whitespace-nowrap" data-product-image="${dataImage}">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Add to Cart</span>
                        </button>
                        <button onclick="removeFromWishlist('${item.id}')" class="text-red-500 hover:text-red-700 transition-colors p-2" title="Remove from wishlist">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    };

    window.toggleCompareModal = function() {
        const modal = document.getElementById('compare-modal');
        if (modal) {
            modal.classList.toggle('hidden');
            window.updateCompareDisplay();
        }
    };

    window.addToCompare = function(productId, productName, productPrice, productImage) {
        productImage = resolveImage(productImage);
        if (compareList.length >= 4) {
            notify('You can only compare up to 4 products at once.', 'error');
            return;
        }
        if (!compareList.find(item => item.id === productId)) {
            compareList.push({ id: productId, name: productName, price: productPrice, image: productImage });
            window.compareList = compareList;
            saveCompare();
            window.updateCompareCount();

            const icon = document.querySelector('.compare-icon-' + productId);
            if (icon) {
                icon.classList.remove('text-gray-600');
                icon.classList.add('text-green-600');
            }

            notify('Product added to compare!', 'success');
        } else {
            notify('Product already in compare list!', 'error');
        }
        window.updateCompareDisplay();
    };

    window.removeFromCompare = function(productId) {
        compareList = compareList.filter(item => item.id !== productId);
        window.compareList = compareList;
        saveCompare();
        window.updateCompareCount();
        window.updateCompareDisplay();

        const icon = document.querySelector('.compare-icon-' + productId);
        if (icon) {
            icon.classList.remove('text-green-600');
            icon.classList.add('text-gray-600');
        }
    };

    window.clearCompare = function() {
        compareList.forEach(item => {
            const icon = document.querySelector('.compare-icon-' + item.id);
            if (icon) {
                icon.classList.remove('text-green-600');
                icon.classList.add('text-gray-600');
            }
        });
        compareList = [];
        window.compareList = compareList;
        saveCompare();
        window.updateCompareCount();
        window.updateCompareDisplay();
    };

    window.updateCompareIcons = function() {
        compareList.forEach(item => {
            const icon = document.querySelector('.compare-icon-' + item.id);
            if (icon) {
                icon.classList.remove('text-gray-600');
                icon.classList.add('text-green-600');
            }
        });
    };

    window.updateCompareCount = function() {
        const badge = document.getElementById('compare-count');
        if (!badge) return;
        badge.textContent = compareList.length;
        badge.classList.toggle('hidden', compareList.length === 0);
    };

    window.updateCompareDisplay = function() {
        const container = document.getElementById('compare-items');
        if (!container) return;

        if (compareList.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">No products to compare yet.</p>';
            return;
        }

        container.innerHTML = compareList.map(item => {
            const imageHtml = renderImage(item.image, item.name);
            return `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                            ${imageHtml}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">${item.name}</h3>
                            <p class="text-green-600 font-bold">${item.price}</p>
                        </div>
                    </div>
                    <button onclick="removeFromCompare('${item.id}')" class="text-red-500 hover:text-red-700 transition-colors">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }).join('');
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.updateCartCount();
        window.updateWishlistCount();
        window.updateCompareCount();
        window.updateCartDisplay();
        window.updateWishlistDisplay();
        window.updateCompareDisplay();
        window.updateCompareIcons();

        wishlist.forEach(item => {
            const icon = document.querySelector('.wishlist-icon-' + item.id);
            if (icon) {
                icon.classList.remove('far', 'text-gray-600');
                icon.classList.add('fas', 'text-red-500');
            }
        });
    });
})();
</script>
