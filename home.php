<?php
if (session_status() === PHP_SESSION_NONE) {
session_start();
}
if (!isset($config_included)) {
include('config.php');
    $config_included = true;
}
if (!isset($nav_included)) {
include('nav-gradient.php');
    $nav_included = true;
}

// Check and add rating field to products table if it doesn't exist
if (isset($conn)) {
    $checkRating = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'rating'");
    if (mysqli_num_rows($checkRating) == 0) {
        mysqli_query($conn, "ALTER TABLE products ADD COLUMN rating DECIMAL(3,2) DEFAULT 0.00");
    }
}

// Always refresh user info from session to ensure it's current
// This ensures user info is accessible even when home.php is included from other files
$isAdmin = isset($_SESSION['admin']);
$isUser = isset($_SESSION['user_id']);
$currentUser = $isAdmin ? $_SESSION['admin'] : ($isUser ? (isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest User') : 'Guest User');
$currentEmail = $isAdmin ? ($_SESSION['admin'] . '@admin.com') : ($isUser ? (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'guest@example.com') : 'guest@example.com');
$currentFirstName = $isUser ? (isset($_SESSION['user_first_name']) ? $_SESSION['user_first_name'] : 'Guest') : ($isAdmin ? $_SESSION['admin'] : 'Guest');
$currentLastName = $isUser ? (isset($_SESSION['user_last_name']) ? $_SESSION['user_last_name'] : 'User') : ($isAdmin ? 'Admin' : 'User');

if (!function_exists('js_str')) {
    function js_str($value) {
        return htmlspecialchars(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    }
}

// Product stock status lookup (matches product-detail.php)
$productStock = [
    1 => true,  // All Natural Style Chicken Meatballs
    2 => true,  // Seeds of Change Organic Red Rice
    3 => true,  // Chobani Complete Vanilla
    4 => true,  // Blue Almonds Lightly Salted Vegetables
    5 => true,  // Haagen Caramel Cone Ice Cream
    6 => true,  // Gorton's Beer Battered Fish
    7 => true,  // Angie's Sweet & Salty Kettle Corn
    8 => true,  // Foster Farms Takeout Crispy Classic
    9 => false, // Encore Seafoods Stuffed (out of stock)
    10 => true, // Canada Dry Ginger Ale - 2 L
];
?>

<?php
// Use hero data from nav-gradient.php if available, otherwise load it
if (!isset($hero) && isset($hero_nav)) {
    $hero = $hero_nav;
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
                        <button id="language-button" class="cursor-pointer hover:opacity-80 transition-opacity duration-200 flex items-center">
                            <img src="image/UK.png" alt="Language" class="w-7 h-5 object-contain rounded-sm" id="language-flag" style="min-width: 28px; min-height: 20px;">
                        </button>
    </div>
</div>

<!-- Language Dropdown Menu -->
<div id="language-dropdown" class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-[120px]" style="display: none; z-index: 99999 !important; position: fixed !important;">
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
<div id="user-dropdown" class="fixed bg-white/40 backdrop-blur-xl rounded-lg shadow-lg border border-gray-200/20 py-3 min-w-[220px]" style="display: none; z-index: 99999 !important; position: fixed !important;">
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
                </div>
                
<!-- User-related modals - Always included (outside headerOnly check) -->
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
            <button onclick="clearCompare()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Clear All</button>
            <button onclick="toggleCompareModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-[80px] right-6 z-[100] space-y-2 pointer-events-none"></div>

<!-- Wishlist Modal -->
<div id="wishlist-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-start justify-center pt-32" onclick="closeModalOnBackdrop(event, 'wishlist-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden mt-8" onclick="event.stopPropagation()">
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

<!-- Orders Modal -->
<div id="orders-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'orders-modal')">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">My Orders</h2>
            <button onclick="closeModal('orders-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div id="orders-list" class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="font-semibold text-gray-800">Order #12345</p>
                            <p class="text-sm text-gray-500">Placed on Jan 15, 2025</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Delivered</span>
                    </div>
                    <div class="flex items-center gap-4 border-t border-gray-200 pt-4">
                        <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">Product Name</p>
                            <p class="text-sm text-gray-500">Quantity: 2</p>
                        </div>
                        <p class="text-green-600 font-bold">$59.98</p>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">View Details</button>
                        <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">Reorder</button>
                    </div>
                </div>
                <p class="text-gray-500 text-center py-8">No more orders found.</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end">
            <button onclick="closeModal('orders-modal')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Close</button>
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

<!-- Address Form Modal (Add/Edit) -->
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
                        <button onclick="enable2FA()" class="w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-left">
                            <i class="fas fa-shield-alt text-gray-500 mr-3"></i>
                            Two-Factor Authentication
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

<!-- Change Password Modal -->
<div id="change-password-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="closeModalOnBackdrop(event, 'change-password-modal')">
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
                    <input type="password" id="current-password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="new-password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="confirm-password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
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

<!-- Essential JavaScript functions - Always included (outside headerOnly check) -->
<script>
// Initialize cart, wishlist, and compare from localStorage (use global scope)
var cart = JSON.parse(localStorage.getItem('cart')) || [];
window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
var wishlist = window.wishlist; // Alias for compatibility

function resolveProductImage(productImage) {
    if (productImage && typeof productImage === 'string' && productImage.trim() !== '') {
        return productImage;
    }

    const evt = window.event || null;
    let current = evt ? (evt.currentTarget || evt.target) : null;

    while (current) {
        if (current.tagName && current.tagName.toLowerCase() === 'img' && current.getAttribute('src')) {
            return current.getAttribute('src');
        }

        if (current.querySelector) {
            const img = current.querySelector('img');
            if (img && img.getAttribute('src')) {
                return img.getAttribute('src');
            }
        }

        current = current.parentElement;
    }

    return 'image/logo.webp';
}
var compareList = JSON.parse(localStorage.getItem('compareList')) || [];

// Update badge counts on page load and initialize wishlist icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof updateCartCount === 'function') updateCartCount();
    if (typeof updateWishlistCount === 'function') updateWishlistCount();
    if (typeof updateCompareCount === 'function') updateCompareCount();
    if (typeof updateCompareIcons === 'function') updateCompareIcons();
    
    // Update wishlist icons based on current wishlist
    var wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist.forEach(function(item) {
        var icon = document.querySelector('.wishlist-icon-' + item.id);
        if (icon) {
            icon.classList.remove('far', 'text-gray-600');
            icon.classList.add('fas', 'text-red-500');
        }
    });
});

// Toast Notification Function
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const colors = type === 'error'
        ? { bg: 'bg-red-600', icon: 'fas fa-exclamation-circle' }
        : { bg: 'bg-green-600', icon: 'fas fa-check-circle' };
    const toast = document.createElement('div');
    toast.className = `${colors.bg} text-white rounded-lg shadow-lg px-4 py-3 flex items-center gap-3 pointer-events-auto transition-opacity duration-300 opacity-0`;
    toast.innerHTML = `<i class="${colors.icon}"></i><span class="text-sm font-medium">${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(() => { toast.classList.remove('opacity-0'); toast.classList.add('opacity-100'); });
    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Search Functions - Always included (outside headerOnly check)
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

function handleSearchSubmit(e) {
    if (e && e.preventDefault) {
        e.preventDefault();
    }
    const input = document.getElementById('search-input');
    if (input && input.value.trim()) {
        window.location.href = `index.php?page=all-products&search=${encodeURIComponent(input.value.trim())}`;
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

// Categories Menu Functions - Always included (outside headerOnly check)
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
    
    if (dropdown && toggle && !toggle.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
        const chevron = document.getElementById('categories-chevron');
        if (chevron) {
            chevron.classList.remove('rotate-180');
        }
    }
});

// Language Dropdown Functions - ensure available even in headerOnly mode
(function() {
    if (window.__languageDropdownInitialized) {
        return;
    }
    window.__languageDropdownInitialized = true;

    let hideLanguageDropdownTimeout = null;

    window.showLanguageDropdown = function() {
        const dropdown = document.getElementById('language-dropdown');
        const languageButton = document.getElementById('language-button');
        if (!dropdown || !languageButton) {
            return;
        }

        if (hideLanguageDropdownTimeout) {
            clearTimeout(hideLanguageDropdownTimeout);
            hideLanguageDropdownTimeout = null;
        }

        const buttonRect = languageButton.getBoundingClientRect();
        const dropdownTop = buttonRect.bottom + 8;
        const dropdownRight = window.innerWidth - buttonRect.right;

        dropdown.style.top = dropdownTop + 'px';
        dropdown.style.right = dropdownRight + 'px';
        dropdown.style.left = 'auto';
        dropdown.style.bottom = 'auto';
        dropdown.style.display = 'block';
        dropdown.style.opacity = '1';
        dropdown.style.visibility = 'visible';
        dropdown.style.pointerEvents = 'auto';
        dropdown.style.position = 'fixed';
        dropdown.style.setProperty('z-index', '99999', 'important');
    };

    window.hideLanguageDropdown = function(immediate = false) {
        const dropdown = document.getElementById('language-dropdown');
        if (!dropdown) {
            return;
        }

        const closeDropdown = () => {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.pointerEvents = 'none';
        };

        if (hideLanguageDropdownTimeout) {
            clearTimeout(hideLanguageDropdownTimeout);
            hideLanguageDropdownTimeout = null;
        }

        if (immediate) {
            closeDropdown();
        } else {
            hideLanguageDropdownTimeout = setTimeout(closeDropdown, 150);
        }
    };

    window.toggleLanguageDropdown = function(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const dropdown = document.getElementById('language-dropdown');
        if (!dropdown) {
            return;
        }

        const isVisible = dropdown.style.display === 'block' && dropdown.style.visibility !== 'hidden';
        if (isVisible) {
            window.hideLanguageDropdown(true);
        } else {
            window.showLanguageDropdown();
        }
    };

    document.addEventListener('click', function(event) {
        const languageButton = event.target.closest('#language-button');
        const dropdown = document.getElementById('language-dropdown');
        const isClickInsideDropdown = dropdown && dropdown.contains(event.target);

        if (!languageButton && !isClickInsideDropdown && dropdown && dropdown.style.display === 'block') {
            window.hideLanguageDropdown(true);
        }
    });

    const attachLanguageButton = function() {
        const languageButton = document.getElementById('language-button');
        if (languageButton && !languageButton.__languageHandlerBound) {
            languageButton.addEventListener('click', window.toggleLanguageDropdown);
            languageButton.__languageHandlerBound = true;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachLanguageButton);
    } else {
        attachLanguageButton();
    }
})();

function changeLanguage(flagImage) {
    const flagImg = document.getElementById('language-flag');
    if (flagImg) {
        flagImg.src = 'image/' + flagImage;
    }

    localStorage.setItem('selectedLanguage', flagImage);

    window.hideLanguageDropdown(true);

    console.log('Language changed to: ' + flagImage);
}

document.addEventListener('DOMContentLoaded', function() {
    const savedLanguage = localStorage.getItem('selectedLanguage');
    const flagImg = document.getElementById('language-flag');
    if (savedLanguage && flagImg) {
        flagImg.src = 'image/' + savedLanguage;
    }
});

// Close language dropdown when clicking outside (redundant guard for safety)
document.addEventListener('click', function(event) {
    const languageButton = event.target.closest('#language-button');
    const dropdown = document.getElementById('language-dropdown');
    const isClickInsideDropdown = dropdown && dropdown.contains(event.target);

    if (!languageButton && !isClickInsideDropdown && dropdown && dropdown.style.display === 'block') {
        window.hideLanguageDropdown(true);
    }
});

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
            
            // Ensure dropdown is on top by setting all positioning properties
            dropdown.style.top = dropdownTop + 'px';
            dropdown.style.right = dropdownRight + 'px';
            dropdown.style.left = 'auto';
            dropdown.style.bottom = 'auto';
            dropdown.style.display = 'block';
            dropdown.style.position = 'fixed';
            dropdown.style.setProperty('z-index', '99999', 'important');
            dropdown.style.pointerEvents = 'auto';
            dropdown.style.opacity = '1';
            dropdown.style.visibility = 'visible';
            dropdown.style.transform = 'none'; // Remove any transforms that might create stacking context
            dropdown.style.isolation = 'isolate'; // Create new stacking context
        } else {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
        }
    }
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');
    const dropdown = document.getElementById('user-dropdown');
    const userMenuContainer = dropdown ? dropdown.closest('.relative') : null;
    
    if (!userButton && !userMenuContainer?.contains(event.target) && dropdown) {
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
        }
    }
});

// Close modal when clicking on backdrop
function closeModalOnBackdrop(event, modalId) {
    if (event.target.id === modalId || event.target.classList.contains('bg-black')) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    }
}

// Close modal function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Profile Modal Functions
function openProfileModal() {
    closeModal('user-dropdown');
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
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file.');
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size must be less than 5MB.');
            return;
        }
        
        // Create a FileReader to preview the image
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const photoData = e.target.result;
            
            // Get current user ID or username for user-specific storage
            const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
            
            // Save to localStorage with user-specific key
            localStorage.setItem('profilePhoto_' + userId, photoData);
            
            // Update all user icons
            updateUserIcons(photoData);
            
            alert('Photo updated successfully!');
        };
        
        reader.readAsDataURL(file);
    }
}

// Helper function to update all user icons
function updateUserIcons(photoData) {
    // Update profile modal photo
    const profilePreview = document.getElementById('profile-photo-preview');
    const profileIcon = document.getElementById('profile-photo-icon');
    
    if (profilePreview && profileIcon) {
        profilePreview.src = photoData;
        profilePreview.classList.remove('hidden');
        profileIcon.classList.add('hidden');
    }
    
    // Update header account button icon
    const headerPhoto = document.getElementById('header-user-photo');
    const headerIcon = document.getElementById('header-user-icon');
    
    if (headerPhoto && headerIcon) {
        headerPhoto.src = photoData;
        headerPhoto.classList.remove('hidden');
        headerIcon.classList.add('hidden');
    }
    
    // Update dropdown menu icon
    const dropdownPhoto = document.getElementById('dropdown-user-photo');
    const dropdownIcon = document.getElementById('dropdown-user-icon');
    
    if (dropdownPhoto && dropdownIcon) {
        dropdownPhoto.src = photoData;
        dropdownPhoto.classList.remove('hidden');
        dropdownIcon.classList.add('hidden');
    }
}

// Load saved profile photo on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get current user ID or username for user-specific storage
    const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
    
    // Only load user-specific photo
    const savedPhoto = localStorage.getItem('profilePhoto_' + userId);
    if (savedPhoto) {
        updateUserIcons(savedPhoto);
    }
});

// Addresses Modal Functions
var addresses = JSON.parse(localStorage.getItem('addresses')) || [
    { id: '1', label: 'Home Address', street: '123 Main Street', city: 'City', state: 'State', zip: '12345', country: 'United States', isDefault: true }
];

var editingAddressId = null;

function openAddressesModal() {
    closeModal('user-dropdown');
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
    const form = document.getElementById('address-form');
    
    editingAddressId = addressId;
    
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
            form.reset();
            document.getElementById('address-id').value = '';
        }
        modal.classList.remove('hidden');
    }
}

function closeAddressForm() {
    const modal = document.getElementById('address-form-modal');
    if (modal) {
        modal.classList.add('hidden');
        editingAddressId = null;
        document.getElementById('address-form').reset();
    }
}

function saveAddress(event) {
    event.preventDefault();
    
    const addressData = {
        id: editingAddressId || Date.now().toString(),
        label: document.getElementById('address-label').value,
        street: document.getElementById('address-street').value,
        city: document.getElementById('address-city').value,
        state: document.getElementById('address-state').value,
        zip: document.getElementById('address-zip').value,
        country: document.getElementById('address-country').value,
        isDefault: document.getElementById('address-default').checked
    };
    
    // If setting as default, remove default from other addresses
    if (addressData.isDefault) {
        addresses.forEach(addr => {
            if (addr.id !== addressData.id) {
                addr.isDefault = false;
            }
        });
    }
    
    if (editingAddressId) {
        // Update existing address
        const index = addresses.findIndex(a => a.id === editingAddressId);
        if (index !== -1) {
            addresses[index] = addressData;
        }
        alert('Address updated successfully!');
    } else {
        // Add new address
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
    closeModal('user-dropdown');
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
        errorDiv.classList.add('hidden');
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
    errorDiv.classList.add('hidden');
    
    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        errorMessage.textContent = 'All fields are required.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (newPassword.length < 6) {
        errorMessage.textContent = 'New password must be at least 6 characters long.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        errorMessage.textContent = 'New password and confirm password do not match.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (currentPassword === newPassword) {
        errorMessage.textContent = 'New password must be different from current password.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Simulate password verification (remove in production)
    const savedPassword = localStorage.getItem('userPassword') || 'currentpass123';
    
    if (currentPassword !== savedPassword) {
        errorMessage.textContent = 'Current password is incorrect.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Save new password (in production, this would be sent to server)
    localStorage.setItem('userPassword', newPassword);
    
    // Show success and close modal
    alert('Password changed successfully!');
    closePasswordModal();
}

function enable2FA() {
    const enable = confirm('Do you want to enable Two-Factor Authentication?');
    if (enable) {
        alert('Two-Factor Authentication enabled! Check your email for setup instructions.');
    }
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

// Cart Modal Functions
function toggleCartModal() {
    const modal = document.getElementById('cart-modal');
    if (modal) {
        modal.classList.toggle('hidden');
        updateCartDisplay();
    }
}

function addToCart(productId, productName, productPrice, productImage, quantity = 1) {
    productImage = resolveProductImage(productImage);
    if (!productImage || typeof productImage !== 'string' || productImage.trim() === '') {
        productImage = 'image/logo.webp';
    }
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ id: productId, name: productName, price: productPrice, image: productImage, quantity: quantity });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
    if (typeof showToast === 'function') {
        showToast(`${productName} added to cart`, 'success');
    } else {
        alert(`${productName} added to cart`);
    }
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
}

function updateCartQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity = parseInt(quantity);
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            updateCartDisplay();
        }
    }
}

function clearCart() {
    cart = [];
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
}

function updateCartDisplay() {
    const container = document.getElementById('cart-items');
    const totalElement = document.getElementById('cart-total');
    
    if (container) {
        if (cart.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty.</p>';
            if (totalElement) totalElement.textContent = '$0.00';
        } else {
            let cartUpdated = false;
            cart = cart.map(item => {
                if (!item.image || typeof item.image !== 'string' || item.image.trim() === '') {
                    cartUpdated = true;
                    return { ...item, image: 'image/logo.webp' };
                }
                return item;
            });
            if (cartUpdated) {
                localStorage.setItem('cart', JSON.stringify(cart));
            }

            container.innerHTML = cart.map(item => {
                const price = parseFloat(item.price.replace('$', '').replace(',', ''));
                const subtotal = price * item.quantity;
                // Product image - use actual image if available, otherwise show placeholder
                let imageHtml = '';
                if (item.image && item.image.trim() !== '') {
                    imageHtml = `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded">`;
                } else {
                    imageHtml = `<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400"></i>
                    </div>`;
                }
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
                            <input type="number" value="${item.quantity}" min="1" 
                                   onchange="updateCartQuantity('${item.id}', this.value)" 
                                   class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                            <p class="text-gray-700 font-semibold w-20 text-right">$${subtotal.toFixed(2)}</p>
                            <button onclick="removeFromCart('${item.id}')" class="text-red-500 hover:text-red-700 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            const total = cart.reduce((sum, item) => {
                const price = parseFloat(item.price.replace('$', '').replace(',', ''));
                return sum + (price * item.quantity);
            }, 0);
            
            if (totalElement) totalElement.textContent = '$' + total.toFixed(2);
        }
    }
}

