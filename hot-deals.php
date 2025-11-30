<?php
session_start();
include('config.php');
include('nav-gradient.php');
$isAdmin = isset($_SESSION['admin']);
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
                        <a href="index.php?page=all-products" class="text-white hover:text-green-300 transition-colors font-medium text-sm whitespace-nowrap">
                            Browse All Categories
                        </a>
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
                                    <input id="search-input" type="text" placeholder="Search products, brands, categories..." class="w-full pl-12 pr-16 py-3.5 backdrop-blur-xl border-2 border-gray-700/30 rounded-xl focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all duration-300 text-white placeholder-gray-400 font-medium shadow-lg" style="background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(48px); -webkit-backdrop-filter: blur(48px); opacity: 0.95;" autocomplete="off" oninput="showSearchSuggestions(this.value)" onfocus="showSearchSuggestions(this.value)" onblur="setTimeout(() => hideSearchSuggestions(), 200)">
                                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white w-10 h-10 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center justify-center z-10">
                                        <i class="fas fa-search text-base"></i>
                                    </button>
                                    <!-- Search Suggestions Dropdown -->
                                    <div id="search-suggestions" class="absolute top-full left-0 right-0 mt-2 backdrop-blur-xl rounded-xl shadow-xl border border-gray-700/30 max-h-[300px] overflow-y-auto z-50 hidden" style="background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(48px); -webkit-backdrop-filter: blur(48px); opacity: 0.95;">
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
<div class="fixed top-4 right-6 z-50 flex items-center gap-4">
    <div class="flex items-center gap-4">
        <button onclick="toggleCartModal()" class="text-gray-600 hover:text-green-600 transition-colors relative group">
            <i class="fas fa-shopping-cart text-xl"></i>
            <span id="cart-count" class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
            <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Cart</span>
        </button>
    </div>
</div>

<!-- Page Content Spacing -->
<div class="pt-[100px]"></div>

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
        <!-- Category Card 1 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-utensils text-3xl text-yellow-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Cake & Milk</h3>
            <p class="text-xs text-gray-500">11 items</p>
        </div>
        
        <!-- Category Card 2 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-kiwi-bird text-3xl text-green-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Organic Kiwi</h3>
            <p class="text-xs text-gray-500">6 items</p>
        </div>
        
        <!-- Category Card 3 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-peach text-3xl text-orange-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Peach</h3>
            <p class="text-xs text-gray-500">8 items</p>
        </div>
        
        <!-- Category Card 4 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-apple-alt text-3xl text-red-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Red Apple</h3>
            <p class="text-xs text-gray-500">12 items</p>
        </div>
        
        <!-- Category Card 5 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-amber-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-cookie text-3xl text-amber-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Snacks</h3>
            <p class="text-xs text-gray-500">15 items</p>
        </div>
        
        <!-- Category Card 6 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-leaf text-3xl text-green-700"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Vegetables</h3>
            <p class="text-xs text-gray-500">20 items</p>
        </div>
        
        <!-- Category Card 7 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-pink-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-strawberry text-3xl text-pink-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Strawberry</h3>
            <p class="text-xs text-gray-500">9 items</p>
        </div>
        
        <!-- Category Card 8 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-apple-alt text-3xl text-purple-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Black plum</h3>
            <p class="text-xs text-gray-500">7 items</p>
        </div>
        
        <!-- Category Card 9 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-apple-alt text-3xl text-yellow-700"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Custard apple</h3>
            <p class="text-xs text-gray-500">5 items</p>
        </div>
        
        <!-- Category Card 10 -->
        <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
            <div class="w-16 h-16 bg-amber-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-coffee text-3xl text-amber-800"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm mb-1">Coffee & Tea</h3>
            <p class="text-xs text-gray-500">13 items</p>
        </div>
    </div>
</div>

