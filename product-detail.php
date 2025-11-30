<?php
session_start();
include('config.php');
$isAdmin = isset($_SESSION['admin']);
$isUser = isset($_SESSION['user_id']);
$currentUser = $isAdmin ? $_SESSION['admin'] : ($isUser ? $_SESSION['username'] : 'Guest User');
$currentEmail = $isAdmin ? ($_SESSION['admin'] . '@admin.com') : ($isUser ? $_SESSION['user_email'] : 'guest@example.com');
$currentFirstName = $isUser ? ($_SESSION['user_first_name'] ?? 'Guest') : ($isAdmin ? $_SESSION['admin'] : 'Guest');
$currentLastName = $isUser ? ($_SESSION['user_last_name'] ?? 'User') : ($isAdmin ? 'Admin' : 'User');

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product from database
$product = null;
if ($productId > 0) {
    $productQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.id = $productId";
    $productResult = mysqli_query($conn, $productQuery);
    
    if ($productResult && mysqli_num_rows($productResult) > 0) {
        $productData = mysqli_fetch_assoc($productResult);
        
        // Calculate display price
        $discountPercent = isset($productData['discount_percentage']) ? floatval($productData['discount_percentage']) : 0;
        $discountPrice = isset($productData['discount_price']) && $productData['discount_price'] < $productData['price'] ? floatval($productData['discount_price']) : null;
        $displayPrice = $discountPrice ? $discountPrice : floatval($productData['price']);
        $originalPrice = floatval($productData['price']);
        
        // Get rating (default to 4.0 if not set)
        $rating = isset($productData['rating']) && $productData['rating'] > 0 ? floatval($productData['rating']) : 4.0;
        
        // Get vendor name
        $vendorName = isset($productData['vendor_name']) && $productData['vendor_name'] ? $productData['vendor_name'] : '';
        
        // Get category info
        $categoryName = $productData['category_name'] ? $productData['category_name'] : 'Uncategorized';
        $categoryIcon = $productData['category_icon'] ? $productData['category_icon'] : 'fa-box';
        
        // Get product image
        $productImage = !empty($productData['image_path']) ? $productData['image_path'] : '';
        
        // Get stock status
        $inStock = $productData['quantity'] > 0;
        $availability = intval($productData['quantity']);
        
        // Get description
        $description = isset($productData['description']) && $productData['description'] ? $productData['description'] : 'Product description not available.';
        
        // Build product array
    $product = [
            'id' => $productData['id'],
            'name' => $productData['name'],
            'category' => $categoryName,
            'category_id' => $productData['category_id'],
            'brand' => $vendorName,
            'vendor_name' => $vendorName,
            'price' => $displayPrice,
        'original_price' => $originalPrice,
            'discount_price' => $discountPrice,
            'discount_percentage' => $discountPercent,
        'rating' => $rating,
            'discount' => $discountPercent,
        'in_stock' => $inStock,
        'availability' => $availability,
            'quantity' => $availability,
            'image_path' => $productImage,
            'image' => $productImage,
            'icon' => $categoryIcon,
            'icon_color' => 'green',
            'description' => $description,
            'sku' => strtolower(str_replace(' ', '-', $productData['name'])) . '-' . $productData['id']
        ];
    }
}

// If product not found, redirect to home
if (!$product) {
    header("Location: index.php?page=home");
    exit;
}

// Fetch related products from the same category
$relatedProducts = [];
if (isset($product['category_id'])) {
    $relatedQuery = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.category_id = {$product['category_id']} 
                     AND p.id != {$product['id']} 
                     ORDER BY p.created_at DESC 
                     LIMIT 4";
    $relatedResult = mysqli_query($conn, $relatedQuery);
    
    if ($relatedResult && mysqli_num_rows($relatedResult) > 0) {
        while ($relatedData = mysqli_fetch_assoc($relatedResult)) {
            $relDiscountPrice = isset($relatedData['discount_price']) && $relatedData['discount_price'] < $relatedData['price'] ? floatval($relatedData['discount_price']) : null;
            $relDisplayPrice = $relDiscountPrice ? $relDiscountPrice : floatval($relatedData['price']);
            $relRating = isset($relatedData['rating']) && $relatedData['rating'] > 0 ? floatval($relatedData['rating']) : 4.0;
            
            $relatedProducts[] = [
                'id' => $relatedData['id'],
                'name' => $relatedData['name'],
                'category' => $relatedData['category_name'] ? $relatedData['category_name'] : 'Uncategorized',
                'price' => $relDisplayPrice,
                'rating' => $relRating,
                'image_path' => !empty($relatedData['image_path']) ? $relatedData['image_path'] : '',
                'icon' => $relatedData['category_icon'] ? $relatedData['category_icon'] : 'fa-box',
                'icon_color' => 'green'
            ];
        }
    }
}