function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    // Close cart modal
    closeModal('cart-modal');
    // Redirect to checkout page
    window.location.href = 'index.php?page=checkout';
}

// Wishlist Modal Functions
function toggleWishlistModal() {
    const modal = document.getElementById('wishlist-modal');
    if (modal) {
        modal.classList.toggle('hidden');
        updateWishlistDisplay();
    }
}

// Toggle wishlist (add if not exists, remove if exists)
function toggleWishlist(productId, productName, productPrice, productImage, productBrand = '', originalPrice = '', inStock = true) {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    productImage = resolveProductImage(productImage);
    productImage = resolveProductImage(productImage);
    
    const existingIndex = wishlist.findIndex(item => item.id === productId);
    
    if (existingIndex === -1) {
        // Add to wishlist
        wishlist.push({ 
            id: productId, 
            name: productName, 
            price: productPrice, 
            image: productImage,
            brand: productBrand,
            original_price: originalPrice,
            in_stock: inStock
        });
        window.wishlist = wishlist; // Sync to global
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        
        // Update icon if exists
        var icon = document.querySelector('.wishlist-icon-' + productId);
        if (icon) {
            icon.classList.remove('far', 'text-gray-600');
            icon.classList.add('fas', 'text-red-500');
        }
        
        updateWishlistCount();
        if (typeof showToast === 'function') {
            showToast('Added to wishlist!', 'success');
        } else {
            alert('Added to wishlist!');
        }
    } else {
        // Remove from wishlist
        wishlist.splice(existingIndex, 1);
        window.wishlist = wishlist; // Sync to global
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        
        // Update icon if exists
        var icon = document.querySelector('.wishlist-icon-' + productId);
        if (icon) {
            icon.classList.remove('fas', 'text-red-500');
            icon.classList.add('far', 'text-gray-600');
        }
        
        updateWishlistCount();
        if (typeof showToast === 'function') {
            showToast('Removed from wishlist!', 'info');
        } else {
            alert('Removed from wishlist!');
        }
    }
    
    // Update display if modal is open
    updateWishlistDisplay();
}