<!-- Hot Deals Hero Section -->
<section class="py-12">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl p-10 text-center border border-red-200">
            <div class="flex items-center justify-center gap-3 mb-4">
                <i class="fas fa-fire text-4xl text-red-500"></i>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800">Hot Deals</h1>
                <i class="fas fa-fire text-4xl text-red-500"></i>
            </div>
            <p class="text-gray-600 text-lg mb-6">Limited time offers! Don't miss out on these incredible discounts</p>
            <div class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-clock"></i>
                <span>Ends Soon!</span>
            </div>
        </div>
    </div>
</section>

<!-- Featured Hot Deals -->
<section class="py-12">
    <div class="max-w-screen-2xl mx-auto px-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Featured Deals</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            <!-- Product Card 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow border-2 border-red-200">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-red-600 text-white px-3 py-1 rounded text-sm font-bold z-10">66% OFF</div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold z-10">
                        <i class="fas fa-fire"></i> Hot
                    </div>
                    <div class="h-48 bg-gradient-to-br from-red-50 to-pink-50 flex items-center justify-center">
                        <i class="fas fa-drumstick-bite text-6xl text-red-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Fresh Seafood</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">All Natural Style Chicken Meatballs</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="far fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs text-gray-500 ml-1">(3.5)</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl font-bold text-red-600">$52.85</span>
                        <span class="text-sm text-gray-400 line-through">$155.00</span>
                    </div>
                    <button onclick="addToCart('hd1', 'All Natural Style Chicken Meatballs', '$52.85', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Product Card 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow border-2 border-orange-200">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-orange-500 text-white px-3 py-1 rounded text-sm font-bold z-10">50% OFF</div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold z-10">
                        <i class="fas fa-fire"></i> Hot
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
                        <span class="text-xl font-bold text-red-600">$28.85</span>
                        <span class="text-sm text-gray-400 line-through">$57.70</span>
                    </div>
                    <button onclick="addToCart('hd2', 'Seeds of Change Organic Red Rice', '$28.85', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Product Card 3 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow border-2 border-red-200">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-red-600 text-white px-3 py-1 rounded text-sm font-bold z-10">40% OFF</div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold z-10">
                        <i class="fas fa-fire"></i> Hot
                    </div>
                    <div class="h-48 bg-gradient-to-br from-yellow-50 to-amber-50 flex items-center justify-center">
                        <i class="fas fa-cheese text-6xl text-yellow-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Milks & Dairies</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">Chobani Complete Vanilla</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs text-gray-500 ml-1">(4.9)</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl font-bold text-red-600">$54.85</span>
                        <span class="text-sm text-gray-400 line-through">$91.42</span>
                    </div>
                    <button onclick="addToCart('hd3', 'Chobani Complete Vanilla', '$54.85', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Product Card 4 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow border-2 border-orange-200">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-orange-500 text-white px-3 py-1 rounded text-sm font-bold z-10">30% OFF</div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold z-10">
                        <i class="fas fa-fire"></i> Hot
                    </div>
                    <div class="h-48 bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center">
                        <i class="fas fa-leaf text-6xl text-green-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Vegetables</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">Blue Almonds Lightly Salted Vegetables</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="far fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs text-gray-500 ml-1">(3.5)</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl font-bold text-red-600">$23.85</span>
                        <span class="text-sm text-gray-400 line-through">$34.07</span>
                    </div>
                    <button onclick="addToCart('hd4', 'Blue Almonds Lightly Salted Vegetables', '$23.85', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Product Card 5 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow border-2 border-red-200">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-red-600 text-white px-3 py-1 rounded text-sm font-bold z-10">25% OFF</div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold z-10">
                        <i class="fas fa-fire"></i> Hot
                    </div>
                    <div class="h-48 bg-gradient-to-br from-purple-50 to-pink-50 flex items-center justify-center">
                        <i class="fas fa-ice-cream text-6xl text-purple-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Milks & Dairies</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">Haagen Caramel Cone Ice Cream</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs text-gray-500 ml-1">(4.5)</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl font-bold text-red-600">$42.85</span>
                        <span class="text-sm text-gray-400 line-through">$57.13</span>
                    </div>
                    <button onclick="addToCart('hd5', 'Haagen Caramel Cone Ice Cream', '$42.85', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Product Card 6 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow border-2 border-orange-200">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-orange-500 text-white px-3 py-1 rounded text-sm font-bold z-10">20% OFF</div>
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold z-10">
                        <i class="fas fa-fire"></i> Hot
                    </div>
                    <div class="h-48 bg-gradient-to-br from-blue-50 to-cyan-50 flex items-center justify-center">
                        <i class="fas fa-fish text-6xl text-blue-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Fresh Seafood</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">Gorton's Beer Battered Fish</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <i class="far fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs text-gray-500 ml-1">(3.8)</span>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl font-bold text-red-600">$32.85</span>
                        <span class="text-sm text-gray-400 line-through">$41.06</span>
                    </div>
                    <button onclick="addToCart('hd6', 'Gorton's Beer Battered Fish', '$32.85', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flash Sale Section -->