// Set default values if not present
if (!isset($product['availability'])) $product['availability'] = $product['quantity'] ?? 0;
if (!isset($product['sku'])) $product['sku'] = strtolower(str_replace(' ', '-', $product['name'])) . '-' . $product['id'];
if (!isset($product['description']) || empty($product['description'])) {
    $product['description'] = 'Product description not available.';
}

// Include header
$headerOnly = true;
include('home.php');
?>

<!-- Page Content Spacing -->
<div class="pt-[150px]"></div>

<!-- Product Detail Section -->
<section class="py-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Product Images -->
            <div>
                <!-- Main Image -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                    <div class="relative">
                        <?php 
                        $productImage = isset($product['image_path']) && !empty($product['image_path']) ? $product['image_path'] : '';
                        $icon = isset($product['icon']) ? $product['icon'] : 'fa-box';
                        $iconColor = isset($product['icon_color']) ? $product['icon_color'] : 'green';
                        $colorClasses = [
                            'red' => 'from-red-50 to-pink-50',
                            'amber' => 'from-amber-50 to-orange-50',
                            'yellow' => 'from-yellow-50 to-amber-50',
                            'green' => 'from-green-50 to-emerald-50',
                            'purple' => 'from-purple-50 to-pink-50',
                            'blue' => 'from-blue-50 to-cyan-50',
                        ];
                        $bgClass = $colorClasses[$iconColor] ?? 'from-green-50 to-emerald-50';
                        $textColor = $iconColor === 'red' ? 'text-red-400' : ($iconColor === 'amber' ? 'text-amber-400' : ($iconColor === 'yellow' ? 'text-yellow-400' : ($iconColor === 'green' ? 'text-green-400' : ($iconColor === 'purple' ? 'text-purple-400' : ($iconColor === 'blue' ? 'text-blue-400' : 'text-green-400')))));
                        ?>
                        <?php if ($productImage): ?>
                            <div id="main-product-image" class="h-96 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                                <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                            </div>
                        <?php else: ?>
                        <div id="main-product-image" class="h-96 bg-gradient-to-br <?php echo $bgClass; ?> flex items-center justify-center rounded-lg">
                            <i class="fas <?php echo $icon; ?> text-9xl <?php echo $textColor; ?>"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Thumbnail Images -->
                <div class="grid grid-cols-4 gap-4">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="bg-white rounded-lg shadow-sm p-3 cursor-pointer hover:shadow-md transition-shadow border-2 border-transparent hover:border-green-500 thumbnail-image" onclick="changeMainImage(<?php echo $i; ?>)">
                            <?php if ($productImage): ?>
                                <div class="h-24 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
                                    <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                                </div>
                            <?php else: ?>
                            <div class="h-24 bg-gradient-to-br <?php echo $bgClass; ?> flex items-center justify-center rounded">
                                <i class="fas <?php echo $icon; ?> text-3xl <?php echo $textColor; ?>"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Product Information -->
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Vendor Name -->
                <?php if (isset($product['vendor_name']) && !empty($product['vendor_name'])): ?>
                    <p class="text-gray-600 mb-4">By <span class="font-semibold text-green-600"><?php echo htmlspecialchars($product['vendor_name']); ?></span></p>
                <?php endif; ?>
                
                <!-- Rating -->
                <div class="flex items-center gap-2 mb-4">
                    <?php
                    $fullStars = floor($product['rating']);
                    $hasHalfStar = ($product['rating'] - $fullStars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) {
                            echo '<i class="far fa-star text-yellow-400 text-lg"></i>';
                        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                            echo '<i class="fas fa-star-half-alt text-yellow-400 text-lg"></i>';
                        } else {
                            echo '<i class="far fa-star text-yellow-400 text-lg"></i>';
                        }
                    }
                    ?>
                    <span class="text-gray-600 text-sm">(<?php echo $product['rating']; ?>)</span>
                </div>
                
                <!-- Availability -->
                <p class="text-gray-600 mb-4">Availability: <span class="font-semibold text-green-600"><?php echo $product['availability']; ?></span></p>
                
                <!-- Price -->
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-3xl font-bold text-green-600">$<?php echo number_format($product['price'], 2); ?> USD</span>
                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                        <span class="text-xl text-gray-400 line-through">$<?php echo number_format($product['original_price'], 2); ?> USD</span>
                        <div class="relative">
                            <div class="absolute -rotate-12 bg-red-600 text-white px-3 py-1 text-sm font-bold border-2 border-white shadow-lg" style="transform: rotate(-12deg);">
                                $<?php echo number_format($product['original_price'] - $product['price'], 2); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Description -->
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($product['description']); ?></p>
                
                <!-- Weight Selector -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Weight:</label>
                    <select class="w-25 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-green-500 font-normal text-gray-600">
                        <option>SELECT</option>
                        <option>1kg</option>
                        <option>2kg</option>
                        <option>3kg</option>
                        <option>4kg</option>
                        <option>5kg</option>
                        <option>6kg</option>
                        <option>7kg</option>
                        <option>8kg</option>
                        <option>9kg</option>
                        <option>10kg</option>
                    </select>
                </div>
                
                <!-- Quantity Selector -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity:</label>
                    <div class="flex items-center gap-3">
                        <button onclick="decreaseQuantity()" class="w-10 h-10 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center">-</button>
                        <input type="number" id="product-quantity" value="1" min="1" max="<?php echo $product['availability']; ?>" class="w-20 h-10 border border-gray-300 rounded-lg text-center focus:outline-none focus:border-green-500">
                        <button onclick="increaseQuantity()" class="w-10 h-10 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center">+</button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-4 mb-6">
                    <button onclick="toggleWishlist('<?php echo $product['id']; ?>', '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', '<?php echo $product['price']; ?>', '<?php echo htmlspecialchars(addslashes($productImage)); ?>')" class="w-12 h-12 border-2 border-yellow-400 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors flex items-center justify-center">
                        <i class="far fa-heart text-yellow-600 text-xl wishlist-icon-<?php echo $product['id']; ?>"></i>
                    </button>
                    <?php if ($product['in_stock']): ?>
                        <button onclick="addToCartFromDetail()" class="bg-orange-500 border border-orange-600 text-white px-8 py-3 rounded-full hover:bg-orange-600 hover:shadow-lg transition-all duration-200 font-semibold text-sm shadow-md">
                            Add to cart
                        </button>
                    <?php else: ?>
                        <button onclick="return false;" disabled class="bg-gray-400 border border-gray-500 text-white px-8 py-3 rounded-full cursor-not-allowed font-semibold text-sm">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                    <button onclick="toggleShareModal()" class="w-12 h-12 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors flex items-center justify-center">
                        <i class="fas fa-share-alt text-gray-600 text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Product Information Tabs -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-12">
            <div class="flex items-center gap-6 border-b border-gray-200 mb-6">
                <button onclick="showTab('description')" id="tab-description" class="px-6 py-3 border-b-2 border-green-600 text-green-600 font-semibold">DESCRIPTION</button>
                <button onclick="showTab('video')" id="tab-video" class="px-6 py-3 border-b-2 border-transparent text-gray-600 hover:text-green-600 font-semibold transition-colors">VIDEO</button>
                <button onclick="showTab('size')" id="tab-size" class="px-6 py-3 border-b-2 border-transparent text-gray-600 hover:text-green-600 font-semibold transition-colors">SIZE CHART</button>
                <button onclick="showTab('reviews')" id="tab-reviews" class="px-6 py-3 border-b-2 border-transparent text-gray-600 hover:text-green-600 font-semibold transition-colors">REVIEWS</button>
            </div>
            
            <div id="tab-content-description" class="tab-content">
                <h3 class="text-xl font-bold text-gray-800 mb-4">More details</h3>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($product['description']); ?>
                </p>
            </div>
            
            <div id="tab-content-video" class="tab-content hidden">
                <div class="aspect-video bg-gray-200 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play-circle text-6xl text-green-600"></i>
                </div>
            </div>
            
            <div id="tab-content-size" class="tab-content hidden">
                <p class="text-gray-600">Size chart information will be displayed here.</p>
            </div>
            
            <div id="tab-content-reviews" class="tab-content hidden">
                <div class="space-y-6">
                    <!-- Rating Form -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Write a Review</h3>
                        <form id="review-form" onsubmit="submitReview(event)">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating</label>
                                <div class="flex items-center gap-2" id="rating-stars">
                                    <button type="button" onclick="setRating(1)" class="star-rating text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="1">
                                        <i class="far fa-star"></i>
                                    </button>
                                    <button type="button" onclick="setRating(2)" class="star-rating text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="2">
                                        <i class="far fa-star"></i>
                                    </button>
                                    <button type="button" onclick="setRating(3)" class="star-rating text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="3">
                                        <i class="far fa-star"></i>
                                    </button>
                                    <button type="button" onclick="setRating(4)" class="star-rating text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="4">
                                        <i class="far fa-star"></i>
                                    </button>
                                    <button type="button" onclick="setRating(5)" class="star-rating text-3xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="5">
                                        <i class="far fa-star"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="rating-value" name="rating" value="0" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="review-name" class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                                <input type="text" id="review-name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" placeholder="Enter your name" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="review-email" class="block text-sm font-medium text-gray-700 mb-2">Your Email</label>
                                <input type="email" id="review-email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="review-title" class="block text-sm font-medium text-gray-700 mb-2">Review Title</label>
                                <input type="text" id="review-title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" placeholder="Give your review a title" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="review-comment" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                                <textarea id="review-comment" name="comment" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" placeholder="Share your thoughts about this product" required></textarea>
                            </div>
                            
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                Submit Review
                            </button>
                        </form>
                    </div>
                    
                    <!-- Existing Reviews -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Customer Reviews</h3>
                        <div id="reviews-container" class="space-y-4">
                            <!-- Reviews will be loaded here -->
                            <p class="text-gray-500 text-sm">No reviews yet. Be the first to review this product!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-8">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $related): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='index.php?page=product-detail&id=<?php echo $related['id']; ?>'">
                        <div class="relative">
                            <?php 
                            $relImage = isset($related['image_path']) && !empty($related['image_path']) ? $related['image_path'] : '';
                            $relIcon = isset($related['icon']) ? $related['icon'] : 'fa-box';
                            $relIconColor = isset($related['icon_color']) ? $related['icon_color'] : 'green';
                            $relBgClass = $colorClasses[$relIconColor] ?? 'from-green-50 to-emerald-50';
                            $relTextColor = $relIconColor === 'red' ? 'text-red-400' : ($relIconColor === 'amber' ? 'text-amber-400' : ($relIconColor === 'yellow' ? 'text-yellow-400' : ($relIconColor === 'green' ? 'text-green-400' : ($relIconColor === 'purple' ? 'text-purple-400' : ($relIconColor === 'blue' ? 'text-blue-400' : 'text-green-400')))));
                            ?>
                            <?php if ($relImage): ?>
                                <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($relImage); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>" class="w-full h-full object-cover">
                                </div>
                            <?php else: ?>
                            <div class="h-48 bg-gradient-to-br <?php echo $relBgClass; ?> flex items-center justify-center">
                                <i class="fas <?php echo $relIcon; ?> text-6xl <?php echo $relTextColor; ?>"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 mb-2 text-sm"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="flex items-center gap-1 mb-2">
                                <?php
                                $relFullStars = floor($related['rating']);
                                $relHasHalfStar = ($related['rating'] - $relFullStars) >= 0.5;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $relFullStars) {
                                        echo '<i class="fas fa-star text-yellow-400 text-xs"></i>';
                                    } elseif ($i == $relFullStars + 1 && $relHasHalfStar) {
                                        echo '<i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>';
                                    } else {
                                        echo '<i class="far fa-star text-yellow-400 text-xs"></i>';
                                    }
                                }
                                ?>
                                <span class="text-xs text-gray-500 ml-1">(<?php echo number_format($related['rating'], 1); ?>)</span>
                            </div>
                            <p class="text-lg font-bold text-green-600">$<?php echo number_format($related['price'], 2); ?> USD</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Share Modal -->
