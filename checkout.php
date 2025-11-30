<?php
session_start();
$isAdmin = isset($_SESSION['admin']);
$isUser = isset($_SESSION['user_id']);
$currentUser = $isAdmin ? $_SESSION['admin'] : ($isUser ? $_SESSION['username'] : 'Guest User');
$currentEmail = $isAdmin ? ($_SESSION['admin'] . '@admin.com') : ($isUser ? $_SESSION['user_email'] : 'guest@example.com');
$currentFirstName = $isUser ? ($_SESSION['user_first_name'] ?? 'Guest') : ($isAdmin ? $_SESSION['admin'] : 'Guest');
$currentLastName = $isUser ? ($_SESSION['user_last_name'] ?? 'User') : ($isAdmin ? 'Admin' : 'User');

// Include header
$headerOnly = true;
include('home.php');
?>

<!-- Page Content Spacing -->
<div class="pt-[150px]"></div>

<!-- Checkout Section -->
<section class="py-8 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form - Left Side (2 columns) -->
            <div class="lg:col-span-2">
                <!-- Billing Information -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-user-circle text-green-600 mr-2"></i>
                        Billing Information
                    </h2>
                    
                    <form id="checkout-form" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first-name" class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                                <input type="text" id="first-name" name="first_name" required value="<?php echo htmlspecialchars($currentFirstName); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="last-name" class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" id="last-name" name="last_name" required value="<?php echo htmlspecialchars($currentLastName); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($currentEmail); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" required placeholder="+1 (555) 123-4567" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                        </div>
                        
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                            <input type="text" id="address" name="address" required placeholder="123 Main Street" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                <input type="text" id="city" name="city" required placeholder="New York" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
                                <input type="text" id="state" name="state" required placeholder="NY" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="zip" class="block text-sm font-medium text-gray-700 mb-1">ZIP Code <span class="text-red-500">*</span></label>
                                <input type="text" id="zip" name="zip" required placeholder="10001" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="same-address" name="same_address" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <label for="same-address" class="ml-2 text-sm text-gray-700">Ship to different address?</label>
                        </div>
                    </form>
                </div>
                
                <!-- Shipping Information (Hidden by default) -->
                <div id="shipping-section" class="bg-white rounded-lg shadow-md p-6 mb-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-truck text-green-600 mr-2"></i>
                        Shipping Information
                    </h2>
                    
                    <form id="shipping-form" class="space-y-4">
                        <div>
                            <label for="shipping-address" class="block text-sm font-medium text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                            <input type="text" id="shipping-address" name="shipping_address" placeholder="123 Main Street" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="shipping-city" class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                <input type="text" id="shipping-city" name="shipping_city" placeholder="New York" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="shipping-state" class="block text-sm font-medium text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
                                <input type="text" id="shipping-state" name="shipping_state" placeholder="NY" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                            <div>
                                <label for="shipping-zip" class="block text-sm font-medium text-gray-700 mb-1">ZIP Code <span class="text-red-500">*</span></label>
                                <input type="text" id="shipping-zip" name="shipping_zip" placeholder="10001" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-credit-card text-green-600 mr-2"></i>
                        Payment Method
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="border border-gray-300 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-all payment-method" data-method="credit-card">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input type="radio" name="payment_method" value="credit-card" id="credit-card" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500" checked>
                                    <label for="credit-card" class="ml-3 text-sm font-medium text-gray-700">
                                        <i class="far fa-credit-card mr-2"></i>Credit / Debit Card
                                    </label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fab fa-cc-visa text-2xl text-blue-600"></i>
                                    <i class="fab fa-cc-mastercard text-2xl text-red-600"></i>
                                    <i class="fab fa-cc-amex text-2xl text-blue-500"></i>
                                    <i class="fab fa-cc-discover text-2xl text-orange-500"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div id="credit-card-form" class="ml-7 mt-4 space-y-4">
                            <div>
                                <label for="card-number" class="block text-sm font-medium text-gray-700 mb-1">Card Number <span class="text-red-500">*</span></label>
                                <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="card-expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiry Date <span class="text-red-500">*</span></label>
                                    <input type="text" id="card-expiry" name="card_expiry" placeholder="MM/YY" maxlength="5" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                                </div>
                                <div>
                                    <label for="card-cvv" class="block text-sm font-medium text-gray-700 mb-1">CVV <span class="text-red-500">*</span></label>
                                    <input type="text" id="card-cvv" name="card_cvv" placeholder="123" maxlength="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                                </div>
                            </div>
                            
                            <div>
                                <label for="card-name" class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name <span class="text-red-500">*</span></label>
                                <input type="text" id="card-name" name="card_name" placeholder="John Doe" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none">
                            </div>
                        </div>
                        
                        <div class="border border-gray-300 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-all payment-method" data-method="paypal">
                            <div class="flex items-center">
                                <input type="radio" name="payment_method" value="paypal" id="paypal" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <label for="paypal" class="ml-3 text-sm font-medium text-gray-700">
                                    <i class="fab fa-paypal text-blue-600 mr-2"></i>PayPal
                                </label>
                            </div>
                        </div>
                        
                        <div class="border border-gray-300 rounded-lg p-4 cursor-pointer hover:border-green-500 transition-all payment-method" data-method="cash-on-delivery">
                            <div class="flex items-center">
                                <input type="radio" name="payment_method" value="cash-on-delivery" id="cash-on-delivery" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <label for="cash-on-delivery" class="ml-3 text-sm font-medium text-gray-700">
                                    <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>Cash on Delivery
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary - Right Side (1 column) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-32">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-shopping-bag text-green-600 mr-2"></i>
                        Order Summary
                    </h2>
                    
                    <!-- Cart Items -->
                    <div id="checkout-cart-items" class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                        <!-- Cart items will be populated by JavaScript -->
                    </div>
                    
                    <!-- Order Totals -->
                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Shipping</span>
                            <span id="shipping-cost">$10.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tax</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-800 pt-3 border-t border-gray-200">
                            <span>Total</span>
                            <span id="total" class="text-green-600">$0.00</span>
                        </div>
                    </div>
                    
                    <!-- Place Order Button -->
                    <button id="place-order-btn" onclick="placeOrder()" class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i>
                        Place Order
                    </button>
                    
                    <!-- Security Badge -->
                    <div class="mt-4 text-center text-xs text-gray-500">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Secure checkout. Your payment is encrypted.
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <!-- Order Confirmation Modal -->
    <div id="order-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4" style="z-index: 99999 !important; position: fixed !important;" onclick="closeOrderConfirmationModal(event)">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto relative" style="z-index: 100000 !important;" onclick="event.stopPropagation()">
            <!-- Success Icon Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 rounded-t-2xl text-center relative">
                <div class="absolute top-4 right-4">
                    <button onclick="closeOrderConfirmationModal()" class="text-white hover:text-gray-200 transition-colors p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto shadow-lg">
                        <i class="fas fa-check-circle text-green-600 text-5xl"></i>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">Order Placed Successfully!</h2>
                <p class="text-green-100 text-lg">Thank you for your purchase</p>
            </div>

            <!-- Order Details -->
            <div class="p-8">
                <!-- Order Number -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Order Number</p>
                            <p id="confirmation-order-number" class="text-2xl font-bold text-gray-800">#ORD-000000</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 mb-1">Order Date</p>
                            <p id="confirmation-order-date" class="text-lg font-semibold text-gray-800"></p>
                        </div>
                    </div>
                </div>

                <!-- Estimated Delivery -->
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-truck text-blue-500 text-xl mr-3 mt-1"></i>
                        <div>
                            <p class="font-semibold text-blue-900 mb-1">Estimated Delivery</p>
                            <p id="confirmation-delivery-date" class="text-blue-700"></p>
                            <p class="text-sm text-blue-600 mt-1">You will receive a confirmation email shortly</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shopping-bag text-green-600 mr-2"></i>
                        Order Summary
                    </h3>
                    <div id="confirmation-items" class="space-y-3 mb-4">
                        <!-- Items will be populated by JavaScript -->
                    </div>
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span id="confirmation-subtotal">$0.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Shipping</span>
                            <span id="confirmation-shipping">$0.00</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tax</span>
                            <span id="confirmation-tax">$0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-800 pt-2 border-t border-gray-200">
                            <span>Total</span>
                            <span id="confirmation-total" class="text-green-600">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                        Shipping Address
                    </h3>
                    <div id="confirmation-address" class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                        <!-- Address will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-credit-card text-green-600 mr-2"></i>
                        Payment Method
                    </h3>
                    <div id="confirmation-payment" class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                        <!-- Payment info will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                    <button onclick="closeOrderConfirmationModal(true)" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center">
                        <i class="fas fa-home mr-2"></i>
                        Continue Shopping
                    </button>
                    <button onclick="printOrderConfirmation()" class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-print mr-2"></i>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