function addToWishlist(productId, productName, productPrice, productImage) {
    toggleWishlist(productId, productName, productPrice, productImage);
}

function removeFromWishlist(productId) {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    wishlist = wishlist.filter(item => item.id !== productId);
    window.wishlist = wishlist; // Sync to global
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    
    // Update icon if exists
    var icon = document.querySelector('.wishlist-icon-' + productId);
    if (icon) {
        icon.classList.remove('fas', 'text-red-500');
        icon.classList.add('far', 'text-gray-600');
    }
    
    updateWishlistCount();
    updateWishlistDisplay();
}

function updateWishlistCount() {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    const count = wishlist.length;
    const badge = document.getElementById('wishlist-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
}

function updateWishlistDisplay() {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    const container = document.getElementById('wishlist-items');
    if (!container) return;
    
    if (wishlist.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Your wishlist is empty.</p>';
    } else {
        container.innerHTML = wishlist.map(item => {
            // Ensure price has $ symbol
            let displayPrice = item.price;
            if (!displayPrice.startsWith('$')) {
                displayPrice = '$' + displayPrice;
            }
            
            // Product image - use actual image if available, otherwise show placeholder
            let imageHtml = '';
            if (item.image && item.image.trim() !== '') {
                imageHtml = `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded">`;
            } else {
                imageHtml = `<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-2xl"></i>
                </div>`;
            }
            
            return `
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow gap-4">
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
                        <button onclick="addToCart('${item.id}', '${item.name.replace(/'/g, "\\'")}', '${item.price}', '${item.image || ''}')" class="px-4 py-2 text-green-600 rounded border border-green-600 hover:text-green-700 hover:border-green-700 transition-colors text-sm flex items-center gap-2 whitespace-nowrap">
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
    }
}

// Compare Modal Functions
function toggleCompareModal() {
    const modal = document.getElementById('compare-modal');
    if (modal) {
        modal.classList.toggle('hidden');
        updateCompareDisplay();
    }
}

function addToCompare(productId, productName, productPrice, productImage) {
    productImage = resolveProductImage(productImage);
    if (!productImage || typeof productImage !== 'string' || productImage.trim() === '') {
        productImage = 'image/logo.webp';
    }
    if (compareList.length >= 4) {
        if (typeof showToast === 'function') {
            showToast('You can only compare up to 4 products at once.', 'error');
        } else {
            alert('You can only compare up to 4 products at once.');
        }
        return;
    }
    
    if (!compareList.find(item => item.id === productId)) {
        compareList.push({ id: productId, name: productName, price: productPrice, image: productImage });
        localStorage.setItem('compareList', JSON.stringify(compareList));
        updateCompareCount();
        
        // Update icon color to green
        const icon = document.querySelector('.compare-icon-' + productId);
        if (icon) {
            icon.classList.remove('text-gray-600');
            icon.classList.add('text-green-600');
        }
        
        if (typeof showToast === 'function') {
            showToast('Product added to compare!', 'success');
        } else {
            alert('Product added to compare!');
        }
    } else {
        if (typeof showToast === 'function') {
            showToast('Product already in compare list!', 'error');
        } else {
            alert('Product already in compare list!');
        }
    }
}

function removeFromCompare(productId) {
    compareList = compareList.filter(item => item.id !== productId);
    localStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareCount();
    updateCompareDisplay();
    
    // Reset icon color to gray
    const icon = document.querySelector('.compare-icon-' + productId);
    if (icon) {
        icon.classList.remove('text-green-600');
        icon.classList.add('text-gray-600');
    }
}

function clearCompare() {
    // Reset all compare icons to gray before clearing
    compareList.forEach(item => {
        const icon = document.querySelector('.compare-icon-' + item.id);
        if (icon) {
            icon.classList.remove('text-green-600');
            icon.classList.add('text-gray-600');
        }
    });
    
    compareList = [];
    localStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareCount();
    updateCompareDisplay();
}

function updateCompareIcons() {
    // Initialize compare icon states on page load
    compareList.forEach(item => {
        const icon = document.querySelector('.compare-icon-' + item.id);
        if (icon) {
            icon.classList.remove('text-gray-600');
            icon.classList.add('text-green-600');
        }
    });
}

function updateCompareCount() {
    const count = compareList.length;
    const badge = document.getElementById('compare-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
}

function updateCompareDisplay() {
    const container = document.getElementById('compare-items');
    if (container) {
        if (compareList.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">No products to compare yet.</p>';
        } else {
            container.innerHTML = compareList.map(item => {
                const resolvedImage = item.image && item.image.trim() !== '' ? item.image : 'image/logo.webp';
                const imageHtml = `<img src="${resolvedImage}" alt="${item.name}" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center\'><i class=\'fas fa-image text-gray-400\'></i></div>'">`;
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
        }
    }
}
</script>

<?php if (!isset($headerOnly) || !$headerOnly): ?>
<!-- Hero Section -->
<?php
// Get hero section data from database (if not already loaded for menu bar)
if (!isset($hero) || !isset($hero['title_line1'])) {
    $heroQuery = "SELECT * FROM hero_section LIMIT 1";
    $heroResult = mysqli_query($conn, $heroQuery);
    $hero = mysqli_fetch_assoc($heroResult);
    
    // Default values if no data exists
    if (!$hero) {
        $hero = [
            'title_line1' => 'Fresh Vegetables',
            'title_line2' => 'Big discount',
            'title_color' => 'text-green-600',
            'subtitle' => 'Save up to 50% off on your first order',
            'description' => '',
            'image_path' => '',
            'button_text' => 'Shop Now',
            'button_link' => '#',
            'background_color' => 'from-green-50 to-green-100'
        ];
    }
}
?>
<div class="bg-gradient-to-r <?php echo htmlspecialchars($hero['background_color']); ?> pt-[120px] pb-16 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Side - Text Content -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-5xl lg:text-6xl font-bold text-gray-800 mb-4">
                        <?php echo htmlspecialchars($hero['title_line1']); ?><br>
                        <span class="<?php echo htmlspecialchars($hero['title_color']); ?>"><?php echo htmlspecialchars($hero['title_line2']); ?></span>
                    </h1>
                    <?php if ($hero['subtitle']): ?>
                        <p class="text-xl text-gray-600 mb-6"><?php echo htmlspecialchars($hero['subtitle']); ?></p>
                    <?php endif; ?>
                    <?php if ($hero['description']): ?>
                        <p class="text-lg text-gray-600 mb-6"><?php echo htmlspecialchars($hero['description']); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Email Subscription Form -->
                <div class="bg-transparent rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Subscribe to our newsletter</h3>
                    <form class="flex gap-2">
                        <input type="email" placeholder="Your email address" class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Right Side - Product Image -->
                                <div class="flex items-center justify-center">
                <div class="relative w-full max-w-lg">
                    <?php 
                    // Check if image exists and is valid
                    $imagePath = isset($hero['image_path']) ? $hero['image_path'] : '';
                    $imageExists = false;
                    if ($imagePath) {
                        // Check if path is absolute or relative
                        if (file_exists($imagePath)) {
                            $imageExists = true;
                        } elseif (file_exists(__DIR__ . '/' . $imagePath)) {
                            $imagePath = __DIR__ . '/' . $imagePath;
                            $imageExists = true;
                        }
                    }
                    ?>
                    <?php if ($imageExists && file_exists($imagePath)): ?>
                        <!-- Display uploaded image -->
                        <div class="bg-white rounded-2xl shadow-2xl p-4 transform hover:scale-105 transition-transform duration-300">
                            <img src="<?php echo htmlspecialchars($hero['image_path']); ?>" alt="Hero Image" 
                                 class="w-full h-auto rounded-xl object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display:none;">
                                <!-- Fallback to default icons -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6">
                                    <div class="flex items-center justify-center h-64">
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                                <i class="fas fa-lemon text-4xl text-yellow-400"></i>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                                <i class="fas fa-carrot text-4xl text-orange-500"></i>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                                <i class="fas fa-apple-alt text-4xl text-red-500"></i>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                                <i class="fas fa-seedling text-4xl text-green-500"></i>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                                <i class="fas fa-pepper-hot text-4xl text-red-600"></i>
                                            </div>
                                            <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                                <i class="fas fa-leaf text-4xl text-green-600"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Default icon grid if no image -->
                    <div class="bg-white rounded-2xl shadow-2xl p-8 transform hover:scale-105 transition-transform duration-300">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6">
                            <div class="flex items-center justify-center h-64">
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                        <i class="fas fa-lemon text-4xl text-yellow-400"></i>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                        <i class="fas fa-carrot text-4xl text-orange-500"></i>
                                </div>
                                    <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                        <i class="fas fa-apple-alt text-4xl text-red-500"></i>
                                </div>
                                    <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                        <i class="fas fa-seedling text-4xl text-green-500"></i>
                            </div>
                                    <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                        <i class="fas fa-pepper-hot text-4xl text-red-600"></i>
                        </div>
                                    <div class="bg-white rounded-lg p-4 shadow-md flex items-center justify-center">
                                        <i class="fas fa-leaf text-4xl text-green-600"></i>
                    </div>
                </div>
            </div>
                </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Slider Indicators -->
        <div class="flex justify-center gap-2 mt-8">
            <div class="w-2 h-2 bg-green-600 rounded-full"></div>
            <div class="w-8 h-2 bg-green-600 rounded-full"></div>
            <div class="w-2 h-2 bg-green-300 rounded-full"></div>
        </div>
    </div>
</div>

<!-- Feature Box Section -->
<div class="max-w-7xl mx-auto px-6 -mt-8 relative z-10">
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Free Shipping -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-shipping-fast text-3xl text-blue-700"></i>
            </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">Free Shipping</h3>
                    <p class="text-gray-600 text-sm">On order bigger than $50</p>
            </div>
        </div>
        
            <!-- 15 Days Return -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-suitcase text-3xl text-blue-700"></i>
            </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">15 Days Return</h3>
                    <p class="text-gray-600 text-sm">Moneyback guarantee</p>
        </div>
        </div>
        
            <!-- Secure Checkout -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-coins text-3xl text-blue-700"></i>
            </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">Secure Checkout</h3>
                    <p class="text-gray-600 text-sm">Secured by Stripe</p>
        </div>
        </div>
        
            <!-- Make Money -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-gift text-3xl text-blue-700"></i>
            </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">Make Money</h3>
                    <p class="text-gray-600 text-sm">Use our affiliate program</p>
        </div>
            </div>
        </div>
            </div>
        </div>
        
<!-- Featured Categories Section -->
<div class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Featured Categories</h2>
        <div class="flex items-center gap-4">
            <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                <i class="fas fa-chevron-left text-gray-600"></i>
            </button>
            <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                <i class="fas fa-chevron-right text-gray-600"></i>
            </button>
            </div>
        </div>
        
    <!-- Category Cards Grid -->
    <div id="featured-categories-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-10 gap-4">
        <?php
        // Fetch categories from database with vendor information
        $categoriesQuery = "SELECT c.id, c.name, c.description, c.icon, c.image_path, c.created_at, v.name as vendor_name, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           LEFT JOIN vendors v ON c.vendor_id = v.id 
                           GROUP BY c.id, c.name, c.description, c.icon, c.image_path, c.created_at, v.name 
                           ORDER BY c.name ASC 
                           LIMIT 10";
        $categoriesResult = mysqli_query($conn, $categoriesQuery);
        
        if (mysqli_num_rows($categoriesResult) > 0) {
            while ($cat = mysqli_fetch_assoc($categoriesResult)) {
                $iconClass = $cat['icon'] ? $cat['icon'] : 'fas fa-tag';
                $productCount = $cat['product_count'] ? $cat['product_count'] : 0;
                $vendorName = isset($cat['vendor_name']) ? $cat['vendor_name'] : '';
                ?>
                <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='index.php?page=all-products&category=<?php echo $cat['id']; ?>'">
                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3 overflow-hidden relative">
                        <?php 
                        $imagePath = isset($cat['image_path']) ? trim($cat['image_path']) : '';
                        
                        // Debug output (view page source to see this)
                        if (!empty($imagePath)) {
                            $fileExists1 = file_exists($imagePath);
                            $fileExists2 = file_exists(__DIR__ . '/' . $imagePath);
                            echo "<!-- Debug: Category '" . htmlspecialchars($cat['name']) . "' (ID: " . $cat['id'] . ") - Image Path: '" . htmlspecialchars($imagePath) . "' - Exists: " . ($fileExists1 ? 'YES' : ($fileExists2 ? 'YES (relative)' : 'NO')) . " -->";
                        }
                        
                        if (!empty($imagePath)): 
                            $displayPath = $imagePath;
                        ?>
                            <img src="<?php echo htmlspecialchars($displayPath); ?>" 
                                 alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                 class="w-full h-full object-cover"
                                 style="min-height: 64px;"
                                 onerror="console.error('Category image failed to load: <?php echo htmlspecialchars(addslashes($displayPath)); ?>'); this.style.display='none'; var iconDiv = this.parentElement.querySelector('.category-icon'); if(iconDiv) iconDiv.style.display='flex';">
                            <div class="category-icon w-full h-full flex items-center justify-center" style="display:none;">
                                <i class="<?php echo htmlspecialchars($iconClass); ?> text-3xl text-green-600"></i>
            </div>
                        <?php else: ?>
                            <i class="<?php echo htmlspecialchars($iconClass); ?> text-3xl text-green-600"></i>
                        <?php endif; ?>
        </div>
                    <h3 class="font-semibold text-gray-800 text-sm mb-1"><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <?php if ($vendorName): ?>
                        <p class="text-xs text-gray-500 mb-1">By <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($vendorName); ?></span></p>
                    <?php endif; ?>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-span-full text-center text-gray-500 py-8">No categories available. Admin can add categories from the admin panel.</div>';
        }
        ?>
                        </div>
                    </div>

<!-- Hot Deals Section -->
<div class="bg-red-50 py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">🔥 Hot Deals</h2>
                <p class="text-gray-600">Limited time offers - Don't miss out!</p>
                </div>
            <div class="hidden md:flex items-center gap-4">
                <button onclick="scrollHotDeals('left')" class="p-2 hover:bg-white rounded-full transition-colors shadow-md">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <button onclick="scrollHotDeals('right')" class="p-2 hover:bg-white rounded-full transition-colors shadow-md">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
                        </div>
                    </div>
        
        <!-- Hot Deals Products Grid -->
        <div id="hot-deals-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 overflow-x-auto">
            <?php
            // Fetch products with "hot_deals" in promotion_status
            $hotDealsQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             WHERE p.promotion_status LIKE '%hot_deals%' 
                             ORDER BY p.created_at DESC 
                             LIMIT 8";
            $hotDealsResult = mysqli_query($conn, $hotDealsQuery);
            
            if (mysqli_num_rows($hotDealsResult) > 0) {
                while ($product = mysqli_fetch_assoc($hotDealsResult)) {
                    $inStock = $product['quantity'] > 0;
                    $categoryName = $product['category_name'] ? $product['category_name'] : 'Uncategorized';
                    $categoryIcon = $product['category_icon'] ? $product['category_icon'] : 'fas fa-box';
                    $productImageRaw = !empty($product['image_path']) ? $product['image_path'] : '';
                    $productImageAttr = htmlspecialchars($productImageRaw, ENT_QUOTES, 'UTF-8');
                    $productImageJs = js_str($productImageRaw);
                    $discountPercent = isset($product['discount_percentage']) ? floatval($product['discount_percentage']) : 0;
                    $displayPrice = isset($product['discount_price']) && $product['discount_price'] < $product['price'] ? $product['discount_price'] : $product['price'];
                    $originalPrice = $product['price'];
                    $vendorName = $product['vendor_name'] ?? '';
                    $rating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 4.0; // Default to 4.0 if not set
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                    $productKey = 'hd' . $product['id'];
                    $productKeyJs = js_str($productKey);
                    $productNameJs = js_str($product['name'] ?? '');
                    $productBrandValue = $vendorName ?: $categoryName;
                    $productBrandJs = js_str($productBrandValue);
                    $displayPriceJs = js_str('$' . number_format($displayPrice, 2));
                    $originalPriceJs = js_str('$' . number_format($originalPrice, 2));
                    
                    // Determine badge color based on discount
                    if ($discountPercent >= 50) {
                        $badgeColor = 'bg-red-500';
                    } elseif ($discountPercent >= 30) {
                        $badgeColor = 'bg-orange-500';
                    } elseif ($discountPercent >= 15) {
                        $badgeColor = 'bg-amber-500';
                    } else {
                        $badgeColor = 'bg-green-500';
                    }
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow relative cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $product['id']; ?>'">
                <div class="relative">
                            <?php if ($discountPercent > 0): ?>
                                <div class="absolute top-2 left-2 <?php echo $badgeColor; ?> text-white px-3 py-1 rounded-full text-xs font-bold z-10 flex items-center gap-1">
                        <i class="fas fa-fire text-xs"></i>
                                    <?php echo number_format($discountPercent, 0); ?>% OFF
                    </div>
                            <?php endif; ?>
                            <button onclick="event.stopPropagation(); toggleWishlist(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>, <?php echo $productBrandJs; ?>, <?php echo $originalPriceJs; ?>, <?php echo $inStock ? 'true' : 'false'; ?>)" class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors z-10">
                                <i class="far fa-heart text-gray-600 wishlist-icon-hd<?php echo $product['id']; ?>"></i>
                    </button>
                            <?php if ($productImageRaw): ?>
                                <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img src="<?php echo $productImageAttr; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                    </div>
                    <?php else: ?>
                                <div class="h-48 bg-gradient-to-br from-red-50 to-pink-50 flex items-center justify-center">
                                    <i class="<?php echo htmlspecialchars($categoryIcon); ?> text-6xl text-red-400"></i>
                                </div>
                    <?php endif; ?>
                    </div>
                    <div class="p-4">
                            <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($categoryName); ?></p>
                            <h3 class="font-semibold text-gray-800 mb-2 text-sm"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="flex items-center gap-1 mb-2">
                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                                <?php if ($hasHalfStar): ?>
                                    <i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>
                                <?php endif; ?>
                                <?php for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++): ?>
                        <i class="far fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                                <span class="text-xs text-gray-500 ml-1">(<?php echo number_format($rating, 1); ?>)</span>
                        </div>
                            <?php if ($vendorName): ?>
                                <p class="text-xs text-gray-500 mb-2">By <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($vendorName); ?></span></p>
                            <?php endif; ?>
                    <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl font-bold text-red-600">$<?php echo number_format($displayPrice, 2); ?></span>
                                <?php if ($displayPrice < $originalPrice): ?>
                                    <span class="text-sm text-gray-400 line-through">$<?php echo number_format($originalPrice, 2); ?></span>
                    <?php endif; ?>
                    </div>
                            <?php if ($inStock): ?>
                                <button onclick="event.stopPropagation(); addToCart(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        Add to Cart
                        </button>
                    <?php else: ?>
                        <button onclick="event.stopPropagation(); return false;" disabled class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
                    <?php
                }
            } else {
                echo '<div class="col-span-full text-center text-gray-500 py-12">No hot deals available at the moment. Check back soon!</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Promotional Banners Section -->
<div class="max-w-7xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Banner 1 -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-bold mb-2">Everyday Fresh & Clean with Our Products</h3>
                <a href="index.php?page=all-products&category=vegetables" class="inline-block mt-4 bg-white text-green-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Shop Now →
                </a>
            </div>
            <div class="absolute right-0 top-0 h-full w-32 opacity-20">
                <i class="fas fa-onion text-8xl text-white"></i>
            </div>
        </div>
        
        <!-- Banner 2 -->
        <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-bold mb-2">Make your Breakfast Healthy and Easy</h3>
                <a href="index.php?page=all-products&category=dairy" class="inline-block mt-4 bg-white text-pink-600 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Shop Now →
                </a>
            </div>
            <div class="absolute right-0 top-0 h-full w-32 opacity-20">
                <i class="fas fa-wine-bottle text-8xl text-white"></i>
            </div>
        </div>
        
        <!-- Banner 3 -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-bold mb-2">The best Organic Products Online</h3>
                <a href="index.php?page=all-products&promotion=new_arrival" class="inline-block mt-4 bg-white text-orange-600 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Shop Now →
                </a>
            </div>
            <div class="absolute right-0 top-0 h-full w-32 opacity-20">
                <i class="fas fa-shopping-basket text-8xl text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Popular Products Section -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Popular Products</h2>
        </div>
        
        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php
            // Fetch products with "popular" in promotion_status
            $popularQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.promotion_status LIKE '%popular%' 
                            ORDER BY p.created_at DESC 
                            LIMIT 20";
            $popularResult = mysqli_query($conn, $popularQuery);
            
            if (mysqli_num_rows($popularResult) > 0) {
                while ($product = mysqli_fetch_assoc($popularResult)) {
                    $inStock = $product['quantity'] > 0;
                    $categoryName = $product['category_name'] ? $product['category_name'] : 'Uncategorized';
                    $categoryIcon = $product['category_icon'] ? $product['category_icon'] : 'fas fa-box';
                    $productImageRaw = !empty($product['image_path']) ? $product['image_path'] : '';
                    $productImageAttr = htmlspecialchars($productImageRaw, ENT_QUOTES, 'UTF-8');
                    $productImageJs = js_str($productImageRaw);
                    $discountPercent = isset($product['discount_percentage']) ? floatval($product['discount_percentage']) : 0;
                    $displayPrice = isset($product['discount_price']) && $product['discount_price'] < $product['price'] ? $product['discount_price'] : $product['price'];
                    $originalPrice = $product['price'];
                    $vendorName = $product['vendor_name'] ?? '';
                    $rating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 4.0; // Default to 4.0 if not set
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                    $productKey = 'p' . $product['id'];
                    $productKeyJs = js_str($productKey);
                    $productNameJs = js_str($product['name'] ?? '');
                    $productBrandValue = $vendorName ?: $categoryName;
                    $productBrandJs = js_str($productBrandValue);
                    $displayPriceJs = js_str('$' . number_format($displayPrice, 2));
                    $originalPriceJs = js_str('$' . number_format($originalPrice, 2));
                    
                    // Determine badge color based on discount
                    if ($discountPercent >= 50) {
                        $badgeColor = 'bg-red-600';
                    } elseif ($discountPercent >= 30) {
                        $badgeColor = 'bg-orange-500';
                    } elseif ($discountPercent >= 15) {
                        $badgeColor = 'bg-amber-500';
                    } else {
                        $badgeColor = 'bg-green-600';
                    }
                    ?>
                    <div class="product-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $product['id']; ?>'" data-category="<?php echo isset($product['category_id']) ? $product['category_id'] : '0'; ?>" data-price="<?php echo $displayPrice; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="relative">
                            <?php if ($discountPercent > 0): ?>
                                <div class="absolute top-2 left-2 <?php echo $badgeColor; ?> text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                                    <i class="fas fa-tag text-white"></i>
                                    <span><?php echo number_format($discountPercent, 0); ?>% OFF</span>
                    </div>
                            <?php endif; ?>
                    <div class="absolute top-2 right-2 flex gap-2 z-10">
                                <button onclick="event.stopPropagation(); toggleWishlist(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>, <?php echo $productBrandJs; ?>, <?php echo $originalPriceJs; ?>, <?php echo $inStock ? 'true' : 'false'; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors">
                                    <i class="far fa-heart text-gray-600 wishlist-icon-p<?php echo $product['id']; ?>"></i>
                        </button>
                                <button onclick="event.stopPropagation(); addToCompare(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-50 transition-colors" title="Add to Compare">
                                    <i class="fas fa-balance-scale text-gray-600 text-sm compare-icon-p<?php echo $product['id']; ?>"></i>
                    </button>
                    </div>
                            <?php if ($productImageRaw): ?>
                                <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img src="<?php echo $productImageAttr; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                    </div>
                    <?php else: ?>
                                <div class="h-48 bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center">
                                    <i class="<?php echo htmlspecialchars($categoryIcon); ?> text-6xl text-green-400"></i>
                                </div>
                    <?php endif; ?>
        </div>
                <div class="p-4">
                            <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($categoryName); ?></p>
                            <h3 class="font-semibold text-gray-800 mb-2 text-sm"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="flex items-center gap-1 mb-2">
                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                                <?php if ($hasHalfStar): ?>
                                    <i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>
                                <?php endif; ?>
                                <?php for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++): ?>
                        <i class="far fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                                <span class="text-xs text-gray-500 ml-1">(<?php echo number_format($rating, 1); ?>)</span>
                    </div>
                            <?php if ($vendorName): ?>
                                <p class="text-xs text-gray-500 mb-2">By <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($vendorName); ?></span></p>
                            <?php endif; ?>
                    <div class="flex items-center gap-2 mb-3">
                                <span class="text-lg font-bold text-green-600">$<?php echo number_format($displayPrice, 2); ?></span>
                                <?php if ($displayPrice < $originalPrice): ?>
                                    <span class="text-sm text-gray-400 line-through">$<?php echo number_format($originalPrice, 2); ?></span>
                                <?php endif; ?>
                    </div>
                            <?php if ($inStock): ?>
                                <button onclick="event.stopPropagation(); addToCart(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        Add
                    </button>
                    <?php else: ?>
                        <button onclick="event.stopPropagation(); return false;" disabled class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm">
                            Out of Stock
                        </button>
                    <?php endif; ?>
    </div>
</div>
                    <?php
                }
            } else {
                echo '<div class="col-span-full text-center text-gray-500 py-12">No popular products available at the moment. Check back soon!</div>';
            }
            ?>
                    </div>
                    </div>
                    </div>

<!-- New Arrival Section -->
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800">New Arrivals</h2>
            </div>
        
        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php
            // Fetch products with "new_arrival" in promotion_status
            $newArrivalQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.promotion_status LIKE '%new_arrival%' 
                            ORDER BY p.created_at DESC 
                            LIMIT 20";
            $newArrivalResult = mysqli_query($conn, $newArrivalQuery);
            
            if (mysqli_num_rows($newArrivalResult) > 0) {
                while ($product = mysqli_fetch_assoc($newArrivalResult)) {
                    $inStock = $product['quantity'] > 0;
                    $categoryName = $product['category_name'] ? $product['category_name'] : 'Uncategorized';
                    $categoryIcon = $product['category_icon'] ? $product['category_icon'] : 'fas fa-box';
                    $productImageRaw = !empty($product['image_path']) ? $product['image_path'] : '';
                    $productImageAttr = htmlspecialchars($productImageRaw, ENT_QUOTES, 'UTF-8');
                    $productImageJs = js_str($productImageRaw);
                    $discountPercent = isset($product['discount_percentage']) ? floatval($product['discount_percentage']) : 0;
                    $displayPrice = isset($product['discount_price']) && $product['discount_price'] < $product['price'] ? $product['discount_price'] : $product['price'];
                    $originalPrice = $product['price'];
                    $vendorName = $product['vendor_name'] ?? '';
                    $rating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 4.0; // Default to 4.0 if not set
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                    $productKey = 'na' . $product['id'];
                    $productKeyJs = js_str($productKey);
                    $productNameJs = js_str($product['name'] ?? '');
                    $productBrandValue = $vendorName ?: $categoryName;
                    $productBrandJs = js_str($productBrandValue);
                    $displayPriceJs = js_str('$' . number_format($displayPrice, 2));
                    $originalPriceJs = js_str('$' . number_format($originalPrice, 2));
                    
                    // Determine badge color based on discount
                    if ($discountPercent >= 50) {
                        $badgeColor = 'bg-red-600';
                    } elseif ($discountPercent >= 30) {
                        $badgeColor = 'bg-orange-500';
                    } elseif ($discountPercent >= 15) {
                        $badgeColor = 'bg-amber-500';
                    } else {
                        $badgeColor = 'bg-green-600';
                    }
                    ?>
                    <div class="product-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $product['id']; ?>'" data-category="<?php echo isset($product['category_id']) ? $product['category_id'] : '0'; ?>" data-price="<?php echo $displayPrice; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="relative">
                            <?php if ($discountPercent > 0): ?>
                                <div class="absolute top-2 left-2 <?php echo $badgeColor; ?> text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                                    <i class="fas fa-tag text-white"></i>
                                    <span><?php echo number_format($discountPercent, 0); ?>% OFF</span>
                    </div>
                            <?php endif; ?>
                    <div class="absolute top-2 right-2 flex gap-2 z-10">
                                <button onclick="event.stopPropagation(); toggleWishlist(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>, <?php echo $productBrandJs; ?>, <?php echo $originalPriceJs; ?>, <?php echo $inStock ? 'true' : 'false'; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors">
                                    <i class="far fa-heart text-gray-600 wishlist-icon-na<?php echo $product['id']; ?>"></i>
                        </button>
                                <button onclick="event.stopPropagation(); addToCompare(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-50 transition-colors" title="Add to Compare">
                                    <i class="fas fa-balance-scale text-gray-600 text-sm compare-icon-na<?php echo $product['id']; ?>"></i>
                    </button>
                    </div>
                            <?php if ($productImageRaw): ?>
                                <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img src="<?php echo $productImageAttr; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
            </div>
                    <?php else: ?>
                    <div class="h-48 bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center">
                                    <i class="<?php echo htmlspecialchars($categoryIcon); ?> text-6xl text-blue-400"></i>
            </div>
                    <?php endif; ?>
            </div>
            <div class="p-4">
                            <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($categoryName); ?></p>
                            <h3 class="font-semibold text-gray-800 mb-2 text-sm"><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="flex items-center gap-1 mb-2">
                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                                <?php if ($hasHalfStar): ?>
                                    <i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>
                    <?php endif; ?>
                                <?php for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++): ?>
                        <i class="far fa-star text-yellow-400 text-xs"></i>
                                <?php endfor; ?>
                                <span class="text-xs text-gray-500 ml-1">(<?php echo number_format($rating, 1); ?>)</span>
                    </div>
                            <?php if ($vendorName): ?>
                                <p class="text-xs text-gray-500 mb-2">By <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($vendorName); ?></span></p>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 mb-3">
                                <span class="text-lg font-bold text-green-600">$<?php echo number_format($displayPrice, 2); ?></span>
                                <?php if ($displayPrice < $originalPrice): ?>
                                    <span class="text-sm text-gray-400 line-through">$<?php echo number_format($originalPrice, 2); ?></span>
                    <?php endif; ?>
            </div>
                            <?php if ($inStock): ?>
                                <button onclick="event.stopPropagation(); addToCart(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        Add
                </button>
                    <?php else: ?>
                        <button onclick="event.stopPropagation(); return false;" disabled class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
                    <?php
                }
            } else {
                echo '<div class="col-span-full text-center text-gray-500 py-12">No new arrivals available at the moment. Check back soon!</div>';
            }
            ?>
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
            
            // Ensure dropdown is on top by setting all positioning properties
            dropdown.style.top = dropdownTop + 'px';
            dropdown.style.right = dropdownRight + 'px';
            dropdown.style.left = 'auto';
            dropdown.style.bottom = 'auto';
            dropdown.style.display = 'block';
            dropdown.style.position = 'fixed';
            dropdown.style.setProperty('z-index', '99999', 'important');
            dropdown.style.pointerEvents = 'auto';
            dropdown.style.opacity = '1';
            dropdown.style.visibility = 'visible';
            dropdown.style.transform = 'none'; // Remove any transforms that might create stacking context
            dropdown.style.isolation = 'isolate'; // Create new stacking context
        } else {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
        }
    }
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');
    const dropdown = document.getElementById('user-dropdown');
    const userMenuContainer = dropdown ? dropdown.closest('.relative') : null;
    
    if (!userButton && !userMenuContainer?.contains(event.target) && dropdown) {
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
        }
    }
});