<div id="share-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Share Product</h3>
            <button onclick="toggleShareModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="flex items-center gap-3 justify-center">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="w-12 h-12 bg-[#1877F2] text-white rounded-lg flex items-center justify-center hover:bg-[#166FE5] hover:shadow-md transition-all duration-200" title="Share on Facebook">
                <i class="fab fa-facebook-f text-base"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($product['name']); ?>" target="_blank" class="w-12 h-12 bg-[#1DA1F2] text-white rounded-lg flex items-center justify-center hover:bg-[#1A91DA] hover:shadow-md transition-all duration-200" title="Share on Twitter">
                <i class="fab fa-twitter text-base"></i>
            </a>
            <a href="https://wa.me/?text=<?php echo urlencode($product['name'] . ' - ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="w-12 h-12 bg-[#25D366] text-white rounded-lg flex items-center justify-center hover:bg-[#20BA5A] hover:shadow-md transition-all duration-200" title="Share on WhatsApp">
                <i class="fab fa-whatsapp text-base"></i>
            </a>
            <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&description=<?php echo urlencode($product['name']); ?>" target="_blank" class="w-12 h-12 bg-[#E60023] text-white rounded-lg flex items-center justify-center hover:bg-[#D50C22] hover:shadow-md transition-all duration-200" title="Share on Pinterest">
                <i class="fab fa-pinterest text-base"></i>
            </a>
            <button onclick="copyProductLink()" class="w-12 h-12 bg-gray-600 text-white rounded-lg flex items-center justify-center hover:bg-gray-700 hover:shadow-md transition-all duration-200" title="Copy Link">
                <i class="fas fa-link text-base"></i>
            </button>
        </div>
    </div>
</div>

<script>
let currentQuantity = 1;

function increaseQuantity() {
    const maxQty = <?php echo $product['availability']; ?>;
    if (currentQuantity < maxQty) {
        currentQuantity++;
        document.getElementById('product-quantity').value = currentQuantity;
    }
}

function decreaseQuantity() {
    if (currentQuantity > 1) {
        currentQuantity--;
        document.getElementById('product-quantity').value = currentQuantity;
    }
}

document.getElementById('product-quantity').addEventListener('change', function() {
    const maxQty = <?php echo $product['availability']; ?>;
    const value = parseInt(this.value);
    if (value < 1) {
        currentQuantity = 1;
        this.value = 1;
    } else if (value > maxQty) {
        currentQuantity = maxQty;
        this.value = maxQty;
    } else {
        currentQuantity = value;
    }
});

// Share Modal Functions
function toggleShareModal() {
    const shareModal = document.getElementById('share-modal');
    shareModal.classList.toggle('hidden');
}

function copyProductLink() {
    const currentUrl = window.location.href;
    navigator.clipboard.writeText(currentUrl).then(function() {
        alert('Product link copied to clipboard!');
    }, function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = currentUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Product link copied to clipboard!');
    });
}

// Close share modal when clicking outside
document.addEventListener('click', function(event) {
    const shareModal = document.getElementById('share-modal');
    if (shareModal && !shareModal.querySelector('.bg-white').contains(event.target) && !event.target.closest('[onclick="toggleShareModal()"]')) {
        if (!shareModal.classList.contains('hidden')) {
            shareModal.classList.add('hidden');
        }
    }
});

function changeMainImage(index) {
    // This function can be extended to change the main image when clicking thumbnails
    // For now, it just highlights the selected thumbnail
    document.querySelectorAll('.thumbnail-image').forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('border-green-500');
            thumb.classList.remove('border-transparent');
        } else {
            thumb.classList.remove('border-green-500');
            thumb.classList.add('border-transparent');
        }
    });
}