// Checkout-specific cart functions - don't rely on home.php's cart variable
function getCartFromStorage() {
    try {
        const cartData = localStorage.getItem('cart');
        return cartData ? JSON.parse(cartData) : [];
    } catch (e) {
        console.error('Error reading cart from localStorage:', e);
        return [];
    }
}

// Function to auto-fill address fields from localStorage
function autoFillAddressFields() {
    try {
        // Get addresses from localStorage
        const addresses = JSON.parse(localStorage.getItem('addresses')) || [];
        
        // Find the default address
        const defaultAddress = addresses.find(addr => addr.isDefault === true) || addresses[0];
        
        if (defaultAddress) {
            // Fill in billing address fields
            const phoneInput = document.getElementById('phone');
            const addressInput = document.getElementById('address');
            const cityInput = document.getElementById('city');
            const stateInput = document.getElementById('state');
            const zipInput = document.getElementById('zip');
            
            // Only fill if the field is empty
            if (phoneInput && !phoneInput.value) {
                // Check if phone is stored in localStorage separately or in address
                const storedPhone = localStorage.getItem('userPhone') || defaultAddress.phone || '';
                if (storedPhone) {
                    phoneInput.value = storedPhone;
                }
            }
            
            if (addressInput && !addressInput.value && defaultAddress.street) {
                addressInput.value = defaultAddress.street;
            }
            
            if (cityInput && !cityInput.value && defaultAddress.city) {
                cityInput.value = defaultAddress.city;
            }
            
            if (stateInput && !stateInput.value && defaultAddress.state) {
                stateInput.value = defaultAddress.state;
            }
            
            if (zipInput && !zipInput.value && defaultAddress.zip) {
                zipInput.value = defaultAddress.zip;
            }
        }
    } catch (e) {
        console.error('Error auto-filling address fields:', e);
    }
}