// Initialize cart, wishlist, and compare from localStorage (use global scope)
var cart = JSON.parse(localStorage.getItem('cart')) || [];
window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
var wishlist = window.wishlist; // Alias for compatibility
var compareList = JSON.parse(localStorage.getItem('compareList')) || [];

// Update badge counts on page load and initialize wishlist icons
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateWishlistCount();
    updateCompareCount();
    updateCompareIcons();
    
    // Update wishlist icons based on current wishlist
    var wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist.forEach(function(item) {
        var icon = document.querySelector('.wishlist-icon-' + item.id);
        if (icon) {
            icon.classList.remove('far', 'text-gray-600');
            icon.classList.add('fas', 'text-red-500');
        }
    });
});

// Toast Notification Function
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const colors = type === 'error'
        ? { bg: 'bg-red-600', icon: 'fas fa-exclamation-circle' }
        : { bg: 'bg-green-600', icon: 'fas fa-check-circle' };
    const toast = document.createElement('div');
    toast.className = `${colors.bg} text-white rounded-lg shadow-lg px-4 py-3 flex items-center gap-3 pointer-events-auto transition-opacity duration-300 opacity-0`;
    toast.innerHTML = `<i class="${colors.icon}"></i><span class="text-sm font-medium">${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(() => { toast.classList.remove('opacity-0'); toast.classList.add('opacity-100'); });
    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}


// Compare Modal Functions
function toggleCompareModal() {
    const modal = document.getElementById('compare-modal');
    modal.classList.toggle('hidden');
    updateCompareDisplay();
}

function addToCompare(productId, productName, productPrice, productImage) {
    productImage = resolveProductImage(productImage);
    if (!productImage || typeof productImage !== 'string' || productImage.trim() === '') {
        productImage = 'image/logo.webp';
    }
    if (compareList.length >= 4) {
        if (typeof showToast === 'function') {
            showToast('You can only compare up to 4 products at once.', 'error');
        } else {
        alert('You can only compare up to 4 products at once.');
        }
        return;
    }
    
    if (!compareList.find(item => item.id === productId)) {
        compareList.push({ id: productId, name: productName, price: productPrice, image: productImage });
        localStorage.setItem('compareList', JSON.stringify(compareList));
        updateCompareCount();
        
        // Update icon color to green
        const icon = document.querySelector('.compare-icon-' + productId);
        if (icon) {
            icon.classList.remove('text-gray-600');
            icon.classList.add('text-green-600');
        }
        
        if (typeof showToast === 'function') {
            showToast('Product added to compare!', 'success');
        } else {
        alert('Product added to compare!');
        }
    } else {
        if (typeof showToast === 'function') {
            showToast('Product already in compare list!', 'error');
    } else {
        alert('Product already in compare list!');
        }
    }
}

function removeFromCompare(productId) {
    compareList = compareList.filter(item => item.id !== productId);
    localStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareCount();
    updateCompareDisplay();
    
    // Reset icon color to gray
    const icon = document.querySelector('.compare-icon-' + productId);
    if (icon) {
        icon.classList.remove('text-green-600');
        icon.classList.add('text-gray-600');
    }
}

function clearCompare() {
    // Reset all compare icons to gray before clearing
    compareList.forEach(item => {
        const icon = document.querySelector('.compare-icon-' + item.id);
        if (icon) {
            icon.classList.remove('text-green-600');
            icon.classList.add('text-gray-600');
        }
    });
    
    compareList = [];
    localStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareCount();
    updateCompareDisplay();
}

function updateCompareIcons() {
    // Initialize compare icon states on page load
    compareList.forEach(item => {
        const icon = document.querySelector('.compare-icon-' + item.id);
        if (icon) {
            icon.classList.remove('text-gray-600');
            icon.classList.add('text-green-600');
        }
    });
}

function updateCompareCount() {
    const count = compareList.length;
    const badge = document.getElementById('compare-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
}

function updateCompareDisplay() {
    const container = document.getElementById('compare-items');
    if (!container) {
        return;
    }

    if (compareList.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No products to compare yet.</p>';
    } else {
        container.innerHTML = compareList.map(item => {
            const resolvedImage = item.image && item.image.trim() !== '' ? item.image : 'image/logo.webp';
            const imageHtml = `<img src="${resolvedImage}" alt="${item.name}" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center\'><i class=\'fas fa-image text-gray-400\'></i></div>'">`;
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
    }
}