// Rating Functions
let currentRating = 0;

function setRating(rating) {
    currentRating = rating;
    document.getElementById('rating-value').value = rating;
    
    const stars = document.querySelectorAll('.star-rating');
    stars.forEach((star, index) => {
        const starIcon = star.querySelector('i');
        if (index < rating) {
            starIcon.classList.remove('far');
            starIcon.classList.add('fas');
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            starIcon.classList.remove('fas');
            starIcon.classList.add('far');
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

// Initialize star hover effects
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating');
    if (stars.length > 0) {
        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', function() {
                const hoverRating = index + 1;
                stars.forEach((s, i) => {
                    const starIcon = s.querySelector('i');
                    if (i < hoverRating) {
                        starIcon.classList.remove('far');
                        starIcon.classList.add('fas');
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-400');
                    }
                });
            });
            
            star.addEventListener('mouseleave', function() {
                stars.forEach((s, i) => {
                    const starIcon = s.querySelector('i');
                    if (i < currentRating) {
                        starIcon.classList.remove('far');
                        starIcon.classList.add('fas');
                        s.classList.remove('text-gray-300');
                        s.classList.add('text-yellow-400');
                    } else {
                        starIcon.classList.remove('fas');
                        starIcon.classList.add('far');
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-300');
                    }
                });
            });
        });
        
        // Load existing reviews
        loadReviews();
    }
});