// Function to auto-fill shipping address fields
function autoFillShippingAddress() {
    try {
        // Get addresses from localStorage
        const addresses = JSON.parse(localStorage.getItem('addresses')) || [];
        
        // Find a non-default address for shipping, or use default if only one exists
        let shippingAddress = addresses.find(addr => !addr.isDefault) || addresses.find(addr => addr.isDefault === true) || addresses[0];
        
        if (shippingAddress) {
            const shippingAddressInput = document.getElementById('shipping-address');
            const shippingCityInput = document.getElementById('shipping-city');
            const shippingStateInput = document.getElementById('shipping-state');
            const shippingZipInput = document.getElementById('shipping-zip');
            
            // Only fill if the field is empty
            if (shippingAddressInput && !shippingAddressInput.value && shippingAddress.street) {
                shippingAddressInput.value = shippingAddress.street;
            }
            
            if (shippingCityInput && !shippingCityInput.value && shippingAddress.city) {
                shippingCityInput.value = shippingAddress.city;
            }
            
            if (shippingStateInput && !shippingStateInput.value && shippingAddress.state) {
                shippingStateInput.value = shippingAddress.state;
            }
            
            if (shippingZipInput && !shippingZipInput.value && shippingAddress.zip) {
                shippingZipInput.value = shippingAddress.zip;
            }
        }
    } catch (e) {
        console.error('Error auto-filling shipping address fields:', e);
    }
}