<section class="py-12 bg-red-50">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <i class="fas fa-bolt text-3xl text-red-600"></i>
                <h2 class="text-3xl font-bold text-gray-800">Flash Sale</h2>
            </div>
            <div class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-clock"></i>
                <span class="font-semibold">Ends in: <span id="countdown">23:59:59</span></span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            <!-- Similar product cards with higher discounts -->
            <!-- Product Card 1 -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow border-2 border-red-500">
                <div class="relative">
                    <div class="absolute top-2 left-2 bg-red-600 text-white px-3 py-1 rounded text-sm font-bold z-10">FLASH SALE</div>
                    <div class="h-48 bg-gradient-to-br from-red-50 to-pink-50 flex items-center justify-center">
                        <i class="fas fa-drumstick-bite text-6xl text-red-400"></i>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1">Fresh Seafood</p>
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm">All Natural Style Chicken Meatballs</h3>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl font-bold text-red-600">$45.99</span>
                        <span class="text-sm text-gray-400 line-through">$155.00</span>
                    </div>
                    <button onclick="addToCart('fs1', 'All Natural Style Chicken Meatballs', '$45.99', '')" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm text-center">
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- More flash sale products can be added here -->
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16">
    <div class="max-w-screen-2xl mx-auto px-6">
        <div class="bg-gradient-to-r from-red-600 to-orange-600 rounded-2xl p-10 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Don't Miss Out!</h2>
            <p class="text-red-50 mb-6 text-lg">More hot deals are coming. Browse our full catalog</p>
            <a href="index.php?page=all-products" class="inline-block px-8 py-3 bg-white text-red-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors">View All Products</a>
        </div>
    </div>
</section>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-[140px] right-6 z-[100] space-y-2 pointer-events-none"></div>

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

<script>
// Initialize cart from localStorage
let cart = JSON.parse(localStorage.getItem('cart')) || [];

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

// Cart Modal Functions
function toggleCartModal() {
    const modal = document.getElementById('cart-modal');
    modal.classList.toggle('hidden');
    updateCartDisplay();
}

function addToCart(productId, productName, productPrice, productImage, quantity = 1) {
    productImage = resolveProductImage(productImage);
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
            return `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
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

function closeModalOnBackdrop(event, modalId) {
    if (event.target.id === modalId || event.target.classList.contains('bg-black')) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
    }
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
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

// Simple countdown timer
function updateCountdown() {
    const countdownEl = document.getElementById('countdown');
    if (!countdownEl) return;
    
    let time = countdownEl.textContent.split(':');
    let hours = parseInt(time[0]);
    let minutes = parseInt(time[1]);
    let seconds = parseInt(time[2]);
    
    if (seconds > 0) {
        seconds--;
    } else if (minutes > 0) {
        minutes--;
        seconds = 59;
    } else if (hours > 0) {
        hours--;
        minutes = 59;
        seconds = 59;
    } else {
        hours = 23;
        minutes = 59;
        seconds = 59;
    }
    
    countdownEl.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

setInterval(updateCountdown, 1000);
</script>