function submitReview(event) {
    event.preventDefault();
    
    const rating = document.getElementById('rating-value').value;
    if (rating === '0' || rating === 0) {
        alert('Please select a rating');
        return;
    }
    
    const reviewData = {
        productId: '<?php echo $product['id']; ?>',
        name: document.getElementById('review-name').value,
        email: document.getElementById('review-email').value,
        title: document.getElementById('review-title').value,
        comment: document.getElementById('review-comment').value,
        rating: rating,
        date: new Date().toLocaleDateString()
    };
    
    // Store review in localStorage (in a real app, this would be sent to a server)
    let reviews = JSON.parse(localStorage.getItem('product_reviews') || '{}');
    if (!reviews[reviewData.productId]) {
        reviews[reviewData.productId] = [];
    }
    reviews[reviewData.productId].push(reviewData);
    localStorage.setItem('product_reviews', JSON.stringify(reviews));
    
    // Reset form
    document.getElementById('review-form').reset();
    currentRating = 0;
    setRating(0);
    
    // Reload reviews
    loadReviews();
    
    alert('Thank you for your review!');
}

function loadReviews() {
    const productId = '<?php echo $product['id']; ?>';
    const reviews = JSON.parse(localStorage.getItem('product_reviews') || '{}');
    const productReviews = reviews[productId] || [];
    
    const container = document.getElementById('reviews-container');
    if (!container) return;
    
    if (productReviews.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm">No reviews yet. Be the first to review this product!</p>';
        return;
    }
    
    container.innerHTML = productReviews.map(review => `
        <div class="border-b border-gray-200 pb-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h4 class="font-semibold text-gray-800">${escapeHtml(review.name)}</h4>
                    <p class="text-sm text-gray-500">${escapeHtml(review.date)}</p>
                </div>
                <div class="flex items-center gap-1">
                    ${generateStars(review.rating)}
                </div>
            </div>
            <h5 class="font-medium text-gray-700 mb-2">${escapeHtml(review.title)}</h5>
            <p class="text-gray-600 text-sm">${escapeHtml(review.comment)}</p>
        </div>
    `).join('');
}