// Initialize checkout page
document.addEventListener('DOMContentLoaded', function() {
    // Load and display cart immediately
    const cart = getCartFromStorage();
    
    if (!cart || cart.length === 0) {
        // Redirect to home if cart is empty
        window.location.href = 'index.php?page=home';
        return;
    }
    
    // Auto-fill address fields from localStorage
    autoFillAddressFields();
    
    // Update display
    updateCheckoutDisplay();
    
    // Toggle shipping section
    const sameAddressCheckbox = document.getElementById('same-address');
    if (sameAddressCheckbox) {
        sameAddressCheckbox.addEventListener('change', function() {
            const shippingSection = document.getElementById('shipping-section');
            if (this.checked) {
                shippingSection.classList.remove('hidden');
                // Auto-fill shipping address from localStorage if available
                autoFillShippingAddress();
            } else {
                shippingSection.classList.add('hidden');
            }
        });
    }
    
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Show/hide credit card form
            const creditCardForm = document.getElementById('credit-card-form');
            if (radio.value === 'credit-card') {
                creditCardForm.classList.remove('hidden');
            } else {
                creditCardForm.classList.add('hidden');
            }
        });
    });
    
    // Format card number input
    document.getElementById('card-number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.substring(0, 19);
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });
    
    // Format expiry date input
    document.getElementById('card-expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
    
    // Format CVV input (numbers only)
    document.getElementById('card-cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
});

function displayCheckoutError(message) {
    if (typeof showToast === 'function') {
        showToast(message, 'error');
    } else {
        alert(message);
    }
}

function isValidCardNumber(cardNumber) {
    const sanitized = cardNumber.replace(/\D/g, '');
    if (sanitized.length < 13 || sanitized.length > 19) {
        return false;
    }

    let sum = 0;
    let shouldDouble = false;

    for (let i = sanitized.length - 1; i >= 0; i--) {
        let digit = parseInt(sanitized.charAt(i), 10);

        if (shouldDouble) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }

        sum += digit;
        shouldDouble = !shouldDouble;
    }

    return sum % 10 === 0;
}

function isValidExpiry(expiry) {
    const match = /^([0-9]{2})\/([0-9]{2})$/.exec(expiry);
    if (!match) {
        return false;
    }

    const month = parseInt(match[1], 10);
    const year = parseInt(match[2], 10);

    if (month < 1 || month > 12) {
        return false;
    }

    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear() % 100; // last two digits

    if (year < currentYear) {
        return false;
    }

    if (year === currentYear && month < currentMonth) {
        return false;
    }

    return true;
}

function isValidCvv(cvv) {
    return /^\d{3,4}$/.test(cvv);
}

function updateCheckoutDisplay() {
    // Always load fresh from localStorage - don't rely on any existing cart variable
    const cart = getCartFromStorage();
    
    const container = document.getElementById('checkout-cart-items');
    
    if (!container) {
        console.error('checkout-cart-items container not found');
        return;
    }
    
    if (!cart || cart.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">Your cart is empty</p>';
        if (document.getElementById('subtotal')) document.getElementById('subtotal').textContent = '$0.00';
        if (document.getElementById('tax')) document.getElementById('tax').textContent = '$0.00';
        if (document.getElementById('total')) document.getElementById('total').textContent = '$0.00';
        return;
    }
    
    console.log('Cart items found:', cart.length, cart);
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        // Handle price - it might be a string with $ or just a number
        let price = 0;
        if (typeof item.price === 'string') {
            price = parseFloat(item.price.replace('$', '').replace(',', '').trim()) || 0;
        } else if (typeof item.price === 'number') {
            price = item.price;
        }
        
        const quantity = parseInt(item.quantity) || 1;
        const itemTotal = price * quantity;
        subtotal += itemTotal;
        
        const itemName = item.name || 'Unknown Product';
        const itemImage = item.image || '';
        
        html += `
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                    ${itemImage ? `<img src="${itemImage}" alt="${itemName}" class="w-full h-full object-cover rounded-lg" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-box text-gray-400 text-xl\\'></i>'">` : `<i class="fas fa-box text-gray-400 text-xl"></i>`}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-gray-800 truncate">${itemName}</h4>
                    <p class="text-xs text-gray-500 mt-1">Qty: ${quantity} × $${price.toFixed(2)}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">$${itemTotal.toFixed(2)}</p>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Calculate totals
    const shippingCost = 10.00;
    const taxRate = 0.08; // 8% tax
    const tax = subtotal * taxRate;
    const total = subtotal + shippingCost + tax;
    
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
}