// Wishlist Modal Functions
function toggleWishlistModal() {
    const modal = document.getElementById('wishlist-modal');
    modal.classList.toggle('hidden');
    updateWishlistDisplay();
}

// Toggle wishlist (add if not exists, remove if exists)
function toggleWishlist(productId, productName, productPrice, productImage, productBrand = '', originalPrice = '', inStock = true) {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    const existingIndex = wishlist.findIndex(item => item.id === productId);
    
    if (existingIndex === -1) {
        // Add to wishlist
        wishlist.push({ 
            id: productId, 
            name: productName, 
            price: productPrice, 
            image: productImage,
            brand: productBrand,
            original_price: originalPrice,
            in_stock: inStock
        });
        window.wishlist = wishlist; // Sync to global
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        
        // Update icon if exists
        var icon = document.querySelector('.wishlist-icon-' + productId);
        if (icon) {
            icon.classList.remove('far', 'text-gray-600');
            icon.classList.add('fas', 'text-red-500');
        }
        
        updateWishlistCount();
        if (typeof showToast === 'function') {
            showToast('Added to wishlist!', 'success');
        } else {
        alert('Added to wishlist!');
        }
    } else {
        // Remove from wishlist
        wishlist.splice(existingIndex, 1);
        window.wishlist = wishlist; // Sync to global
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        
        // Update icon if exists
        var icon = document.querySelector('.wishlist-icon-' + productId);
        if (icon) {
            icon.classList.remove('fas', 'text-red-500');
            icon.classList.add('far', 'text-gray-600');
        }
        
        updateWishlistCount();
        if (typeof showToast === 'function') {
            showToast('Removed from wishlist!', 'info');
        } else {
            alert('Removed from wishlist!');
        }
    }
    
    // Update display if modal is open
    updateWishlistDisplay();
}