function generateStars(rating) {
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += '<i class="fas fa-star text-yellow-400"></i>';
        } else {
            starsHtml += '<i class="far fa-star text-gray-300"></i>';
        }
    }
    return starsHtml;
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        tab.classList.remove('border-green-600', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-600');
    });
    
    // Show selected tab content
    document.getElementById('tab-content-' + tabName).classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-600');
    activeTab.classList.add('border-green-600', 'text-green-600');
    
    // Load reviews if reviews tab is selected
    if (tabName === 'reviews') {
        loadReviews();
    }
}

function addToCartFromDetail() {
    const inStock = <?php echo $product['in_stock'] ? 'true' : 'false'; ?>;
    
    // Prevent adding out-of-stock products to cart
    if (!inStock) {
        alert('This product is currently out of stock.');
        return false;
    }
    
    const quantity = parseInt(document.getElementById('product-quantity').value) || 1;
    const productId = '<?php echo $product['id']; ?>';
    const productName = '<?php echo htmlspecialchars(addslashes($product['name'])); ?>';
    const productPrice = '$<?php echo number_format($product['price'], 2); ?>';
    
    // Add to cart with quantity
    if (typeof addToCart === 'function') {
        const productImage = '<?php echo htmlspecialchars(addslashes($productImage)); ?>';
        for (let i = 0; i < quantity; i++) {
            addToCart(productId, productName, productPrice, productImage);
        }
    } else {
        alert('Added ' + quantity + ' item(s) to cart!');
    }
}

// Initialize wishlist icon state on page load
document.addEventListener('DOMContentLoaded', function() {
    window.wishlist = window.wishlist || JSON.parse(localStorage.getItem('wishlist')) || [];
    const productId = '<?php echo $product['id']; ?>';
    const isInWishlist = window.wishlist.some(item => item.id == productId);
    
    if (isInWishlist) {
        const icon = document.querySelector('.wishlist-icon-<?php echo $product['id']; ?>');
        if (icon) {
            icon.classList.remove('far');
            icon.classList.add('fas', 'text-red-500');
        }
    }
});
</script>