function placeOrder() {
    const form = document.getElementById('checkout-form');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const cart = getCartFromStorage();
    if (!cart || cart.length === 0) {
        alert('Your cart is empty. Please add items to your cart before placing an order.');
        window.location.href = 'index.php?page=home';
        return;
    }

    const placeOrderBtn = document.getElementById('place-order-btn');
    if (placeOrderBtn) {
        placeOrderBtn.disabled = true;
        placeOrderBtn.classList.add('opacity-70');
        placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    }

    const phoneInput = document.getElementById('phone');
    if (phoneInput && phoneInput.value) {
        localStorage.setItem('userPhone', phoneInput.value);
    }

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    if (paymentMethod === 'credit-card') {
        const cardNumberInput = document.getElementById('card-number');
        const cardExpiryInput = document.getElementById('card-expiry');
        const cardCvvInput = document.getElementById('card-cvv');
        const cardNameInput = document.getElementById('card-name');

        const rawCardNumber = cardNumberInput.value.replace(/\s/g, '');
        const cardExpiry = cardExpiryInput.value.trim();
        const cardCvv = cardCvvInput.value.trim();
        const cardName = cardNameInput.value.trim();

        if (!isValidCardNumber(rawCardNumber)) {
            displayCheckoutError('Please enter a valid card number.');
            cardNumberInput.focus();
            if (placeOrderBtn) resetPlaceOrderButton(placeOrderBtn);
            return;
        }

        if (!isValidExpiry(cardExpiry)) {
            displayCheckoutError('Please enter a valid expiry date (MM/YY).');
            cardExpiryInput.focus();
            if (placeOrderBtn) resetPlaceOrderButton(placeOrderBtn);
            return;
        }

        if (!isValidCvv(cardCvv)) {
            displayCheckoutError('Please enter a valid CVV.');
            cardCvvInput.focus();
            if (placeOrderBtn) resetPlaceOrderButton(placeOrderBtn);
            return;
        }

        if (!cardName) {
            displayCheckoutError('Please enter the cardholder name.');
            cardNameInput.focus();
            if (placeOrderBtn) resetPlaceOrderButton(placeOrderBtn);
            return;
        }
    }

    const billingData = {
        firstName: document.getElementById('first-name').value,
        lastName: document.getElementById('last-name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        state: document.getElementById('state').value,
        zip: document.getElementById('zip').value
    };

    const shippingData = document.getElementById('same-address').checked ? {
        address: document.getElementById('shipping-address').value,
        city: document.getElementById('shipping-city').value,
        state: document.getElementById('shipping-state').value,
        zip: document.getElementById('shipping-zip').value
    } : null;

    const totalsData = {
        subtotal: parseFloat(document.getElementById('subtotal').textContent.replace('$', '')),
        shipping: parseFloat(document.getElementById('shipping-cost').textContent.replace('$', '')),
        tax: parseFloat(document.getElementById('tax').textContent.replace('$', '')),
        total: parseFloat(document.getElementById('total').textContent.replace('$', ''))
    };

    const clientOrderData = {
        billing: billingData,
        shipping: shippingData,
        payment: {
            method: paymentMethod,
            cardNumber: paymentMethod === 'credit-card' ? document.getElementById('card-number').value : null,
            cardExpiry: paymentMethod === 'credit-card' ? document.getElementById('card-expiry').value : null,
            cardCvv: paymentMethod === 'credit-card' ? document.getElementById('card-cvv').value : null,
            cardName: paymentMethod === 'credit-card' ? document.getElementById('card-name').value : null
        }
    };

    const payload = {
        billing: billingData,
        shipping: shippingData,
        payment: { method: paymentMethod },
        cart: cart.map(item => ({
            id: item.id,
            quantity: parseInt(item.quantity, 10) || 1
        })),
        totals: totalsData
    };

    fetch('create_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to place order.');
            }

            localStorage.setItem('cart', JSON.stringify([]));
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }

            const confirmationData = {
                billing: billingData,
                shipping: shippingData,
                payment: clientOrderData.payment,
                cart: data.items,
                totals: data.totals
            };

            localStorage.setItem('last_order', JSON.stringify({
                order_number: data.order_number,
                order_date: data.order_date,
                ...confirmationData
            }));

            showOrderConfirmation(confirmationData, data.order_number, data.order_date);

            if (placeOrderBtn) {
                placeOrderBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Order Placed';
            }
        })
        .catch(error => {
            console.error('Order placement failed:', error);
            alert(error.message || 'Sorry, something went wrong while placing your order.');
        })
        .finally(() => {
            if (placeOrderBtn) {
                setTimeout(() => resetPlaceOrderButton(placeOrderBtn), 1500);
            }
        });
}