function addToWishlist(productId, productName, productPrice, productImage) {
    toggleWishlist(productId, productName, productPrice, productImage);
}

function removeFromWishlist(productId) {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    wishlist = wishlist.filter(item => item.id !== productId);
    window.wishlist = wishlist; // Sync to global
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    
    // Update icon if exists
    var icon = document.querySelector('.wishlist-icon-' + productId);
    if (icon) {
        icon.classList.remove('fas', 'text-red-500');
        icon.classList.add('far', 'text-gray-600');
    }
    
    updateWishlistCount();
    updateWishlistDisplay();
}

function updateWishlistCount() {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    const count = wishlist.length;
    const badge = document.getElementById('wishlist-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
}

function updateWishlistDisplay() {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    const container = document.getElementById('wishlist-items');
    if (!container) return;
    
    if (wishlist.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Your wishlist is empty.</p>';
    } else {
        container.innerHTML = wishlist.map(item => {
            // Ensure price has $ symbol
            let displayPrice = item.price;
            if (!displayPrice.startsWith('$')) {
                displayPrice = '$' + displayPrice;
            }
            
            // Product image - use actual image if available, otherwise show placeholder
            let imageHtml = '';
            if (item.image && item.image.trim() !== '') {
                imageHtml = `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded">`;
            } else {
                imageHtml = `<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-2xl"></i>
                </div>`;
            }
            
            // Category display if available
            const categoryHtml = item.category ? `<p class="text-xs text-gray-500 mb-1">${item.category}</p>` : '';
            
            // Rating display if available
            let ratingHtml = '';
            if (item.rating) {
                const rating = parseFloat(item.rating);
                const fullStars = Math.floor(rating);
                const hasHalfStar = rating % 1 >= 0.5;
                let starsHtml = '';
                for (let i = 0; i < fullStars; i++) {
                    starsHtml += '<i class="fas fa-star text-yellow-400 text-xs"></i>';
                }
                if (hasHalfStar) {
                    starsHtml += '<i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>';
                }
                for (let i = fullStars + (hasHalfStar ? 1 : 0); i < 5; i++) {
                    starsHtml += '<i class="far fa-star text-yellow-400 text-xs"></i>';
                }
                ratingHtml = `<div class="flex items-center gap-1 mb-2">${starsHtml}<span class="text-xs text-gray-500 ml-1">(${rating.toFixed(1)})</span></div>`;
            }
            
            // Combine brand, stock, and price on the same line
            let priceLineHtml = '';
            if (item.original_price && parseFloat(item.original_price) > parseFloat(item.price.replace('$', '').replace(',', ''))) {
                // Show discount price with original price strikethrough
                const originalPrice = item.original_price.startsWith('$') ? item.original_price : '$' + item.original_price;
                priceLineHtml = `
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <div class="flex items-center gap-2">
                            <p class="text-green-600 font-bold text-lg">${displayPrice}</p>
                            <p class="text-gray-400 line-through text-sm">${originalPrice}</p>
                    </div>
                        ${item.brand ? `<span class="text-xs text-gray-500">| By <span class="text-green-600 font-semibold">${item.brand}</span></span>` : ''}
                        ${item.in_stock !== undefined ? (item.in_stock === true || item.in_stock === 'true' || item.in_stock === 1 ? `<span class="text-xs text-green-600 font-semibold"><i class="fas fa-check-circle"></i> In Stock</span>` : `<span class="text-xs text-red-600 font-semibold"><i class="fas fa-times-circle"></i> Out of Stock</span>`) : ''}
                    </div>
                `;
            } else {
                // Just show the price
                priceLineHtml = `
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <p class="text-green-600 font-bold text-lg">${displayPrice}</p>
                        ${item.brand ? `<span class="text-xs text-gray-500">| By <span class="text-green-600 font-semibold">${item.brand}</span></span>` : ''}
                        ${item.in_stock !== undefined ? (item.in_stock === true || item.in_stock === 'true' || item.in_stock === 1 ? `<span class="text-xs text-green-600 font-semibold"><i class="fas fa-check-circle"></i> In Stock</span>` : `<span class="text-xs text-red-600 font-semibold"><i class="fas fa-times-circle"></i> Out of Stock</span>`) : ''}
                </div>
                `;
            }
            
            return `
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow gap-4">
                    <div class="flex items-center gap-4 flex-1 w-full sm:w-auto">
                        <div class="w-24 h-24 sm:w-20 sm:h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${imageHtml}
                        </div>
                        <div class="flex-1 min-w-0">
                            ${categoryHtml}
                            <h3 class="font-semibold text-gray-800 mb-1 text-sm sm:text-base">${item.name}</h3>
                            ${ratingHtml}
                            ${priceLineHtml}
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end sm:justify-start">
                        <button onclick="event.stopPropagation(); addToCart('${item.id}', '${item.name}', '${item.price}', '${item.image || ''}')" class="px-4 py-2 text-green-600 rounded border border-green-600 hover:text-green-700 hover:border-green-700 transition-colors text-sm flex items-center gap-2 whitespace-nowrap">
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
    }
}

// Cart Modal Functions
function toggleCartModal() {
    const modal = document.getElementById('cart-modal');
    modal.classList.toggle('hidden');
    updateCartDisplay();
}

function addToCart(productId, productName, productPrice, productImage, quantity = 1) {
    productImage = resolveProductImage(productImage);
    if (!productImage || typeof productImage !== 'string' || productImage.trim() === '') {
        productImage = 'image/logo.webp';
    }
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ id: productId, name: productName, price: productPrice, image: productImage, quantity: quantity });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
    showToast(`${productName} added to cart`, 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
}

function updateCartQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity = parseInt(quantity);
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            updateCartDisplay();
        }
    }
}

function clearCart() {
    cart = [];
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDisplay();
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
}

function updateCartDisplay() {
    const container = document.getElementById('cart-items');
    const totalElement = document.getElementById('cart-total');
    
    if (container) {
        if (cart.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty.</p>';
            if (totalElement) totalElement.textContent = '$0.00';
        } else {
            let cartUpdated = false;
            cart = cart.map(item => {
                if (!item.image || typeof item.image !== 'string' || item.image.trim() === '') {
                    cartUpdated = true;
                    return { ...item, image: 'image/logo.webp' };
                }
                return item;
            });
            if (cartUpdated) {
                localStorage.setItem('cart', JSON.stringify(cart));
            }

            container.innerHTML = cart.map(item => {
                const price = parseFloat(item.price.replace('$', '').replace(',', ''));
                const subtotal = price * item.quantity;
                let imageHtml = '';
                if (item.image && item.image.trim() !== '') {
                    imageHtml = `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover rounded">`;
                } else {
                    imageHtml = `<div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400"></i>
                    </div>`;
                }
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
                            <input type="number" value="${item.quantity}" min="1" 
                                   onchange="updateCartQuantity('${item.id}', this.value)" 
                                   class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                            <p class="text-gray-700 font-semibold w-20 text-right">$${subtotal.toFixed(2)}</p>
                            <button onclick="removeFromCart('${item.id}')" class="text-red-500 hover:text-red-700 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            const total = cart.reduce((sum, item) => {
                const price = parseFloat(item.price.replace('$', '').replace(',', ''));
                return sum + (price * item.quantity);
            }, 0);

            if (totalElement) totalElement.textContent = '$' + total.toFixed(2);
        }
    }
}

