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

// Get filter parameters
$category = isset($_GET['category']) ? intval($_GET['category']) : 0; // Changed to intval for category ID

// Fixed price range for slider: $1 to $200
$sliderMinPrice = 1;
$sliderMaxPrice = 200;

// Use user-selected prices or defaults (set to full slider range to show all products by default)
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : $sliderMinPrice;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : $sliderMaxPrice;
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$promotion = isset($_GET['promotion']) ? $_GET['promotion'] : '';
$availability = isset($_GET['availability']) ? $_GET['availability'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
// Use 'p' parameter for pagination to avoid conflict with 'page' routing parameter
$pageNum = isset($_GET['p']) ? intval($_GET['p']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch products from database
$allProducts = [];
$productsQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id";
$whereConditions = [];

// Add category filter if specified
if ($category > 0) {
    $whereConditions[] = "p.category_id = $category";
}

// Add search filter if specified
if (!empty($search)) {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    $whereConditions[] = "(p.name LIKE '%$searchEscaped%' OR p.description LIKE '%$searchEscaped%')";
}

// Add price filter (only if explicitly set by user, not using defaults)
if (isset($_GET['min_price']) || isset($_GET['max_price'])) {
    if (!isset($_GET['min_price'])) $minPrice = 0;
    if (!isset($_GET['max_price'])) $maxPrice = 100000;
    $whereConditions[] = "p.price >= $minPrice AND p.price <= $maxPrice";
}
// If no price filter is set, don't filter by price (show all products)

// Add availability filter
if ($availability === 'in_stock') {
    $whereConditions[] = "p.quantity > 0";
} elseif ($availability === 'out_of_stock') {
    $whereConditions[] = "p.quantity = 0";
}

// Build WHERE clause
if (!empty($whereConditions)) {
    $productsQuery .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $productsQuery .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $productsQuery .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $productsQuery .= " ORDER BY p.name ASC";
        break;
    default:
        $productsQuery .= " ORDER BY p.created_at DESC";
        break;
}

$productsResult = mysqli_query($conn, $productsQuery);

// Convert database products to array format compatible with existing code
while ($product = mysqli_fetch_assoc($productsResult)) {
    $discountPercentage = isset($product['discount_percentage']) ? floatval($product['discount_percentage']) : 0;
    $discountPrice = isset($product['discount_price']) && $product['discount_price'] ? floatval($product['discount_price']) : null;
    $displayPrice = $discountPrice && $discountPrice < $product['price'] ? $discountPrice : floatval($product['price']);
    
    $allProducts[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'category' => $product['category_name'] ? $product['category_name'] : 'Uncategorized',
        'brand' => isset($product['vendor_name']) && $product['vendor_name'] ? $product['vendor_name'] : '', // Use vendor_name instead of brand
        'vendor_name' => isset($product['vendor_name']) && $product['vendor_name'] ? $product['vendor_name'] : '',
        'price' => $displayPrice,
        'original_price' => floatval($product['price']),
        'discount_price' => $discountPrice,
        'rating' => 4.0, // Default rating
        'discount' => $discountPercentage,
        'discount_percentage' => $discountPercentage,
        'in_stock' => $product['quantity'] > 0,
        'promotion' => '',
        'image_path' => $product['image_path'],
        'category_icon' => $product['category_icon'] ? $product['category_icon'] : 'fa-box',
        'icon' => 'fa-box',
        'icon_color' => 'green',
        'description' => $product['description'] ?? ''
    ];
}

// Products are already filtered and sorted by the SQL query, so just use them directly
$filteredProducts = $allProducts;

// Pagination
$productsPerPage = 12;
$totalProducts = count($filteredProducts);
$totalPages = ceil($totalProducts / $productsPerPage);
$offset = ($pageNum - 1) * $productsPerPage;
$paginatedProducts = array_slice($filteredProducts, $offset, $productsPerPage);

// Get active filters
$activeFilters = [];
if ($category > 0) {
    // Get category name from database
    $catQuery = "SELECT name FROM categories WHERE id = $category";
    $catResult = mysqli_query($conn, $catQuery);
    if ($catRow = mysqli_fetch_assoc($catResult)) {
        $activeFilters[] = ['type' => 'category', 'label' => $catRow['name'], 'value' => $category];
    }
}
// Only show price filter if it's been explicitly set and differs from defaults
if (isset($_GET['min_price']) || isset($_GET['max_price'])) {
    $activeFilters[] = ['type' => 'price', 'label' => 'Price: $' . number_format($minPrice, 2) . '-$' . number_format($maxPrice, 2), 'value' => 'price'];
}
if ($rating) $activeFilters[] = ['type' => 'rating', 'label' => $rating . ' Star', 'value' => $rating];
if ($promotion) {
    $promoLabels = ['new_arrival' => 'New Arrivals', 'best_seller' => 'Best Sellers', 'on_sale' => 'On Sale'];
    $activeFilters[] = ['type' => 'promotion', 'label' => $promoLabels[$promotion], 'value' => $promotion];
}
if ($availability) {
    $availLabels = ['in_stock' => 'In Stock', 'out_of_stock' => 'Out of Stock'];
    $activeFilters[] = ['type' => 'availability', 'label' => $availLabels[$availability], 'value' => $availability];
}
?>

<?php 
// Ensure user info variables are set before including home.php
// These will be refreshed in home.php from session, but we set them here for consistency
$headerOnly = true; 
?>
<!-- Include header from home.php -->
<?php include('home.php'); ?>

<!-- Filter Sidebar and Products Section -->
<div class="pt-32 pb-8 bg-gray-50 min-h-screen">
    <div class="max-w-[1920px] mx-auto px-6 py-6">
        <div class="flex gap-6">
            <!-- Filter Sidebar -->
            <aside class="w-72 flex-shrink-0 bg-white rounded-lg shadow-sm p-6 h-fit sticky top-28">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Filter Options</h2>
                
                <!-- By Categories -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3">By Categories</h3>
                    <div class="space-y-2">
                        <?php
                        // Fetch categories from database
                        $categoriesQuery = "SELECT * FROM categories ORDER BY name ASC";
                        $categoriesResult = mysqli_query($conn, $categoriesQuery);
                        
                        if (mysqli_num_rows($categoriesResult) > 0) {
                            while ($cat = mysqli_fetch_assoc($categoriesResult)) {
                                $isChecked = $category == $cat['id']; // Use numeric ID comparison
                                echo '<label class="flex items-center cursor-pointer hover:text-green-600 transition-colors">';
                                echo '<input type="radio" name="category" value="' . htmlspecialchars($cat['id']) . '" class="mr-2" onchange="applyFilter()" ' . ($isChecked ? 'checked' : '') . '>';
                                echo '<span class="text-sm text-gray-600">' . htmlspecialchars($cat['name']) . '</span>';
                                echo '</label>';
                            }
                        } else {
                            echo '<div class="text-sm text-gray-500">No categories available</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Price Range -->
<script>
// Define price slider functions FIRST - before HTML that uses them
(function() {
    'use strict';
    
    // Make sure updatePriceRange is globally accessible
    window.updatePriceRange = function(type, value) {
        try {
            const minSlider = document.getElementById('price-slider-min');
            const maxSlider = document.getElementById('price-slider-max');
            const progressBar = document.getElementById('price-range-progress');
            const minPriceDisplay = document.getElementById('min-price-display');
            const maxPriceDisplay = document.getElementById('max-price-display');
            
            if (!minSlider || !maxSlider || !progressBar || !minPriceDisplay || !maxPriceDisplay) {
                console.error('Price slider elements not found:', {
                    minSlider: !!minSlider,
                    maxSlider: !!maxSlider,
                    progressBar: !!progressBar,
                    minPriceDisplay: !!minPriceDisplay,
                    maxPriceDisplay: !!maxPriceDisplay
                });
                return;
            }
            
            const sliderMin = parseFloat(minSlider.getAttribute('min')) || 1;
            const sliderMax = parseFloat(minSlider.getAttribute('max')) || 200;
            const range = sliderMax - sliderMin;
            const newValue = parseFloat(value);
            
            // Update the slider value first
            if (type === 'min') {
                const currentMaxPrice = parseFloat(maxSlider.value);
                // Prevent min from exceeding max
                if (newValue > currentMaxPrice) {
                    minSlider.value = currentMaxPrice;
                } else {
                    minSlider.value = newValue;
                }
            } else if (type === 'max') {
                const currentMinPrice = parseFloat(minSlider.value);
                // Prevent max from going below min
                if (newValue < currentMinPrice) {
                    maxSlider.value = currentMinPrice;
                } else {
                    maxSlider.value = newValue;
                }
            }
            
            // Get final values after validation
            const finalMinPrice = parseFloat(minSlider.value);
            const finalMaxPrice = parseFloat(maxSlider.value);
            
            // Update display immediately with proper formatting
            if (minPriceDisplay) {
                minPriceDisplay.textContent = finalMinPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            if (maxPriceDisplay) {
                maxPriceDisplay.textContent = finalMaxPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            
            // Update progress bar in real-time
            if (range > 0 && progressBar) {
                const leftPercent = ((finalMinPrice - sliderMin) / range) * 100;
                const widthPercent = ((finalMaxPrice - finalMinPrice) / range) * 100;
                progressBar.style.left = Math.max(0, Math.min(100, leftPercent)) + '%';
                progressBar.style.width = Math.max(0, Math.min(100, widthPercent)) + '%';
            }
        } catch (error) {
            console.error('Error in updatePriceRange:', error);
        }
    };
    
    // Make sure debouncePriceFilter is globally accessible
    let priceFilterTimeout;
    window.debouncePriceFilter = function() {
        clearTimeout(priceFilterTimeout);
        priceFilterTimeout = setTimeout(function() {
            const params = new URLSearchParams(window.location.search);
            const minSlider = document.getElementById('price-slider-min');
            const maxSlider = document.getElementById('price-slider-max');
            if (!minSlider || !maxSlider) {
                console.error('Price sliders not found in debouncePriceFilter');
                return;
            }
            const minPrice = parseFloat(minSlider.value);
            const maxPrice = parseFloat(maxSlider.value);
            const defaultMin = parseFloat(minSlider.getAttribute('min'));
            const defaultMax = parseFloat(maxSlider.getAttribute('max'));
            
            // Only set price parameters if they differ from defaults
            if (minPrice !== defaultMin || maxPrice !== defaultMax) {
                params.set('min_price', minPrice);
                params.set('max_price', maxPrice);
            } else {
                params.delete('min_price');
                params.delete('max_price');
            }
            
            // Preserve 'page=all-products' for routing and reset pagination to page 1
            if (params.get('page') !== 'all-products') {
                params.set('page', 'all-products');
            }
            // Reset pagination to page 1 when filters change
            params.delete('p');
            
            window.location.search = params.toString();
        }, 500);
    };
})();
</script>

                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3">Price</h3>
                    <div class="mb-4">
                        <div class="relative">
                            <div class="relative h-2 bg-gray-200 rounded-lg">
                                <?php 
                                $sliderMin = $sliderMinPrice;
                                $sliderMax = $sliderMaxPrice;
                                $range = $sliderMax - $sliderMin;
                                $leftPercent = $range > 0 ? (($minPrice - $sliderMin) / $range) * 100 : 0;
                                $widthPercent = $range > 0 ? (($maxPrice - $minPrice) / $range) * 100 : 100;
                                ?>
                                <div class="absolute h-2 bg-green-600 rounded-lg" id="price-range-progress" style="left: <?php echo max(0, min(100, $leftPercent)); ?>%; width: <?php echo max(0, min(100, $widthPercent)); ?>%;"></div>
                            </div>
                            <input type="range" id="price-slider-min" min="<?php echo $sliderMin; ?>" max="<?php echo $sliderMax; ?>" step="1" value="<?php echo $minPrice; ?>" class="absolute top-0 w-full h-2 bg-transparent appearance-none cursor-pointer" oninput="window.updatePriceRange('min', this.value)" onchange="window.debouncePriceFilter()" onmouseup="window.debouncePriceFilter()" ontouchend="window.debouncePriceFilter()">
                            <input type="range" id="price-slider-max" min="<?php echo $sliderMin; ?>" max="<?php echo $sliderMax; ?>" step="1" value="<?php echo $maxPrice; ?>" class="absolute top-0 w-full h-2 bg-transparent appearance-none cursor-pointer" oninput="window.updatePriceRange('max', this.value)" onchange="window.debouncePriceFilter()" onmouseup="window.debouncePriceFilter()" ontouchend="window.debouncePriceFilter()">
                        </div>
                        <div class="flex justify-between mt-3">
                            <span class="text-sm text-gray-600">$<span id="min-price-display"><?php echo number_format($minPrice, 2); ?></span></span>
                            <span class="text-sm text-gray-600">$<span id="max-price-display"><?php echo number_format($maxPrice, 2); ?></span></span>
                        </div>
                    </div>
                </div>

                <!-- Review -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3">Review</h3>
                    <div class="space-y-2">
                        <?php
                        for ($i = 5; $i >= 1; $i--) {
                            $isChecked = $rating == $i;
                            echo '<label class="flex items-center cursor-pointer hover:text-green-600 transition-colors">';
                            echo '<input type="radio" name="rating" value="' . $i . '" class="mr-2" onchange="applyFilter()" ' . ($isChecked ? 'checked' : '') . '>';
                            echo '<span class="flex items-center text-yellow-400">';
                            for ($j = 1; $j <= 5; $j++) {
                                if ($j <= $i) {
                                    echo '<i class="fas fa-star text-xs"></i>';
                                } else {
                                    echo '<i class="far fa-star text-xs"></i>';
                                }
                            }
                            echo '</span>';
                            echo '<span class="ml-2 text-sm text-gray-600">' . $i . ' Star</span>';
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>

                <!-- By Promotions -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3">By Promotions</h3>
                    <div class="space-y-2">
                        <?php
                        $promotions = [
                            'new_arrival' => 'New Arrivals',
                            'best_seller' => 'Best Sellers',
                            'on_sale' => 'On Sale'
                        ];
                        foreach ($promotions as $key => $label) {
                            $isChecked = $promotion === $key;
                            echo '<label class="flex items-center cursor-pointer hover:text-green-600 transition-colors">';
                            echo '<input type="checkbox" name="promotion" value="' . $key . '" class="mr-2" onchange="applyFilter()" ' . ($isChecked ? 'checked' : '') . '>';
                            echo '<span class="text-sm text-gray-600">' . htmlspecialchars($label) . '</span>';
                            if ($isChecked) {
                                echo '<i class="fas fa-check text-green-600 ml-auto"></i>';
                            }
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Availability -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3">Availability</h3>
                    <div class="space-y-2">
                        <?php
                        $availabilities = [
                            'in_stock' => 'In Stock',
                            'out_of_stock' => 'Out of Stocks'
                        ];
                        foreach ($availabilities as $key => $label) {
                            $isChecked = $availability === $key;
                            echo '<label class="flex items-center cursor-pointer hover:text-green-600 transition-colors">';
                            echo '<input type="checkbox" name="availability" value="' . $key . '" class="mr-2" onchange="applyFilter()" ' . ($isChecked ? 'checked' : '') . '>';
                            echo '<span class="text-sm text-gray-600">' . htmlspecialchars($label) . '</span>';
                            if ($isChecked) {
                                echo '<i class="fas fa-check text-green-600 ml-auto"></i>';
                            }
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>

                <button onclick="clearAllFilters()" class="w-full mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Clear All Filters
                </button>
            </aside>

            <!-- Products Section -->
            <div class="flex-1">
                <!-- Header -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-gray-600">Showing <?php echo $totalProducts > 0 ? ($offset + 1) : 0; ?>-<?php echo min($offset + $productsPerPage, $totalProducts); ?> of <?php echo number_format($totalProducts); ?> results</p>
                        <div class="flex items-center gap-3">
                            <label class="text-sm text-gray-600">Sort by:</label>
                            <select id="sort-select" onchange="applySort()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
                                <option value="default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>Default Sorting</option>
                                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    <?php if (!empty($activeFilters)): ?>
                    <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200">
                        <span class="text-sm font-semibold text-gray-700">Active Filter:</span>
                        <?php foreach ($activeFilters as $filter): ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                <?php echo htmlspecialchars($filter['label']); ?>
                                <button onclick="removeFilterTag('<?php echo $filter['type']; ?>')" class="text-green-700 hover:text-green-900">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        <?php endforeach; ?>
                        <button onclick="clearAllFilters()" class="text-sm text-red-600 hover:text-red-700 underline">Clear All</button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
                    <?php foreach ($paginatedProducts as $product): ?>
                        <?php 
                        // Calculate discounted price once for this product
                        // If product has "New" tag, don't show discount price - use regular price
                        $isNewProduct = isset($product['promotion']) && $product['promotion'] === 'new_arrival';
                        $displayPrice = $product['price'];
                        if (!$isNewProduct && isset($product['discount']) && $product['discount'] > 0 && isset($product['original_price'])) {
                            $displayPrice = $product['original_price'] * (1 - $product['discount'] / 100);
                        }

                        $productImageRaw = !empty($product['image_path']) ? $product['image_path'] : '';
                        $productImageAttr = htmlspecialchars($productImageRaw, ENT_QUOTES, 'UTF-8');
                        $productImageJs = js_str($productImageRaw);
                        $categoryIcon = !empty($product['category_icon']) ? $product['category_icon'] : 'fa-box';

                        $productName = $product['name'] ?? '';
                        $productNameJs = js_str($productName);

                        $productBrand = $product['brand'] ?? '';
                        $productBrandJs = js_str($productBrand);

                        $productIdJs = js_str($product['id']);

                        $displayPriceFormatted = '$' . number_format($displayPrice, 2);
                        $displayPriceJs = js_str($displayPriceFormatted);

                        $originalPriceValue = $product['original_price'] ?? $displayPrice;
                        $originalPriceFormatted = '$' . number_format($originalPriceValue, 2);
                        $originalPriceJs = js_str($originalPriceFormatted);
                        ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow product-card cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $product['id']; ?>'" data-category="<?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>" data-price="<?php echo $displayPrice; ?>" data-rating="<?php echo $product['rating']; ?>" data-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="relative">
                                <!-- Badges -->
                                <?php if (!$product['in_stock']): ?>
                                    <div class="absolute top-2 left-2 bg-red-600 text-white px-2 py-1 rounded text-xs font-semibold z-10">Out of Stock</div>
                                <?php elseif (isset($product['promotion']) && $product['promotion'] === 'new_arrival'): ?>
                                    <div class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold z-10">New</div>
                                <?php elseif (isset($product['discount_percentage']) && $product['discount_percentage'] > 0): ?>
                                    <?php 
                                    // Determine badge color based on discount percentage
                                    $discountPercent = $product['discount_percentage'];
                                    if ($discountPercent >= 50) {
                                        $badgeColor = 'bg-red-600'; // High discount - red
                                    } elseif ($discountPercent >= 30) {
                                        $badgeColor = 'bg-orange-500'; // Medium-high discount - orange
                                    } elseif ($discountPercent >= 15) {
                                        $badgeColor = 'bg-amber-500'; // Medium discount - amber
                                    } else {
                                        $badgeColor = 'bg-green-600'; // Low discount - green
                                    }
                                    ?>
                                    <div class="absolute top-2 left-2 <?php echo $badgeColor; ?> text-white px-3 py-1.5 rounded-full text-xs font-bold z-10 flex items-center gap-1.5 shadow-lg">
                                        <i class="fas fa-tag text-white"></i>
                                        <span><?php echo number_format($discountPercent, 0); ?>% OFF</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Wishlist and Compare Buttons -->
                                <div class="absolute top-2 right-2 flex gap-2 z-10">
                                    <button onclick="event.stopPropagation(); toggleWishlist(<?php echo $productIdJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>, <?php echo $productBrandJs; ?>, <?php echo $originalPriceJs; ?>, <?php echo $product['in_stock'] ? 'true' : 'false'; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-green-50 transition-colors wishlist-btn-<?php echo $product['id']; ?>">
                                        <i class="far fa-heart text-gray-600 wishlist-icon-<?php echo $product['id']; ?>"></i>
                                    </button>
                                    <button onclick="event.stopPropagation(); addToCompare(<?php echo $productIdJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-50 transition-colors" title="Add to Compare">
                                        <i class="fas fa-balance-scale text-gray-600 text-sm compare-icon-<?php echo $product['id']; ?>"></i>
                                    </button>
                                </div>
                                
                                <!-- Product Image -->
                                <?php if ($productImageRaw): ?>
                                    <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        <img src="<?php echo $productImageAttr; ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-full object-cover">
                                </div>
                                <?php else: ?>
                                    <div class="h-48 bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center">
                                        <i class="fas <?php echo htmlspecialchars($categoryIcon); ?> text-6xl text-green-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="p-4">
                                    <p class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($product['category']); ?></p>
                                    <h3 class="font-semibold text-gray-800 mb-2 text-sm"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    
                                    <!-- Rating -->
                                    <div class="flex items-center gap-1 mb-2">
                                        <?php
                                        $fullStars = floor($product['rating']);
                                        $hasHalfStar = ($product['rating'] - $fullStars) >= 0.5;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $fullStars) {
                                                echo '<i class="fas fa-star text-yellow-400 text-xs"></i>';
                                            } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                                                echo '<i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-yellow-400 text-xs"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="text-xs text-gray-500 ml-1">(<?php echo $product['rating']; ?>)</span>
                                    </div>
                                    
                                    <!-- Vendor Name -->
                                    <?php if (isset($product['vendor_name']) && !empty($product['vendor_name'])): ?>
                                        <p class="text-xs mb-2">
                                            <span class="text-gray-500">By </span>
                                            <span class="text-green-600 font-semibold"><?php echo htmlspecialchars($product['vendor_name']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Price -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <?php if (isset($product['discount_price']) && $product['discount_price'] && $product['discount_price'] < $product['original_price']): ?>
                                            <span class="text-lg font-bold text-green-600">$<?php echo number_format($product['discount_price'], 2); ?></span>
                                            <span class="text-sm text-gray-400 line-through">$<?php echo number_format($product['original_price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="text-lg font-bold text-green-600">$<?php echo number_format($displayPrice, 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Add to Cart Button -->
                                    <?php if ($product['in_stock']): ?>
                                        <button onclick="event.stopPropagation(); addToCart(<?php echo $productIdJs; ?>, <?php echo $productNameJs; ?>, <?php echo $displayPriceJs; ?>, <?php echo $productImageJs; ?>)" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold text-sm text-center flex items-center justify-center gap-2">
                                            <i class="fas fa-shopping-cart"></i>
                                            <span>Add</span>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="event.stopPropagation();" class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed font-semibold text-sm" disabled>
                                            Add
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-center gap-2 mt-8">
                    <?php if ($pageNum > 1): ?>
                        <a href="?<?php 
                            $paginationParams = array_merge($_GET, ['p' => $pageNum - 1]);
                            // Ensure 'page=all-products' is preserved
                            if (!isset($paginationParams['page']) || $paginationParams['page'] !== 'all-products') {
                                $paginationParams['page'] = 'all-products';
                            }
                            echo http_build_query($paginationParams);
                        ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $pageNum - 2);
                    $endPage = min($totalPages, $pageNum + 2);
                    
                    if ($startPage > 1) {
                        $pageParams = array_merge($_GET, ['p' => 1]);
                        if (!isset($pageParams['page']) || $pageParams['page'] !== 'all-products') {
                            $pageParams['page'] = 'all-products';
                        }
                        echo '<a href="?' . http_build_query($pageParams) . '" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="px-3 py-2 text-gray-500">...</span>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $isActive = $i === $pageNum;
                        $pageParams = array_merge($_GET, ['p' => $i]);
                        if (!isset($pageParams['page']) || $pageParams['page'] !== 'all-products') {
                            $pageParams['page'] = 'all-products';
                        }
                        echo '<a href="?' . http_build_query($pageParams) . '" class="px-3 py-2 border rounded-lg transition-colors ' . ($isActive ? 'bg-green-600 text-white border-green-600' : 'border-gray-300 hover:bg-gray-50') . '">' . $i . '</a>';
                    }
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="px-3 py-2 text-gray-500">...</span>';
                        }
                        $pageParams = array_merge($_GET, ['p' => $totalPages]);
                        if (!isset($pageParams['page']) || $pageParams['page'] !== 'all-products') {
                            $pageParams['page'] = 'all-products';
                        }
                        echo '<a href="?' . http_build_query($pageParams) . '" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">' . $totalPages . '</a>';
                    }
                    ?>
                    
                    <?php if ($pageNum < $totalPages): ?>
                        <a href="?<?php 
                            $paginationParams = array_merge($_GET, ['p' => $pageNum + 1]);
                            // Ensure 'page=all-products' is preserved
                            if (!isset($paginationParams['page']) || $paginationParams['page'] !== 'all-products') {
                                $paginationParams['page'] = 'all-products';
                            }
                            echo http_build_query($paginationParams);
                        ?>" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Compare Modal -->
<div id="compare-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" onclick="if(event.target === this) toggleCompareModal()">
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
            <button onclick="clearCompare()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Clear All</button>
            <button onclick="toggleCompareModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Close</button>
        </div>
    </div>
</div>

<script>
// Define toggleWishlist to ensure it's available immediately
(function() {
    'use strict';
    
    // Initialize wishlist globally
    if (typeof window.wishlist === 'undefined') {
        window.wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    }
    
    // Initialize compareList globally
    if (typeof window.compareList === 'undefined') {
        window.compareList = JSON.parse(localStorage.getItem('compareList')) || [];
    }
    
    // Define compare functions
    window.addToCompare = function(productId, productName, productPrice, productImage) {
        try {
            var compareList = window.compareList || JSON.parse(localStorage.getItem('compareList')) || [];
            productImage = resolveProductImage(productImage);
            if (!productImage || typeof productImage !== 'string' || productImage.trim() === '') {
                productImage = 'image/logo.webp';
            }
            if (compareList.length >= 4) {
                if (typeof window.showToast === 'function') {
                    window.showToast('You can only compare up to 4 products at once.', 'error');
                } else {
                    alert('You can only compare up to 4 products at once.');
                }
                return;
            }
            
            if (!compareList.find(function(item) { return item.id === productId; })) {
                compareList.push({ id: productId, name: productName, price: productPrice, image: productImage });
                window.compareList = compareList;
                localStorage.setItem('compareList', JSON.stringify(compareList));
                window.updateCompareCount();
                
                // Update icon color to green
                var icon = document.querySelector('.compare-icon-' + productId);
                if (icon) {
                    icon.classList.remove('text-gray-600');
                    icon.classList.add('text-green-600');
                }
                
                if (typeof window.showToast === 'function') {
                    window.showToast('Product added to compare!', 'success');
                } else {
                    alert('Product added to compare!');
                }
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast('Product already in compare list!', 'error');
                } else {
                    alert('Product already in compare list!');
                }
            }
        } catch (e) {
            console.error('Error in addToCompare:', e);
        }
    };
    
    window.removeFromCompare = function(productId) {
        try {
            var compareList = window.compareList || JSON.parse(localStorage.getItem('compareList')) || [];
            compareList = compareList.filter(function(item) { return item.id !== productId; });
            window.compareList = compareList;
            localStorage.setItem('compareList', JSON.stringify(compareList));
            window.updateCompareCount();
            window.updateCompareDisplay();
            
            // Reset icon color to gray
            var icon = document.querySelector('.compare-icon-' + productId);
            if (icon) {
                icon.classList.remove('text-green-600');
                icon.classList.add('text-gray-600');
            }
        } catch (e) {
            console.error('Error in removeFromCompare:', e);
        }
    };
    
    window.clearCompare = function() {
        try {
            // Reset all compare icons to gray before clearing
            var compareList = window.compareList || JSON.parse(localStorage.getItem('compareList')) || [];
            compareList.forEach(function(item) {
                var icon = document.querySelector('.compare-icon-' + item.id);
                if (icon) {
                    icon.classList.remove('text-green-600');
                    icon.classList.add('text-gray-600');
                }
            });
            
            window.compareList = [];
            localStorage.setItem('compareList', JSON.stringify(window.compareList));
            window.updateCompareCount();
            window.updateCompareDisplay();
        } catch (e) {
            console.error('Error in clearCompare:', e);
        }
    };
    
    window.updateCompareIcons = function() {
        try {
            // Initialize compare icon states on page load
            var compareList = window.compareList || JSON.parse(localStorage.getItem('compareList')) || [];
            compareList.forEach(function(item) {
                var icon = document.querySelector('.compare-icon-' + item.id);
                if (icon) {
                    icon.classList.remove('text-gray-600');
                    icon.classList.add('text-green-600');
                }
            });
        } catch (e) {
            console.error('Error in updateCompareIcons:', e);
        }
    };
    
    window.updateCompareCount = function() {
        try {
            var compareList = window.compareList || JSON.parse(localStorage.getItem('compareList')) || [];
            var count = compareList.length;
            var badge = document.getElementById('compare-count');
            if (badge) {
                badge.textContent = count;
                badge.classList.toggle('hidden', count === 0);
            }
        } catch (e) {
            console.error('Error in updateCompareCount:', e);
        }
    };
    
    window.updateCompareDisplay = function() {
        try {
            var compareList = window.compareList || JSON.parse(localStorage.getItem('compareList')) || [];
            var container = document.getElementById('compare-items');
            if (container) {
                if (compareList.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-8">No products to compare yet.</p>';
                } else {
                    container.innerHTML = compareList.map(function(item) {
                        var resolvedImage = item.image && item.image.trim() !== '' ? item.image : 'image/logo.webp';
                        var imageHtml = '<img src="' + resolvedImage + '" alt="' + item.name + '" class="w-full h-full object-cover rounded" onerror="this.onerror=null; this.parentElement.innerHTML=\'<div class=\\'w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center\\'><i class=\\'fas fa-image text-gray-400\\'></i></div>\'">';
                        return '<div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">' +
                            '<div class="flex items-center gap-4">' +
                            '<div class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex-shrink-0">' +
                            imageHtml +
                            '</div>' +
                            '<div>' +
                            '<h3 class="font-semibold text-gray-800">' + item.name + '</h3>' +
                            '<p class="text-green-600 font-bold">' + item.price + '</p>' +
                            '</div>' +
                            '</div>' +
                            '<button onclick="removeFromCompare(\'' + item.id + '\')" class="text-red-500 hover:text-red-700 transition-colors">' +
                            '<i class="fas fa-trash"></i>' +
                            '</button>' +
                            '</div>';
                    }).join('');
                }
            }
        } catch (e) {
            console.error('Error in updateCompareDisplay:', e);
        }
    };
    
    window.toggleCompareModal = function() {
        try {
            var modal = document.getElementById('compare-modal');
            if (modal) {
                modal.classList.toggle('hidden');
                if (!modal.classList.contains('hidden')) {
                    window.updateCompareDisplay();
                }
            }
        } catch (e) {
            console.error('Error in toggleCompareModal:', e);
        }
    };
    
    // Define toggleWishlist function immediately
    window.toggleWishlist = function(productId, productName, productPrice, productImage) {
        try {
            var wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
            var existingIndex = wishlist.findIndex(function(item) { return item.id === productId; });
            
            if (existingIndex === -1) {
                // Add to wishlist
                wishlist.push({ id: productId, name: productName, price: productPrice, image: productImage });
                window.wishlist = wishlist;
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
                
                // Update icon
                var icon = document.querySelector('.wishlist-icon-' + productId);
                if (icon) {
                    icon.classList.remove('far', 'text-gray-600');
                    icon.classList.add('fas', 'text-red-500');
                }
                
                // Update count
                var badge = document.getElementById('wishlist-count');
                if (badge) {
                    badge.textContent = wishlist.length;
                    badge.classList.remove('hidden');
                }
                
                alert('Added to wishlist!');
            } else {
                // Remove from wishlist
                wishlist.splice(existingIndex, 1);
                window.wishlist = wishlist;
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
                
                // Update icon
                var icon = document.querySelector('.wishlist-icon-' + productId);
                if (icon) {
                    icon.classList.remove('fas', 'text-red-500');
                    icon.classList.add('far', 'text-gray-600');
                }
                
                // Update count
                var badge = document.getElementById('wishlist-count');
                if (badge) {
                    badge.textContent = wishlist.length;
                    if (wishlist.length === 0) {
                        badge.classList.add('hidden');
                    }
                }
                
                alert('Removed from wishlist!');
            }
            
            // Update display if modal exists (from home.php)
            if (typeof updateWishlistDisplay === 'function') {
                updateWishlistDisplay();
            } else if (typeof window.updateWishlistDisplay === 'function') {
                window.updateWishlistDisplay();
            }
        } catch (e) {
            console.error('Error in toggleWishlist:', e);
            alert('Error: ' + e.message);
        }
    };
})();

// Cart functions
var cart = JSON.parse(localStorage.getItem('cart')) || [];
var wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
window.wishlist = wishlist;

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

function addToCart(productId, productName, productPrice, productImage) {
    productImage = resolveProductImage(productImage);
    if (!productImage || typeof productImage !== 'string' || productImage.trim() === '') {
        productImage = 'image/logo.webp';
    }
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ id: productId, name: productName, price: productPrice, image: productImage, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    if (typeof showToast === 'function') {
        showToast(productName + ' added to cart!', 'success');
    } else {
        alert(productName + ' added to cart!');
    }
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
    // Cart count is stored but not displayed in this standalone section
}

// Wishlist functions - override home.php functions if they exist
// Make sure wishlist is global
window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
wishlist = window.wishlist;

window.toggleWishlist = function(productId, productName, productPrice, productImage) {
    // Use global wishlist
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    productImage = resolveProductImage(productImage);
    
    const existingIndex = wishlist.findIndex(item => item.id === productId);
    if (existingIndex === -1) {
        // Add to wishlist
        wishlist.push({ id: productId, name: productName, price: productPrice, image: productImage });
        window.wishlist = wishlist; // Sync to global
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        if (typeof updateWishlistIcon === 'function') {
            updateWishlistIcon(productId, true);
        }
        if (typeof updateWishlistCount === 'function') {
            updateWishlistCount();
        }
        if (typeof showToast === 'function') {
            showToast(`${productName} added to wishlist!`, 'success');
        } else {
            alert('Added to wishlist!');
        }
    } else {
        // Remove from wishlist
        wishlist.splice(existingIndex, 1);
        window.wishlist = wishlist; // Sync to global
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        if (typeof updateWishlistIcon === 'function') {
            updateWishlistIcon(productId, false);
        }
        if (typeof updateWishlistCount === 'function') {
            updateWishlistCount();
        }
        if (typeof showToast === 'function') {
            showToast(`${productName} removed from wishlist`, 'info');
        } else {
            alert('Removed from wishlist!');
        }
    }
    // Update wishlist modal if it exists and is open
    if (typeof updateWishlistDisplay === 'function') {
        updateWishlistDisplay();
    }
};

// Make toggleWishlist available as addToWishlist for compatibility
// If home.php already defined toggleWishlist, use it; otherwise use our version
if (typeof toggleWishlist === 'function' && typeof window.toggleWishlist === 'undefined') {
    window.toggleWishlist = toggleWishlist;
}

// Override addToWishlist to use toggle functionality
window.addToWishlist = function(productId, productName, productPrice, productImage) {
    if (typeof toggleWishlist === 'function') {
        toggleWishlist(productId, productName, productPrice, productImage);
    } else if (typeof window.toggleWishlist === 'function') {
        window.toggleWishlist(productId, productName, productPrice, productImage);
    }
};

window.removeFromWishlist = function(productId) {
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    wishlist = wishlist.filter(item => item.id !== productId);
    window.wishlist = wishlist; // Sync to global
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    if (typeof updateWishlistIcon === 'function') {
        updateWishlistIcon(productId, false);
    }
    if (typeof updateWishlistCount === 'function') {
        updateWishlistCount();
    }
    if (typeof updateWishlistDisplay === 'function') {
        updateWishlistDisplay();
    }
};

window.updateWishlistIcon = function(productId, isInWishlist) {
    const icon = document.querySelector(`.wishlist-icon-${productId}`);
    if (icon) {
        if (isInWishlist) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.classList.remove('text-gray-600');
            icon.classList.add('text-red-500');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.classList.remove('text-red-500');
            icon.classList.add('text-gray-600');
        }
    }
};

window.updateWishlistCount = function() {
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    const count = wishlist.length;
    const badge = document.getElementById('wishlist-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
};

window.updateWishlistDisplay = function() {
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
};

// Initialize wishlist icons on page load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure wishlist is initialized globally
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = window.wishlist;
    
    wishlist.forEach(item => {
        if (typeof updateWishlistIcon === 'function') {
            updateWishlistIcon(item.id, true);
        }
    });
    if (typeof updateWishlistCount === 'function') {
        updateWishlistCount();
    }
});

// Filter functions
function applyFilter() {
    const params = new URLSearchParams(window.location.search);
    
    // Add category filter
    const categoryRadio = document.querySelector('input[name="category"]:checked');
    if (categoryRadio && categoryRadio.value) {
        params.set('category', categoryRadio.value);
    } else {
        params.delete('category');
    }
    
    // Add price filters only if user changed them from defaults
    const minSlider = document.getElementById('price-slider-min');
    const maxSlider = document.getElementById('price-slider-max');
    const minPrice = parseFloat(minSlider.value);
    const maxPrice = parseFloat(maxSlider.value);
    const defaultMin = parseFloat(minSlider.getAttribute('min'));
    const defaultMax = parseFloat(maxSlider.getAttribute('max'));
    
    // Only set price parameters if they differ from defaults
    if (minPrice !== defaultMin || maxPrice !== defaultMax) {
    params.set('min_price', minPrice);
    params.set('max_price', maxPrice);
    } else {
        params.delete('min_price');
        params.delete('max_price');
    }
    
    // Add rating filter
    const ratingRadio = document.querySelector('input[name="rating"]:checked');
    if (ratingRadio && ratingRadio.value) {
        params.set('rating', ratingRadio.value);
    } else {
        params.delete('rating');
    }
    
    // Add promotion filter
    const promotionCheckboxes = document.querySelectorAll('input[name="promotion"]:checked');
    if (promotionCheckboxes.length > 0) {
        params.set('promotion', promotionCheckboxes[0].value);
    } else {
        params.delete('promotion');
    }
    
    // Add availability filter
    const availabilityCheckboxes = document.querySelectorAll('input[name="availability"]:checked');
    if (availabilityCheckboxes.length > 0) {
        params.set('availability', availabilityCheckboxes[0].value);
    } else {
        params.delete('availability');
    }
    
    // Preserve 'page=all-products' for routing and reset pagination to page 1
    if (params.get('page') !== 'all-products') {
        params.set('page', 'all-products');
    }
    params.delete('p'); // Reset pagination when filters change
    window.location.search = params.toString();
}

function applySort() {
    const sortValue = document.getElementById('sort-select').value;
    const params = new URLSearchParams(window.location.search);
    params.set('sort', sortValue);
    // Preserve 'page=all-products' for routing and reset pagination to page 1
    if (params.get('page') !== 'all-products') {
        params.set('page', 'all-products');
    }
    params.delete('p'); // Reset pagination when sort changes
    window.location.search = params.toString();
}

function removeFilterTag(type) {
    const params = new URLSearchParams(window.location.search);
    if (type === 'price') {
        params.delete('min_price');
        params.delete('max_price');
    } else {
        params.delete(type);
    }
    // Preserve 'page=all-products' for routing and reset pagination to page 1
    if (params.get('page') !== 'all-products') {
        params.set('page', 'all-products');
    }
    params.delete('p'); // Reset pagination when filters change
    window.location.search = params.toString();
}

function clearAllFilters() {
    const params = new URLSearchParams();
    params.set('page', 'all-products');
        window.location.search = params.toString();
}


// Initialize cart count and compare count on page load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure price slider function is available
    if (typeof window.updatePriceRange !== 'function') {
        console.error('updatePriceRange function not found!');
    }
    
    // Test if price slider elements exist
    const testMinSlider = document.getElementById('price-slider-min');
    const testMaxSlider = document.getElementById('price-slider-max');
    if (!testMinSlider || !testMaxSlider) {
        console.error('Price slider elements not found on page load');
    } else {
        console.log('Price slider elements found:', {
            minSlider: !!testMinSlider,
            maxSlider: !!testMaxSlider
        });
    }
    
    updateCartCount();
    if (typeof window.updateCompareCount === 'function') {
        window.updateCompareCount();
    }
    if (typeof window.updateCompareIcons === 'function') {
        window.updateCompareIcons();
    }
    
    const rangeInputs = document.querySelectorAll('input[type="range"]');
    rangeInputs.forEach(input => {
        input.style.webkitAppearance = 'none';
        input.style.appearance = 'none';
        input.style.height = '8px';
        input.style.background = 'transparent';
    });
});
</script> 