function resetPlaceOrderButton(button) {
    button.disabled = false;
    button.classList.remove('opacity-70');
    button.innerHTML = '<i class="fas fa-lock mr-2"></i>Place Order';
}

// Function to generate order number
function generateOrderNumber() {
    return 'ORD-' + Date.now().toString().substr(-8).toUpperCase();
}

// Function to calculate delivery date (3-5 business days)
function calculateDeliveryDate() {
    const deliveryDate = new Date();
    deliveryDate.setDate(deliveryDate.getDate() + Math.floor(Math.random() * 3) + 3); // 3-5 days
    
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return deliveryDate.toLocaleDateString('en-US', options);
}

// Function to format payment method display
function formatPaymentMethod(method, cardNumber = null) {
    switch(method) {
        case 'credit-card':
            if (cardNumber) {
                const lastFour = cardNumber.replace(/\s/g, '').slice(-4);
                return `Credit Card ...... ${lastFour}`;
            }
            return 'Credit / Debit Card';
        case 'paypal':
            return 'PayPal';
        case 'cash-on-delivery':
            return 'Cash on Delivery';
        default:
            return method;
    }
}

// Function to show order confirmation modal
function showOrderConfirmation(orderData, orderNumber, orderDate = null) {
    // Set order number
    document.getElementById('confirmation-order-number').textContent = '#' + orderNumber;
    
    // Set order date
    const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
    const orderDateObj = orderDate ? new Date(orderDate) : new Date();
    document.getElementById('confirmation-order-date').textContent = orderDateObj.toLocaleDateString('en-US', dateOptions);
    
    // Set delivery date
    document.getElementById('confirmation-delivery-date').textContent = calculateDeliveryDate();
    
    // Display order items
    const itemsContainer = document.getElementById('confirmation-items');
    let itemsHtml = '';
    
    if (orderData.cart && orderData.cart.length > 0) {
        orderData.cart.forEach(item => {
            let price = 0;
            if (typeof item.unit_price === 'number') {
                price = item.unit_price;
            } else if (typeof item.price === 'number') {
                price = item.price;
            } else if (typeof item.price === 'string') {
                price = parseFloat(item.price.replace('$', '').replace(',', '').trim()) || 0;
            }
            const quantity = parseInt(item.quantity, 10) || 1;
            const itemTotal = price * quantity;
            
            itemsHtml += `
                <div class="flex items-center gap-4 pb-3 border-b border-gray-100">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                        ${item.image ? `<img src="${item.image}" alt="${item.name || 'Product'}" class="w-full h-full object-cover rounded-lg" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-box text-gray-400\\'></i>'">` : `<i class="fas fa-box text-gray-400"></i>`}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-800 truncate">${item.name || 'Product'}</h4>
                        <p class="text-xs text-gray-500">Quantity: ${quantity}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800">$${itemTotal.toFixed(2)}</p>
                    </div>
                </div>
            `;
        });
    }
    itemsContainer.innerHTML = itemsHtml;
    
    // Display totals
    if (orderData.totals) {
        document.getElementById('confirmation-subtotal').textContent = `$${orderData.totals.subtotal.toFixed(2)}`;
        document.getElementById('confirmation-shipping').textContent = `$${orderData.totals.shipping.toFixed(2)}`;
        document.getElementById('confirmation-tax').textContent = `$${orderData.totals.tax.toFixed(2)}`;
        document.getElementById('confirmation-total').textContent = `$${orderData.totals.total.toFixed(2)}`;
    }
    
    // Display shipping address
    const addressContainer = document.getElementById('confirmation-address');
    if (orderData.billing) {
        const shippingAddr = orderData.shipping || orderData.billing;
        addressContainer.innerHTML = `
            <p class="font-medium text-gray-800 mb-1">${orderData.billing.firstName} ${orderData.billing.lastName}</p>
            <p>${shippingAddr.address}</p>
            <p>${shippingAddr.city}, ${shippingAddr.state} ${shippingAddr.zip}</p>
            <p class="mt-2"><strong>Phone:</strong> ${orderData.billing.phone}</p>
            <p><strong>Email:</strong> ${orderData.billing.email}</p>
        `;
    }
    
    // Display payment method
    const paymentContainer = document.getElementById('confirmation-payment');
    if (orderData.payment) {
        paymentContainer.innerHTML = `
            <p class="font-medium text-gray-800">${formatPaymentMethod(orderData.payment.method, orderData.payment.cardNumber)}</p>
        `;
    }
    
    // Show modal
    const modal = document.getElementById('order-confirmation-modal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

// Function to close order confirmation modal
function closeOrderConfirmationModal(redirect = false, event = null) {
    if (event && event.target !== event.currentTarget) {
        return; // Don't close if clicking inside modal
    }
    
    const modal = document.getElementById('order-confirmation-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
    
    if (redirect) {
        // Redirect to home after a short delay
        setTimeout(() => {
            window.location.href = 'index.php?page=home';
        }, 300);
    }
}

// Function to print order confirmation
function printOrderConfirmation() {
    const modal = document.getElementById('order-confirmation-modal');
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Order Confirmation - ${document.getElementById('confirmation-order-number').textContent}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #10b981;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #10b981;
                    margin: 10px 0;
                }
                .order-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px;
                    padding: 15px;
                    background: #f9fafb;
                    border-radius: 8px;
                }
                .section {
                    margin-bottom: 25px;
                }
                .section h3 {
                    color: #10b981;
                    border-bottom: 2px solid #10b981;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th, table td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                table th {
                    background: #f9fafb;
                    font-weight: bold;
                }
                .total-row {
                    font-weight: bold;
                    font-size: 1.1em;
                }
                .address-box {
                    background: #f9fafb;
                    padding: 15px;
                    border-radius: 8px;
                    margin-top: 10px;
                }
                @media print {
                    body { margin: 0; padding: 15px; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Order Confirmation</h1>
                <p>${document.getElementById('confirmation-order-number').textContent}</p>
                <p>${document.getElementById('confirmation-order-date').textContent}</p>
            </div>
            
            <div class="order-info">
                <div>
                    <strong>Order Number:</strong> ${document.getElementById('confirmation-order-number').textContent}<br>
                    <strong>Order Date:</strong> ${document.getElementById('confirmation-order-date').textContent}
                </div>
                <div>
                    <strong>Estimated Delivery:</strong> ${document.getElementById('confirmation-delivery-date').textContent}
                </div>
            </div>
            
            <div class="section">
                <h3>Order Items</h3>
                ${document.getElementById('confirmation-items').innerHTML.replace(/<div/g, '<tr><td').replace(/<\/div>/g, '</td></tr>')}
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td style="text-align: right;">${document.getElementById('confirmation-subtotal').textContent}</td>
                    </tr>
                    <tr>
                        <td>Shipping</td>
                        <td style="text-align: right;">${document.getElementById('confirmation-shipping').textContent}</td>
                    </tr>
                    <tr>
                        <td>Tax</td>
                        <td style="text-align: right;">${document.getElementById('confirmation-tax').textContent}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td style="text-align: right; color: #10b981;">${document.getElementById('confirmation-total').textContent}</td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <h3>Shipping Address</h3>
                <div class="address-box">${document.getElementById('confirmation-address').innerHTML}</div>
            </div>
            
            <div class="section">
                <h3>Payment Method</h3>
                <div class="address-box">${document.getElementById('confirmation-payment').innerHTML}</div>
            </div>
            
            <div style="margin-top: 40px; text-align: center; color: #666; font-size: 0.9em;">
                <p>Thank you for your purchase!</p>
                <p>You will receive a confirmation email shortly.</p>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 250);
}
</script>