function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    // Close cart modal
    closeModal('cart-modal');
    // Redirect to checkout page
    window.location.href = 'index.php?page=checkout';
}

// Close modal when clicking on backdrop
function closeModalOnBackdrop(event, modalId) {
    if (event.target.id === modalId || event.target.classList.contains('bg-black')) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
    }
}

// Close modal function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Profile Modal Functions
function openProfileModal() {
    closeModal('user-dropdown');
    const modal = document.getElementById('profile-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function saveProfile() {
    alert('Profile updated successfully!');
    closeModal('profile-modal');
    // Here you can add actual profile save logic
    // const firstName = document.querySelector('#profile-modal input[type="text"]').value;
    // Save to database or localStorage
}

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file.');
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size must be less than 5MB.');
            return;
        }
        
        // Create a FileReader to preview the image
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const photoData = e.target.result;
            
            // Get current user ID or username for user-specific storage
            const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
            
            // Save to localStorage with user-specific key
            localStorage.setItem('profilePhoto_' + userId, photoData);
            
            // Update all user icons
            updateUserIcons(photoData);
            
            alert('Photo updated successfully!');
        };
        
        reader.readAsDataURL(file);
    }
}

// Helper function to update all user icons
function updateUserIcons(photoData) {
    // Update profile modal photo
    const profilePreview = document.getElementById('profile-photo-preview');
    const profileIcon = document.getElementById('profile-photo-icon');
    
    if (profilePreview && profileIcon) {
        profilePreview.src = photoData;
        profilePreview.classList.remove('hidden');
        profileIcon.classList.add('hidden');
    }
    
    // Update header account button icon
    const headerPhoto = document.getElementById('header-user-photo');
    const headerIcon = document.getElementById('header-user-icon');
    
    if (headerPhoto && headerIcon) {
        headerPhoto.src = photoData;
        headerPhoto.classList.remove('hidden');
        headerIcon.classList.add('hidden');
    }
    
    // Update dropdown menu icon
    const dropdownPhoto = document.getElementById('dropdown-user-photo');
    const dropdownIcon = document.getElementById('dropdown-user-icon');
    
    if (dropdownPhoto && dropdownIcon) {
        dropdownPhoto.src = photoData;
        dropdownPhoto.classList.remove('hidden');
        dropdownIcon.classList.add('hidden');
    }
}

// Load saved profile photo on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get current user ID or username for user-specific storage
    const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
    
    // Clear old generic profilePhoto if it exists (migration from old system)
    const oldPhoto = localStorage.getItem('profilePhoto');
    if (oldPhoto && userId !== 'guest') {
        localStorage.removeItem('profilePhoto');
    }
    
    // Only load user-specific photo
    const savedPhoto = localStorage.getItem('profilePhoto_' + userId);
    if (savedPhoto) {
        updateUserIcons(savedPhoto);
    } else {
        // Ensure no photo is displayed if user hasn't uploaded one
        const profilePreview = document.getElementById('profile-photo-preview');
        const profileIcon = document.getElementById('profile-photo-icon');
        if (profilePreview && profileIcon) {
            profilePreview.src = '';
            profilePreview.classList.add('hidden');
            profileIcon.classList.remove('hidden');
        }
        const headerPhoto = document.getElementById('header-user-photo');
        const headerIcon = document.getElementById('header-user-icon');
        if (headerPhoto && headerIcon) {
            headerPhoto.src = '';
            headerPhoto.classList.add('hidden');
            headerIcon.classList.remove('hidden');
        }
        const dropdownPhoto = document.getElementById('dropdown-user-photo');
        const dropdownIcon = document.getElementById('dropdown-user-icon');
        if (dropdownPhoto && dropdownIcon) {
            dropdownPhoto.src = '';
            dropdownPhoto.classList.add('hidden');
            dropdownIcon.classList.remove('hidden');
        }
    }
});

// Orders Modal Functions
function openOrdersModal() {
    closeModal('user-dropdown');
    const modal = document.getElementById('orders-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

// Addresses Modal Functions
var addresses = JSON.parse(localStorage.getItem('addresses')) || [
    { id: '1', label: 'Home Address', street: '123 Main Street', city: 'City', state: 'State', zip: '12345', country: 'United States', isDefault: true }
];

var editingAddressId = null;

function openAddressesModal() {
    closeModal('user-dropdown');
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
    const form = document.getElementById('address-form');
    
    editingAddressId = addressId;
    
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
            form.reset();
            document.getElementById('address-id').value = '';
        }
        modal.classList.remove('hidden');
    }
}

function closeAddressForm() {
    const modal = document.getElementById('address-form-modal');
    if (modal) {
        modal.classList.add('hidden');
        editingAddressId = null;
        document.getElementById('address-form').reset();
    }
}

function saveAddress(event) {
    event.preventDefault();
    
    const addressData = {
        id: editingAddressId || Date.now().toString(),
        label: document.getElementById('address-label').value,
        street: document.getElementById('address-street').value,
        city: document.getElementById('address-city').value,
        state: document.getElementById('address-state').value,
        zip: document.getElementById('address-zip').value,
        country: document.getElementById('address-country').value,
        isDefault: document.getElementById('address-default').checked
    };
    
    // If setting as default, remove default from other addresses
    if (addressData.isDefault) {
        addresses.forEach(addr => {
            if (addr.id !== addressData.id) {
                addr.isDefault = false;
            }
        });
    }
    
    if (editingAddressId) {
        // Update existing address
        const index = addresses.findIndex(a => a.id === editingAddressId);
        if (index !== -1) {
            addresses[index] = addressData;
        }
        alert('Address updated successfully!');
    } else {
        // Add new address
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

// Load addresses on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAddresses();
});

