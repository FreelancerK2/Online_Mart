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

<!-- Page Content Spacing -->
<div class="pt-[100px]"></div>

<script>
// Define filterProductsByCategory function early - before HTML that uses it
// This must be defined before the category onclick handlers
window.filterProductsByCategory = function(categoryId, categoryName) {
    console.log('filterProductsByCategory called with:', categoryId, '(', typeof categoryId, ')', categoryName);
    
    // Filter Best Sellers
    const bestSellersGrid = document.getElementById('bestsellers-grid');
    const bestSellersTitle = document.getElementById('bestsellers-title');
    const bestSellersReset = document.getElementById('bestsellers-reset');
    
    if (bestSellersGrid && bestSellersTitle && bestSellersReset) {
        const bestSellersCards = bestSellersGrid.querySelectorAll('.product-card');
        console.log('Best Sellers cards found:', bestSellersCards.length);
        
        if (bestSellersCards.length === 0) {
            console.warn('No product cards found in Best Sellers section. Checking if products exist...');
            // Check if there are any child elements
            console.log('Best Sellers grid children:', bestSellersGrid.children.length);
            // Check all children to see what's there
            Array.from(bestSellersGrid.children).forEach((child, idx) => {
                console.log(`Child ${idx + 1}:`, {
                    tagName: child.tagName,
                    className: child.className,
                    hasProductCard: child.classList.contains('product-card'),
                    innerHTML: child.innerHTML.substring(0, 100)
                });
            });
        }
        
        let visibleCount = 0;
        bestSellersCards.forEach((card, index) => {
            const cardCategoryId = card.getAttribute('data-category-id');
            const cardCategoryIdNum = cardCategoryId ? parseInt(cardCategoryId) : null;
            const targetCategoryIdNum = parseInt(categoryId);
            
            console.log(`Best Seller card ${index + 1}:`, {
                'data-category-id': cardCategoryId,
                'parsed': cardCategoryIdNum,
                'target': targetCategoryIdNum,
                'match': cardCategoryIdNum === targetCategoryIdNum
            });
            
            if (cardCategoryIdNum === targetCategoryIdNum) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        console.log('Best Sellers visible after filter:', visibleCount);
        
        // Update title
        bestSellersTitle.textContent = `${categoryName} - Best Sellers`;
        
        // Show reset button
        bestSellersReset.classList.remove('hidden');
        
        // If no cards match, show a message
        if (visibleCount === 0 && bestSellersCards.length > 0) {
            console.warn('No products found for category', categoryId, 'in Best Sellers');
        }
    } else {
        console.error('Best Sellers elements not found:', {
            grid: !!bestSellersGrid,
            title: !!bestSellersTitle,
            reset: !!bestSellersReset
        });
    }
    
    // Filter New Arrivals
    const newArrivalsGrid = document.getElementById('newarrivals-grid');
    const newArrivalsTitle = document.getElementById('newarrivals-title');
    const newArrivalsReset = document.getElementById('newarrivals-reset');
    
    if (newArrivalsGrid && newArrivalsTitle && newArrivalsReset) {
        const newArrivalsCards = newArrivalsGrid.querySelectorAll('.product-card');
        console.log('New Arrivals cards found:', newArrivalsCards.length);
        
        let visibleCount = 0;
        newArrivalsCards.forEach((card, index) => {
            const cardCategoryId = card.getAttribute('data-category-id');
            const cardCategoryIdNum = cardCategoryId ? parseInt(cardCategoryId) : null;
            const targetCategoryIdNum = parseInt(categoryId);
            
            console.log(`New Arrival card ${index + 1}:`, {
                'data-category-id': cardCategoryId,
                'parsed': cardCategoryIdNum,
                'target': targetCategoryIdNum,
                'match': cardCategoryIdNum === targetCategoryIdNum
            });
            
            if (cardCategoryIdNum === targetCategoryIdNum) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        console.log('New Arrivals visible after filter:', visibleCount);
        
        // Update title
        newArrivalsTitle.textContent = `${categoryName} - New Arrivals`;
        
        // Show reset button
        newArrivalsReset.classList.remove('hidden');
    } else {
        console.error('New Arrivals elements not found:', {
            grid: !!newArrivalsGrid,
            title: !!newArrivalsTitle,
            reset: !!newArrivalsReset
        });
    }
    
    // Scroll to Best Sellers section if it exists
    if (bestSellersGrid) {
        bestSellersGrid.closest('section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
</script>

<!-- Shop Hero Section -->
<section class="py-12">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Shop All Products</h1>
            <p class="text-gray-600 text-lg mb-6">Discover our complete collection of fresh groceries and household essentials</p>
            <a href="index.php?page=all-products" class="inline-block px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-colors">View All Products</a>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="py-8">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Vendors</h2>
            <div class="flex items-center gap-4">
                <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i class="fas fa-chevron-left text-gray-600"></i>
            </button>
                <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i class="fas fa-chevron-right text-gray-600"></i>
            </button>
                </div>
                </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-10 gap-4">
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
                    $vendorName = isset($cat['vendor_name']) ? $cat['vendor_name'] : '';
                    ?>
                    <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer" onclick="filterProductsByCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>')">
                        <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3 overflow-hidden relative">
                            <?php 
                            $imagePath = isset($cat['image_path']) ? trim($cat['image_path']) : '';
                            
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
</section>

<!-- Best Sellers Section -->
<section class="py-12 bg-gray-50">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <h2 id="bestsellers-title" class="text-3xl font-bold text-gray-800">Best Sellers</h2>
                <button id="bestsellers-reset" onclick="resetVendorFilter()" class="hidden text-xs text-gray-500 hover:text-gray-700 underline">Show All</button>
            </div>
            <a href="index.php?page=all-products" class="text-green-600 hover:text-green-700 font-semibold">View All <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6" id="bestsellers-grid">
            <?php
            // Fetch products with "Best Sellers" promotion status
            $productsQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon, p.vendor_id, v.name as vendor_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              LEFT JOIN vendors v ON p.vendor_id = v.id 
                              WHERE p.promotion_status LIKE '%best_sellers%'
                              ORDER BY p.created_at DESC 
                              LIMIT 12";
            $productsResult = mysqli_query($conn, $productsQuery);
            
            if (mysqli_num_rows($productsResult) > 0) {
                while ($product = mysqli_fetch_assoc($productsResult)) {
                    $inStock = $product['quantity'] > 0;
                    $categoryName = $product['category_name'] ? $product['category_name'] : 'Uncategorized';
                    $categoryIcon = $product['category_icon'] ? $product['category_icon'] : 'fas fa-box';
                    // Check image path - use directly if exists, don't check file_exists as it may have path issues
                    $productImageRaw = !empty($product['image_path']) ? $product['image_path'] : '';
                    $productImageAttr = htmlspecialchars($productImageRaw, ENT_QUOTES, 'UTF-8');
                    $productImageJs = js_str($productImageRaw);
                    $vendorName = isset($product['vendor_name']) && !empty($product['vendor_name']) ? $product['vendor_name'] : '';
                    $rating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 4.0; // Default to 4.0 if not set
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                    $productBrandValue = $vendorName ?: $categoryName;
                    $productBrandJs = js_str($productBrandValue);
                    $productKey = (string) $product['id'];
                    $productKeyJs = js_str($productKey);
                    $productNameJs = js_str($product['name'] ?? '');
                    $effectivePrice = isset($product['discount_price']) && $product['discount_price'] && $product['discount_price'] < $product['price'] ? $product['discount_price'] : $product['price'];
                    $effectivePriceJs = js_str('$' . number_format($effectivePrice, 2));
                    $originalPriceJs = js_str('$' . number_format($product['price'], 2));
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow product-card cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $product['id']; ?>'" data-vendor-id="<?php echo isset($product['vendor_id']) ? $product['vendor_id'] : ''; ?>" data-vendor="<?php echo htmlspecialchars($product['vendor_name'] ?? $categoryName); ?>" data-category-id="<?php echo isset($product['category_id']) ? $product['category_id'] : ''; ?>" data-product-image="<?php echo $productImageAttr; ?>">
                <div class="relative">
                            <!-- Discount Badge -->
                            <?php if (isset($product['discount_percentage']) && $product['discount_percentage'] > 0): ?>
                                <?php 
                                $discountPercent = floatval($product['discount_percentage']);
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
                                <div class="absolute top-2 left-2 <?php echo $badgeColor; ?> text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                                    <i class="fas fa-tag text-white"></i>
                                    <span><?php echo number_format($discountPercent, 0); ?>% OFF</span>
                </div>
                            <?php elseif ($product['quantity'] > 0): ?>
                    <div class="absolute top-2 left-2 bg-green-600 text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                        <i class="fas fa-fire text-white"></i>
                                    <span>In Stock</span>
                    </div>
                    <?php endif; ?>
                    <div class="absolute top-2 right-2 flex gap-2 z-10">
                                <button onclick="event.stopPropagation(); toggleWishlist(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $effectivePriceJs; ?>, <?php echo $productImageJs; ?>, <?php echo $productBrandJs; ?>, <?php echo $originalPriceJs; ?>, <?php echo $inStock ? 'true' : 'false'; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors">
                                    <i class="far fa-heart text-gray-600 wishlist-icon-<?php echo $product['id']; ?>"></i>
                        </button>
                                <button onclick="event.stopPropagation(); addToCompare(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $effectivePriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-50 transition-colors" title="Add to Compare">
                                    <i class="fas fa-balance-scale text-gray-600 text-sm compare-icon-<?php echo $product['id']; ?>"></i>
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
                            
                            <!-- Star Rating -->
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
                            
                            <!-- Vendor Name -->
                            <?php if (!empty($vendorName)): ?>
                                <p class="text-xs text-gray-500 mb-2">By <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($vendorName); ?></span></p>
                            <?php endif; ?>
                            
                            <!-- Price -->
                    <div class="flex items-center gap-2 mb-3">
                                <span class="text-lg font-bold text-green-600">$<?php echo number_format($effectivePrice, 2); ?></span>
                                <?php if (isset($product['discount_price']) && $product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                    <span class="text-sm text-gray-400 line-through">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                            <?php if ($inStock): ?>
                                <button onclick="event.stopPropagation(); addToCart(this, <?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $effectivePriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm text-center" data-product-image="<?php echo $productImageAttr; ?>">
                        Add to Cart
                    </button>
                    <?php else: ?>
                        <button onclick="event.stopPropagation(); return false;" disabled class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm text-center">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
                    <?php
                }
            } else {
                echo '<div class="col-span-full text-center text-gray-500 py-12">No products available yet. Check back soon!</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- New Arrivals Section -->
<section class="py-12">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <h2 id="newarrivals-title" class="text-3xl font-bold text-gray-800">New Arrivals</h2>
                <button id="newarrivals-reset" onclick="resetVendorFilter()" class="hidden text-xs text-gray-500 hover:text-gray-700 underline">Show All</button>
            </div>
            <a href="index.php?page=all-products" class="text-green-600 hover:text-green-700 font-semibold">View All <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6" id="newarrivals-grid">
            <?php
            // Fetch products with "New Arrival" promotion status
            $newProductsQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon, p.vendor_id, v.name as vendor_name 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.id 
                                 LEFT JOIN vendors v ON p.vendor_id = v.id 
                                 WHERE p.promotion_status LIKE '%new_arrival%'
                                 ORDER BY p.created_at DESC 
                                 LIMIT 6";
            $newProductsResult = mysqli_query($conn, $newProductsQuery);
            
            if (mysqli_num_rows($newProductsResult) > 0) {
                while ($product = mysqli_fetch_assoc($newProductsResult)) {
                    $inStock = $product['quantity'] > 0;
                    $categoryName = $product['category_name'] ? $product['category_name'] : 'Uncategorized';
                    $categoryIcon = $product['category_icon'] ? $product['category_icon'] : 'fas fa-box';
                    // Check image path - use directly if exists, don't check file_exists as it may have path issues
                    $productImageRaw = !empty($product['image_path']) ? $product['image_path'] : '';
                    $productImageAttr = htmlspecialchars($productImageRaw, ENT_QUOTES, 'UTF-8');
                    $productImageJs = js_str($productImageRaw);
                    $vendorName = isset($product['vendor_name']) && !empty($product['vendor_name']) ? $product['vendor_name'] : '';
                    $rating = isset($product['rating']) && $product['rating'] > 0 ? floatval($product['rating']) : 4.0; // Default to 4.0 if not set
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;

                    $productBrandValue = $vendorName ?: $categoryName;
                    $productBrandJs = js_str($productBrandValue);
                    $productKey = (string) $product['id'];
                    $productKeyJs = js_str($productKey);
                    $productNameJs = js_str($product['name'] ?? '');
                    $displayPrice = isset($product['discount_price']) && $product['discount_price'] < $product['price'] ? $product['discount_price'] : $product['price'];
                    $originalPrice = $product['price'];
                    $displayPriceJs = js_str('$' . number_format($displayPrice, 2));
                    $originalPriceJs = js_str('$' . number_format($originalPrice, 2));
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow product-card cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $product['id']; ?>'" data-vendor-id="<?php echo isset($product['vendor_id']) ? $product['vendor_id'] : ''; ?>" data-vendor="<?php echo htmlspecialchars($product['vendor_name'] ?? $categoryName); ?>" data-category-id="<?php echo isset($product['category_id']) ? $product['category_id'] : ''; ?>" data-product-image="<?php echo $productImageAttr; ?>">
                <div class="relative">
                            <!-- Discount Badge (priority over New badge) -->
                            <?php if (isset($product['discount_percentage']) && $product['discount_percentage'] > 0): ?>
                                <?php 
                                $discountPercent = floatval($product['discount_percentage']);
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
                                <div class="absolute top-2 left-2 <?php echo $badgeColor; ?> text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                                    <i class="fas fa-tag text-white"></i>
                                    <span><?php echo number_format($discountPercent, 0); ?>% OFF</span>
                    </div>
                    <?php else: ?>
                    <div class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold z-10">New</div>
                            <?php endif; ?>
                    <div class="absolute top-2 right-2 flex gap-2 z-10">
                                <button onclick="event.stopPropagation(); toggleWishlist(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>, <?php echo $productBrandJs; ?>, <?php echo $originalPriceJs; ?>, <?php echo $inStock ? 'true' : 'false'; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors">
                                    <i class="far fa-heart text-gray-600 wishlist-icon-<?php echo $product['id']; ?>"></i>
                        </button>
                                <button onclick="event.stopPropagation(); addToCompare(<?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-50 transition-colors" title="Add to Compare">
                                    <i class="fas fa-balance-scale text-gray-600 text-sm compare-icon-<?php echo $product['id']; ?>"></i>
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
                            
                            <!-- Star Rating -->
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
                            
                            <!-- Vendor Name -->
                            <?php if (!empty($vendorName)): ?>
                                <p class="text-xs text-gray-500 mb-2">By <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($vendorName); ?></span></p>
                            <?php endif; ?>
                            
                            <!-- Price -->
                    <div class="flex items-center gap-2 mb-3">
                                <?php 
                                $displayPrice = $product['price'];
                                if (isset($product['discount_price']) && $product['discount_price'] && $product['discount_price'] < $product['price']) {
                                    $displayPrice = $product['discount_price'];
                                }
                                ?>
                                <span class="text-lg font-bold text-green-600">$<?php echo number_format($displayPrice, 2); ?></span>
                                <?php if (isset($product['discount_price']) && $product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                    <span class="text-sm text-gray-400 line-through">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                    </div>
                            <?php if ($inStock): ?>
                                <button onclick="event.stopPropagation(); addToCart(this, <?php echo $productKeyJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm text-center" data-product-image="<?php echo $productImageAttr; ?>">
                        Add to Cart
                    </button>
                    <?php else: ?>
                        <button onclick="event.stopPropagation(); return false;" disabled class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm text-center">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
                    <?php
                }
            } else {
                echo '<div class="col-span-full text-center text-gray-500 py-12">No products available yet. Check back soon!</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Old hardcoded products removed -->
<!-- <div class="relative">
                    <div class="absolute top-2 left-2 bg-green-600 text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                        <i class="fas fa-fire text-white"></i>
                        <span>13% OFF</span>
                    </div>
                    <div class="absolute top-2 right-2 flex gap-2 z-10">
                        <button onclick="event.stopPropagation(); toggleWishlist('2', 'Seeds of Change Organic Red Rice', '$28.85', '', 'NestFood', '$32.00', true)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors">
                            <i class="far fa-heart text-gray-600 wishlist-icon-2"></i>
                        </button>
                        <button onclick="event.stopPropagation(); addToCompare('2', 'Seeds of Change Organic Red Rice', '$28.85', '')" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-50 transition-colors" title="Add to Compare">
                            <i class="fas fa-balance-scale text-gray-600 text-sm compare-icon-2"></i>
                        </button>
                    </div>
                    <div class="h-48 bg-gradient-to-br from-amber-50 to-orange-50 flex items-center justify-center">
                        <i class="fas fa-wheat text-6xl text-amber-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Fresh Fruit</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">Seeds of Change Organic Red Rice</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs text-gray-500 ml-1">(4.0)</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg font-bold text-green-600">$28.85</span>
                        <span class="text-sm text-gray-400 line-through">$32.00</span>
                    </div>
                    <?php if ($productStock[2]): ?>
                        <button onclick="event.stopPropagation(); addToCart(this, '2', 'Seeds of Change Organic Red Rice', '$28.85', '')" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm text-center" data-product-image="">
                        Add to Cart
                    </button>
                    <?php else: ?>
                        <button onclick="event.stopPropagation(); return false;" disabled class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm text-center">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>

<!-- CTA Section -->
<section class="py-16">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-10 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Can't Find What You're Looking For?</h2>
            <p class="text-green-50 mb-6 text-lg">Browse our complete product catalog</p>
            <a href="index.php?page=all-products" class="inline-block px-8 py-3 bg-white text-green-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors">View All Products</a>
        </div>
    </div>
</section>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-[80px] right-6 z-[100] space-y-2 pointer-events-none"></div>

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

<script>
// Initialize cart from localStorage
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Initialize wishlist from localStorage (global)
window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];

// Initialize compareList from localStorage
let compareList = JSON.parse(localStorage.getItem('compareList')) || [];

function resolveProductImage(productImage, trigger) {
    if (productImage && typeof productImage === 'string' && productImage.trim() !== '') {
        return productImage;
    }

    let button = trigger || null;
    const evt = window.event || null;
    if (!button && evt && evt.target) {
        button = evt.target.closest('button');
    }

    if (button) {
        const buttonData = button.getAttribute('data-product-image');
        if (buttonData && buttonData.trim() !== '') {
            return buttonData.trim();
        }

        const dataCard = button.closest('[data-product-image]');
        if (dataCard && dataCard.dataset.productImage && dataCard.dataset.productImage.trim() !== '') {
            return dataCard.dataset.productImage.trim();
        }

        const fallbackImg = button.closest('div')?.querySelector('img');
        if (fallbackImg && fallbackImg.src) {
            return fallbackImg.src;
        }
    }

    let current = button || (evt ? (evt.currentTarget || evt.target) : null);

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

// Category and Vendor filter variables
let selectedVendorId = null;
let selectedVendorName = null;
let selectedCategoryId = null;
let selectedCategoryName = null;

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    let colors;
    if (type === 'error') {
        colors = { bg: 'bg-red-600', icon: 'fas fa-exclamation-circle' };
    } else if (type === 'info') {
        colors = { bg: 'bg-blue-600', icon: 'fas fa-info-circle' };
    } else {
        colors = { bg: 'bg-green-600', icon: 'fas fa-check-circle' };
    }
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

// Cart Modal Functions
function toggleCartModal() {
    const modal = document.getElementById('cart-modal');
    modal.classList.toggle('hidden');
    updateCartDisplay();
}

function addToCart() {
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

    productImage = resolveProductImage(productImage, trigger);
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
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty.</p>';
        if (totalElement) totalElement.textContent = '$0.00';
    } else {
        container.innerHTML = cart.map(item => {
            const price = parseFloat(item.price.replace('$', '').replace(',', ''));
            const subtotal = price * item.quantity;
            const imageSrc = item.image && item.image.trim() !== '' ? item.image : 'image/logo.webp';
            const imageHtml = `<img src="${imageSrc}" alt="${item.name}" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center\'><i class=\'fas fa-image text-gray-400\'></i></div>'">`;
            return `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg" data-product-image="${imageSrc.replace(/"/g, '&quot;')}">
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

function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    alert('Redirecting to checkout page...');
}

// Wishlist Modal Functions
function toggleWishlistModal() {
    const modal = document.getElementById('wishlist-modal');
    modal.classList.toggle('hidden');
    updateWishlistDisplay();
}

// Toggle wishlist (add if not exists, remove if exists)
(function() {
    window.toggleWishlist = function(productId, productName, productPrice, productImage, productBrand = '', originalPrice = '', inStock = true) {
        // Use global wishlist
        window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
        var wishlist = window.wishlist;
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
    };
})();

function addToWishlist(productId, productName, productPrice, productImage) {
    window.toggleWishlist(productId, productName, productPrice, productImage);
}

function removeFromWishlist(productId) {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    var wishlist = window.wishlist;
    
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
    var wishlist = window.wishlist;
    
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
    var wishlist = window.wishlist;
    
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
            
            const imageSrc = item.image && item.image.trim() !== '' ? item.image : 'image/logo.webp';
            const imageHtml = `<img src="${imageSrc}" alt="${item.name}" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center\'><i class=\'fas fa-image text-gray-400\'></i></div>'">`;
            
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
            
            const dataImage = imageSrc.replace(/"/g, '&quot;');

            return `
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow gap-4" data-product-image="${dataImage}">
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
                        <button onclick="addToCart(this, '${item.id}', '${item.name.replace(/'/g, "\\'")}', '${item.price}', '${item.image || ''}')" class="px-4 py-2 text-green-600 rounded-lg border border-green-600 hover:text-green-700 hover:border-green-700 transition-colors text-sm flex items-center gap-2 whitespace-nowrap" data-product-image="${dataImage}">
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

function closeModalOnBackdrop(event, modalId) {
    if (event.target.id === modalId || event.target.classList.contains('bg-black')) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
    }
}

// Initialize cart count and wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateWishlistCount();
    
    // Initialize wishlist icons
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    window.wishlist.forEach(function(item) {
        var icon = document.querySelector('.wishlist-icon-' + item.id);
        if (icon) {
            icon.classList.remove('far', 'text-gray-600');
            icon.classList.add('fas', 'text-red-500');
        }
    });
    
    // Load saved language preference on page load
    const savedLanguage = localStorage.getItem('selectedLanguage');
    const flagImg = document.getElementById('language-flag');
    if (savedLanguage && flagImg) {
        flagImg.src = 'image/' + savedLanguage;
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

// Close language dropdown when clicking outside
document.addEventListener('click', function(event) {
    const languageButton = event.target.closest('#language-button');
    const dropdown = document.getElementById('language-dropdown');
    
    if (!languageButton && dropdown && dropdown.style.display === 'block') {
        hideLanguageDropdown();
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
</script>

<script>
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


function filterByVendor(vendorId, vendorName) {
    selectedVendorId = vendorId;
    selectedVendorName = vendorName;
    
    // Filter Best Sellers
    const bestSellersGrid = document.getElementById('bestsellers-grid');
    const bestSellersTitle = document.getElementById('bestsellers-title');
    const bestSellersReset = document.getElementById('bestsellers-reset');
    const bestSellersCards = bestSellersGrid.querySelectorAll('.product-card');
    
    bestSellersCards.forEach(card => {
        const cardVendorId = card.dataset.vendorId;
        if (cardVendorId && parseInt(cardVendorId) === vendorId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Filter New Arrivals
    const newArrivalsGrid = document.getElementById('newarrivals-grid');
    const newArrivalsTitle = document.getElementById('newarrivals-title');
    const newArrivalsReset = document.getElementById('newarrivals-reset');
    const newArrivalsCards = newArrivalsGrid.querySelectorAll('.product-card');
    
    newArrivalsCards.forEach(card => {
        const cardVendorId = card.dataset.vendorId;
        if (cardVendorId && parseInt(cardVendorId) === vendorId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update titles
    bestSellersTitle.textContent = `${vendorName}'s Best Sellers`;
    newArrivalsTitle.textContent = `${vendorName}'s New Arrivals`;
    
    // Show reset buttons
    bestSellersReset.classList.remove('hidden');
    newArrivalsReset.classList.remove('hidden');
    
    // Scroll to Best Sellers section
    bestSellersGrid.closest('section').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetVendorFilter() {
    selectedVendorId = null;
    selectedVendorName = null;
    selectedCategoryId = null;
    selectedCategoryName = null;
    
    // Reset Best Sellers
    const bestSellersGrid = document.getElementById('bestsellers-grid');
    const bestSellersTitle = document.getElementById('bestsellers-title');
    const bestSellersReset = document.getElementById('bestsellers-reset');
    const bestSellersCards = bestSellersGrid.querySelectorAll('.product-card');
    
    bestSellersCards.forEach(card => {
        card.style.display = 'block';
    });
    
    bestSellersTitle.textContent = 'Best Sellers';
    bestSellersReset.classList.add('hidden');
    
    // Reset New Arrivals
    const newArrivalsGrid = document.getElementById('newarrivals-grid');
    const newArrivalsTitle = document.getElementById('newarrivals-title');
    const newArrivalsReset = document.getElementById('newarrivals-reset');
    const newArrivalsCards = newArrivalsGrid.querySelectorAll('.product-card');
    
    newArrivalsCards.forEach(card => {
        card.style.display = 'block';
    });
    
    newArrivalsTitle.textContent = 'New Arrivals';
    newArrivalsReset.classList.add('hidden');
}

// Compare Modal Functions
function addToCompare(productId, productName, productPrice, productImage) {
    productImage = resolveProductImage(productImage);
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
        return;
    }

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

function toggleCompareModal() {
    const modal = document.getElementById('compare-modal');
    if (modal) {
        modal.classList.toggle('hidden');
        if (!modal.classList.contains('hidden')) {
            updateCompareDisplay();
        }
    }
}

// Initialize compare count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCompareCount();
    updateCompareIcons();
});

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
    if (typeof showToast === 'function') {
        showToast('Password changed successfully!', 'success');
    } else {
        alert('Password changed successfully!');
    }
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

// Load saved profile photo on page load (if not already loaded)
if (!window.profilePhotoLoaded) {
    document.addEventListener('DOMContentLoaded', function() {
        const userId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["admin"]) ? "admin_" . $_SESSION["admin"] : "guest"); ?>';
        const savedPhoto = localStorage.getItem('profilePhoto_' + userId);
        if (savedPhoto) {
            updateUserIcons(savedPhoto);
        }
    });
    window.profilePhotoLoaded = true;
}
</script>