// Settings Modal Functions
function openSettingsModal() {
    closeModal('user-dropdown');
    const modal = document.getElementById('settings-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function saveSettings() {
    alert('Settings saved successfully!');
    closeModal('settings-modal');
    // Here you can save settings to localStorage or database
}

function changePassword() {
    const modal = document.getElementById('change-password-modal');
    const form = document.getElementById('password-form');
    const errorDiv = document.getElementById('password-error');
    
    if (modal && form) {
        form.reset();
        errorDiv.classList.add('hidden');
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
    errorDiv.classList.add('hidden');
    
    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        errorMessage.textContent = 'All fields are required.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (newPassword.length < 6) {
        errorMessage.textContent = 'New password must be at least 6 characters long.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        errorMessage.textContent = 'New password and confirm password do not match.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (currentPassword === newPassword) {
        errorMessage.textContent = 'New password must be different from current password.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Here you would typically verify the current password with the server
    // For now, we'll just show a success message
    // In a real application, you would make an API call to verify and update the password
    
    // Simulate password verification (remove in production)
    const savedPassword = localStorage.getItem('userPassword') || 'currentpass123';
    
    if (currentPassword !== savedPassword) {
        errorMessage.textContent = 'Current password is incorrect.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Save new password (in production, this would be sent to server)
    localStorage.setItem('userPassword', newPassword);
    
    // Show success and close modal
    alert('Password changed successfully!');
    closePasswordModal();
}

function enable2FA() {
    const enable = confirm('Do you want to enable Two-Factor Authentication?');
    if (enable) {
        alert('Two-Factor Authentication enabled! Check your email for setup instructions.');
    }
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

// Hot Deals Functions
function scrollHotDeals(direction) {
    const container = document.getElementById('hot-deals-container');
    if (container) {
        const scrollAmount = 300;
        if (direction === 'left') {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }
}

// Countdown Timer Functions
function updateCountdown(elementId, hours, minutes, seconds) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let totalSeconds = hours * 3600 + minutes * 60 + seconds;
    
    const timer = setInterval(function() {
        if (totalSeconds <= 0) {
            clearInterval(timer);
            element.textContent = '00:00:00';
            element.parentElement.parentElement.style.opacity = '0.5';
            return;
        }
        
        const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
        const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
        const s = (totalSeconds % 60).toString().padStart(2, '0');
        
        element.textContent = `${h}:${m}:${s}`;
        totalSeconds--;
    }, 1000);
}

// All Categories and Products Functions
let currentCategoryFilter = 'all';
let currentSort = 'default';

// Define all categories
const allCategories = [
    { id: 'cake-milk', name: 'Cake & Milk', icon: 'fa-utensils', color: 'yellow', items: 11 },
    { id: 'coffee-tea', name: 'Coffee & Tea', icon: 'fa-coffee', color: 'orange', items: 13 },
    { id: 'pet-foods', name: 'Pet Foods', icon: 'fa-bone', color: 'amber', items: 8 },
    { id: 'vegetables', name: 'Vegetables', icon: 'fa-leaf', color: 'green', items: 20 },
    { id: 'fruits', name: 'Fresh Fruits', icon: 'fa-apple-alt', color: 'red', items: 15 },
    { id: 'dairy', name: 'Milks & Dairies', icon: 'fa-cheese', color: 'yellow', items: 12 },
    { id: 'meats', name: 'Meats', icon: 'fa-drumstick-bite', color: 'red', items: 9 },
    { id: 'seafood', name: 'Seafood', icon: 'fa-fish', color: 'blue', items: 7 },
    { id: 'snacks', name: 'Snacks', icon: 'fa-cookie', color: 'amber', items: 15 },
    { id: 'beverages', name: 'Beverages', icon: 'fa-wine-bottle', color: 'purple', items: 10 },
    { id: 'baking', name: 'Baking Material', icon: 'fa-wheat', color: 'amber', items: 6 },
    { id: 'organic', name: 'Organic Products', icon: 'fa-seedling', color: 'green', items: 14 }
];

// Category to filter mapping
const categoryMapping = {
    'cake-milk': 'baking',
    'coffee-tea': 'beverages',
    'pet-foods': 'all',
    'vegetables': 'vegetables',
    'fruits': 'fruits',
    'dairy': 'dairy',
    'meats': 'meats',
    'seafood': 'seafood',
    'snacks': 'snacks',
    'beverages': 'beverages',
    'baking': 'baking',
    'organic': 'all'
};

function loadAllCategories() {
    const container = document.getElementById('all-categories-grid');
    if (!container) {
        console.error('Categories container not found');
        return;
    }
    
    if (!allCategories || allCategories.length === 0) {
        console.error('Categories array is empty or undefined');
        container.innerHTML = '<p class="col-span-full text-gray-600 text-center py-8">No categories available.</p>';
        return;
    }
    
    const colorClasses = {
        yellow: { bg: 'bg-yellow-100', text: 'text-yellow-600' },
        orange: { bg: 'bg-orange-100', text: 'text-orange-600' },
        amber: { bg: 'bg-amber-100', text: 'text-amber-600' },
        green: { bg: 'bg-green-100', text: 'text-green-600' },
        red: { bg: 'bg-red-100', text: 'text-red-600' },
        blue: { bg: 'bg-blue-100', text: 'text-blue-600' },
        purple: { bg: 'bg-purple-100', text: 'text-purple-600' }
    };
    
    try {
        container.innerHTML = allCategories.map(category => {
            const colors = colorClasses[category.color] || colorClasses.amber;
            const filterCategory = categoryMapping[category.id] || 'all';
            return `
            <div onclick="filterProductsByCategory('${filterCategory}')" class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-all cursor-pointer transform hover:scale-105">
                <div class="w-16 h-16 ${colors.bg} rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="fas ${category.icon} text-3xl ${colors.text}"></i>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm mb-1">${category.name}</h3>
                <p class="text-xs text-gray-500">${category.items} items</p>
            </div>
        `;
        }).join('');
        console.log('Categories loaded successfully:', allCategories.length);
    } catch (error) {
        console.error('Error loading categories:', error);
        container.innerHTML = '<p class="col-span-full text-red-600 text-center py-8">Error loading categories. Please refresh the page.</p>';
    }
}

function filterProductsByCategory(category) {
    currentCategoryFilter = category;
    filterProducts(category);
    
    // Update active filter button
    document.querySelectorAll('.category-filter-btn').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('bg-white', 'text-gray-700');
            btn.classList.add('bg-green-600', 'text-white');
        } else {
            btn.classList.remove('bg-green-600', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700');
        }
    });
}

function filterByFeaturedCategory(category) {
    // Store the selected category in localStorage
    localStorage.setItem('selectedCategory', category);
    
    // Navigate to All Products page with category filter as URL parameter
    window.location.href = 'index.php?page=all-products&category=' + encodeURIComponent(category);
}

function filterPopularProducts(category) {
    // Find the Popular Products section
    const headings = document.querySelectorAll('h2');
    let popularSection = null;
    
    headings.forEach(h2 => {
        if (h2.textContent.trim() === 'Popular Products') {
            popularSection = h2.closest('.bg-gray-50');
        }
    });
    
    if (!popularSection) return;
    
    const productGrid = popularSection.querySelector('.grid');
    if (!productGrid) return;
    
    // Remove any existing "no products" message
    const existingMessage = productGrid.querySelector('.no-products-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const products = productGrid.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    products.forEach(product => {
        const productCategory = String(product.dataset.category);
        const filterCategory = String(category);
        
        if (category === 'all' || productCategory === filterCategory) {
            product.style.display = 'block';
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    // Show "no products" message if no products are visible
    if (visibleCount === 0 && category !== 'all') {
        const noProductsMsg = document.createElement('div');
        noProductsMsg.className = 'no-products-message col-span-full text-center text-gray-500 py-12';
        noProductsMsg.innerHTML = '<p>No products found in this category.</p>';
        productGrid.appendChild(noProductsMsg);
    }
    
    // Update active filter button in Popular Products section only
    const popularButtons = popularSection.querySelectorAll('.category-filter-btn');
    popularButtons.forEach(btn => {
        const btnCategory = String(btn.dataset.category);
        const filterCategory = String(category);
        
        if (btnCategory === filterCategory) {
            btn.classList.remove('bg-white', 'text-gray-700');
            btn.classList.add('bg-green-600', 'text-white');
        } else {
            btn.classList.remove('bg-green-600', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700');
        }
    });
}

function filterNewArrivals(category) {
    // Find the New Arrival section by finding the h2 with "New Arrivals" text
    const headings = document.querySelectorAll('h2');
    let newArrivalSection = null;
    
    headings.forEach(h2 => {
        if (h2.textContent.trim() === 'New Arrivals') {
            newArrivalSection = h2.closest('.bg-white');
        }
    });
    
    if (!newArrivalSection) return;
    
    const productGrid = newArrivalSection.querySelector('.grid');
    if (!productGrid) return;
    
    // Remove any existing "no products" message
    const existingMessage = productGrid.querySelector('.no-products-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const products = productGrid.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    products.forEach(product => {
        const productCategory = String(product.dataset.category);
        const filterCategory = String(category);
        
        if (category === 'all' || productCategory === filterCategory) {
            product.style.display = 'block';
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    // Show "no products" message if no products are visible
    if (visibleCount === 0 && category !== 'all') {
        const noProductsMsg = document.createElement('div');
        noProductsMsg.className = 'no-products-message col-span-full text-center text-gray-500 py-12';
        noProductsMsg.innerHTML = '<p>No products found in this category.</p>';
        productGrid.appendChild(noProductsMsg);
    }
    
    // Update active filter button in New Arrival section only
    const newArrivalButtons = newArrivalSection.querySelectorAll('.category-filter-btn');
    newArrivalButtons.forEach(btn => {
        const btnCategory = String(btn.dataset.category);
        const filterCategory = String(category);
        
        if (btnCategory === filterCategory) {
            btn.classList.remove('bg-white', 'text-gray-700');
            btn.classList.add('bg-green-600', 'text-white');
        } else {
            btn.classList.remove('bg-green-600', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700');
        }
    });
}

function filterProducts(category) {
    currentCategoryFilter = category;
    const products = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    products.forEach(product => {
        const productCategory = product.dataset.category;
        
        if (category === 'all' || productCategory === category) {
            product.style.display = 'block';
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    // Update product count
    updateProductsCount();
    
    // Apply current sort
    if (currentSort !== 'default') {
        sortProducts();
    }
    
    // Scroll to products section
    const productsSection = document.getElementById('all-products-grid');
    if (productsSection) {
        setTimeout(() => {
            productsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

function sortProducts() {
    const sortValue = document.getElementById('sort-products').value;
    currentSort = sortValue;
    
    const container = document.getElementById('all-products-grid');
    if (!container) return;
    
    const products = Array.from(container.querySelectorAll('.product-card'));
    const visibleProducts = products.filter(p => p.style.display !== 'none');
    
    visibleProducts.sort((a, b) => {
        switch(sortValue) {
            case 'price-low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price-high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'name':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'rating':
                return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
            default:
                return 0;
        }
    });
    
    // Reorder products in DOM
    visibleProducts.forEach(product => {
        container.appendChild(product);
    });
}

function updateProductsCount() {
    const products = document.querySelectorAll('.product-card');
    const visibleProducts = Array.from(products).filter(p => {
        const display = window.getComputedStyle(p).display;
        return display !== 'none';
    });
    const countElement = document.getElementById('products-count');
    
    if (countElement) {
        if (currentCategoryFilter === 'all') {
            countElement.textContent = `Showing all ${visibleProducts.length} products`;
        } else {
            const categoryNames = {
                'baking': 'Baking Material',
                'fruits': 'Fresh Fruits',
                'dairy': 'Milks & Dairies',
                'meats': 'Meats',
                'vegetables': 'Vegetables',
                'seafood': 'Seafood',
                'snacks': 'Snacks',
                'beverages': 'Beverages'
            };
            const categoryName = categoryNames[currentCategoryFilter] || currentCategoryFilter;
            countElement.textContent = `Showing ${visibleProducts.length} products in ${categoryName}`;
        }
    }
}

// Initialize countdown timers on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing...');
    
    // Initialize countdown timers for hot deals
    updateCountdown('countdown-1', 23, 59, 45);
    updateCountdown('countdown-2', 18, 30, 22);
    updateCountdown('countdown-3', 12, 15, 30);
    updateCountdown('countdown-4', 6, 45, 10);
    
    // Load all categories
    console.log('Loading categories...');
    loadAllCategories();
    
    // Initialize product filtering
    console.log('Updating product count...');
    updateProductsCount();
    
    
    // Ensure all products are visible by default
    const allProducts = document.querySelectorAll('.product-card');
    console.log('Total products found:', allProducts.length);
    allProducts.forEach(product => {
        product.style.display = 'block';
    });
    
    console.log('Initialization complete!');
});

// Allow adding to cart from product cards (to be called from product buttons)
// Example: <button onclick="event.stopPropagation(); addToCart('prod1', 'Product Name', '$29.99', 'image.jpg')">Add to Cart</button>

// Hot Deals Functions
function scrollCategories(direction) {
    const container = document.getElementById('featured-categories-container');
    if (container) {
        const scrollAmount = 300;
        if (direction === 'left') {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }
}

</script>

<?php endif; // End of headerOnly check ?>
