<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Create/update products table to include category_id, description, and image_path
$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'category_id'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN category_id INT NULL");
}

$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'description'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN description TEXT NULL");
}

$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'image_path'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN image_path VARCHAR(500) NULL");
}

$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'discount_percentage'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0");
}

$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'discount_price'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN discount_price DECIMAL(10,2) NULL");
}

$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'vendor_name'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN vendor_name VARCHAR(255) NULL");
}

// Add vendor_id column if it doesn't exist
$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'vendor_id'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN vendor_id INT NULL");
}

$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'promotion_status'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN promotion_status VARCHAR(50) NULL");
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $discount_percentage = isset($_POST['discount_percentage']) ? floatval($_POST['discount_percentage']) : 0;
        $vendor_id = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;
        // Get vendor name from vendor_id for backward compatibility
        $vendor_name = '';
        if ($vendor_id) {
            $vendorQuery = mysqli_query($conn, "SELECT name FROM vendors WHERE id = $vendor_id");
            if ($vendorRow = mysqli_fetch_assoc($vendorQuery)) {
                $vendor_name = mysqli_real_escape_string($conn, $vendorRow['name']);
            }
        }
        // Handle multiple promotion statuses (checkboxes)
        $promotion_status = null;
        if (isset($_POST['promotion_status']) && is_array($_POST['promotion_status']) && !empty($_POST['promotion_status'])) {
            $promotion_status = implode(',', array_map(function($val) use ($conn) {
                return mysqli_real_escape_string($conn, $val);
            }, $_POST['promotion_status']));
        }
        
        // Calculate discount price
        $discount_price = null;
        if ($discount_percentage > 0 && $discount_percentage <= 100) {
            $discount_amount = ($price * $discount_percentage) / 100;
            $discount_price = $price - $discount_amount;
        }
        
        $image_path = '';
        $uploadError = '';
        
        // Check if file was sent
        $hasFile = false;
        $fileErrorCode = null;
        
        if (isset($_FILES['product_image'])) {
            $fileErrorCode = $_FILES['product_image']['error'] ?? null;
            $fileName = $_FILES['product_image']['name'] ?? '';
            $hasFile = !empty($fileName) && $fileErrorCode !== 4; // Error 4 = no file uploaded
        }
        
        // Handle image upload
        if ($hasFile && $fileErrorCode == 0) {
            $uploadDir = 'image/products/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $uploadError = 'Failed to create upload directory';
                }
            }
            
            if (file_exists($uploadDir) && is_writable($uploadDir)) {
                $fileExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $fileName = 'product_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
                        // Verify file was uploaded
                        if (file_exists($targetPath)) {
                            $image_path = $targetPath;
                        } else {
                            $uploadError = 'File upload failed - file not found after move. Target: ' . $targetPath;
                        }
                    } else {
                        $uploadError = 'Failed to move uploaded file. Check permissions.';
                    }
                } else {
                    $uploadError = 'Invalid file format: ' . htmlspecialchars($fileExtension) . '. Allowed: ' . implode(', ', $allowedExtensions);
                }
            } else {
                $uploadError = 'Upload directory is not writable: ' . $uploadDir;
            }
        } else {
            if ($hasFile && $fileErrorCode != 0) {
                $uploadErrors = [
                    1 => 'File exceeds upload_max_filesize (2MB)',
                    2 => 'File exceeds MAX_FILE_SIZE',
                    3 => 'File partially uploaded',
                    4 => 'No file uploaded',
                    6 => 'Missing temporary folder',
                    7 => 'Failed to write file to disk',
                    8 => 'PHP extension stopped upload'
                ];
                $uploadError = isset($uploadErrors[$fileErrorCode]) ? $uploadErrors[$fileErrorCode] : 'Upload error code: ' . $fileErrorCode;
            }
        }
        
        $categoryValue = $category_id ? $category_id : 'NULL';
        $imageValue = $image_path ? "'$image_path'" : 'NULL';
        $discountPriceValue = $discount_price !== null ? $discount_price : 'NULL';
        $vendorValue = $vendor_name ? "'$vendor_name'" : 'NULL';
        $vendorIdValue = $vendor_id ? $vendor_id : 'NULL';
        $promotionValue = $promotion_status ? "'$promotion_status'" : 'NULL';
        
        $query = "INSERT INTO products (name, price, quantity, category_id, description, image_path, discount_percentage, discount_price, vendor_name, vendor_id, promotion_status) 
                  VALUES ('$name', $price, $quantity, $categoryValue, '$description', $imageValue, $discount_percentage, $discountPriceValue, $vendorValue, $vendorIdValue, $promotionValue)";
        
        if (mysqli_query($conn, $query)) {
            if ($uploadError && empty($image_path)) {
                $message = 'Product added but image upload failed: ' . $uploadError;
                $messageType = 'error';
            } elseif ($hasFile && empty($image_path) && empty($uploadError)) {
                $message = 'Product added but image upload failed: Unknown error.';
                $messageType = 'error';
            } else {
                $message = 'Product added successfully' . ($image_path ? ' with image!' : ' (no image uploaded)');
                $messageType = 'success';
            }
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $discount_percentage = isset($_POST['discount_percentage']) ? floatval($_POST['discount_percentage']) : 0;
        $vendor_id = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;
        // Get vendor name from vendor_id for backward compatibility
        $vendor_name = '';
        if ($vendor_id) {
            $vendorQuery = mysqli_query($conn, "SELECT name FROM vendors WHERE id = $vendor_id");
            if ($vendorRow = mysqli_fetch_assoc($vendorQuery)) {
                $vendor_name = mysqli_real_escape_string($conn, $vendorRow['name']);
            }
        }
        // Handle multiple promotion statuses (checkboxes)
        $promotion_status = null;
        if (isset($_POST['promotion_status']) && is_array($_POST['promotion_status']) && !empty($_POST['promotion_status'])) {
            $promotion_status = implode(',', array_map(function($val) use ($conn) {
                return mysqli_real_escape_string($conn, $val);
            }, $_POST['promotion_status']));
        }
        
        // Calculate discount price
        $discount_price = null;
        if ($discount_percentage > 0 && $discount_percentage <= 100) {
            $discount_amount = ($price * $discount_percentage) / 100;
            $discount_price = $price - $discount_amount;
        }
        
        // Get existing image
        $existing = mysqli_query($conn, "SELECT image_path FROM products WHERE id=$id");
        $existingRow = mysqli_fetch_assoc($existing);
        $image_path = $existingRow['image_path'];
        $uploadError = '';
        
        // Check if file was sent
        $hasFile = false;
        $fileErrorCode = null;
        
        if (isset($_FILES['product_image'])) {
            $fileErrorCode = $_FILES['product_image']['error'] ?? null;
            $fileName = $_FILES['product_image']['name'] ?? '';
            $hasFile = !empty($fileName) && $fileErrorCode !== 4; // Error 4 = no file uploaded
        }
        
        // Handle image upload
        if ($hasFile && $fileErrorCode == 0) {
            $uploadDir = 'image/products/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $uploadError = 'Failed to create upload directory';
                }
            }
            
            if (file_exists($uploadDir) && is_writable($uploadDir)) {
                $fileExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $fileName = 'product_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
                        // Verify file was uploaded
                        if (file_exists($targetPath)) {
                            // Delete old image
                            if ($image_path && file_exists($image_path)) {
                                @unlink($image_path);
                            }
                            $image_path = $targetPath;
                        } else {
                            $uploadError = 'File upload failed - file not found after move. Target: ' . $targetPath;
                        }
                    } else {
                        $uploadError = 'Failed to move uploaded file. Check permissions.';
                    }
                } else {
                    $uploadError = 'Invalid file format: ' . htmlspecialchars($fileExtension) . '. Allowed: ' . implode(', ', $allowedExtensions);
                }
            } else {
                $uploadError = 'Upload directory is not writable: ' . $uploadDir;
            }
        } else {
            if ($hasFile && $fileErrorCode != 0) {
                $uploadErrors = [
                    1 => 'File exceeds upload_max_filesize (2MB)',
                    2 => 'File exceeds MAX_FILE_SIZE',
                    3 => 'File partially uploaded',
                    4 => 'No file uploaded',
                    6 => 'Missing temporary folder',
                    7 => 'Failed to write file to disk',
                    8 => 'PHP extension stopped upload'
                ];
                $uploadError = isset($uploadErrors[$fileErrorCode]) ? $uploadErrors[$fileErrorCode] : 'Upload error code: ' . $fileErrorCode;
            }
        }
        
        $categoryValue = $category_id ? $category_id : 'NULL';
        $imageValue = $image_path ? "'$image_path'" : 'NULL';
        $discountPriceValue = $discount_price !== null ? $discount_price : 'NULL';
        $vendorValue = $vendor_name ? "'$vendor_name'" : 'NULL';
        $vendorIdValue = $vendor_id ? $vendor_id : 'NULL';
        $promotionValue = $promotion_status ? "'$promotion_status'" : 'NULL';
        
        $query = "UPDATE products SET 
                  name='$name', 
                  price=$price, 
                  quantity=$quantity, 
                  category_id=$categoryValue,
                  description='$description',
                  image_path=$imageValue,
                  discount_percentage=$discount_percentage,
                  discount_price=$discountPriceValue,
                  vendor_name=$vendorValue,
                  vendor_id=$vendorIdValue,
                  promotion_status=$promotionValue
                  WHERE id=$id";
        
        if (mysqli_query($conn, $query)) {
            if ($uploadError && $hasFile) {
                $message = 'Product updated but image upload failed: ' . $uploadError;
                $messageType = 'error';
            } elseif ($hasFile && !$image_path && empty($uploadError)) {
                $message = 'Product updated but image upload failed: Unknown error.';
                $messageType = 'error';
            } else {
                $message = 'Product updated successfully' . ($hasFile && $image_path ? ' with new image!' : '');
                $messageType = 'success';
            }
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get image path before deleting
    $result = mysqli_query($conn, "SELECT image_path FROM products WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    if ($row && $row['image_path'] && file_exists($row['image_path'])) {
        @unlink($row['image_path']);
    }
    
    $query = "DELETE FROM products WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $message = 'Product deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    $edit_product = mysqli_fetch_assoc($result);
}

// Fetch all products with category names
$products = mysqli_query($conn, "SELECT p.*, c.name as category_name 
                                  FROM products p 
                                  LEFT JOIN categories c ON p.category_id = c.id 
                                  ORDER BY p.id ASC");

// Fetch all categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// Fetch all vendors for dropdown
$vendors = mysqli_query($conn, "SELECT * FROM vendors ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Mini Mart</title>
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
    </style>
</head>
<body class="min-h-screen text-white">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-64 min-h-screen fixed left-0 top-0 p-6">
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
            </div>

            <nav class="space-y-2">
                <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-tags w-5"></i>
                    <span>Categories</span>
                </a>
                <a href="admin-products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-500 text-white">
                    <i class="fas fa-box w-5"></i>
                    <span>Products</span>
                </a>
                <a href="admin-vendors.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-truck w-5"></i>
                    <span>Vendors</span>
                </a>
                <a href="admin-home.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-home w-5"></i>
                    <span>Home Page</span>
                </a>
                <a href="admin-hot-deals.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-fire w-5"></i>
                    <span>Hot Deals</span>
                </a>
                <a href="admin-shop.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-shopping-bag w-5"></i>
                    <span>Shop Page</span>
                </a>
            </nav>

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
                    <a href="admin.php?logout=1" class="w-full border border-red-500 text-red-500 hover:text-red-600 hover:bg-red-500/10 py-2 rounded-lg transition-colors font-medium flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Products Management</h1>
                <p class="text-gray-400">Create and manage products</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-500 bg-opacity-20 border border-green-500' : 'bg-red-500 bg-opacity-20 border border-red-500'; ?>">
                <p class="text-white"><?php echo htmlspecialchars($message); ?></p>
            </div>
            <?php endif; ?>

            <!-- Add/Edit Product Form -->
            <div class="card rounded-xl p-6 mb-8 max-w-2xl">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                </h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-2">Product Name *</label>
                            <input type="text" name="name" required 
                                   value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-300 mb-2">Category</label>
                            <select name="category_id" 
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                <option value="">-- Select Category --</option>
                                <?php 
                                mysqli_data_seek($categories, 0);
                                while ($cat = mysqli_fetch_assoc($categories)): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($edit_product && $edit_product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-2">Price ($) *</label>
                            <input type="number" name="price" id="price" step="0.01" min="0" required 
                                   value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>"
                                   oninput="calculateDiscountPrice()"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-300 mb-2">Quantity *</label>
                            <input type="number" name="quantity" min="0" required 
                                   value="<?php echo $edit_product ? $edit_product['quantity'] : ''; ?>"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-2">Discount Percentage (%)</label>
                            <input type="number" name="discount_percentage" id="discount_percentage" step="0.01" min="0" max="100" 
                                   value="<?php echo $edit_product ? ($edit_product['discount_percentage'] ?? 0) : '0'; ?>"
                                   oninput="calculateDiscountPrice()"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                            <p class="text-gray-400 text-xs mt-1">Enter discount percentage (0-100)</p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-300 mb-2">Discount Price ($)</label>
                            <input type="number" name="discount_price" id="discount_price" step="0.01" min="0" readonly
                                   value="<?php echo $edit_product ? ($edit_product['discount_price'] ?? '') : ''; ?>"
                                   class="w-full bg-slate-600 border border-slate-500 rounded-lg px-4 py-2 text-gray-300 cursor-not-allowed">
                            <p class="text-gray-400 text-xs mt-1">Calculated automatically</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-2">Vendor Name</label>
                            <select name="vendor_id" 
                                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                <option value="">-- Select Vendor --</option>
                                <?php 
                                mysqli_data_seek($vendors, 0);
                                while ($vendor = mysqli_fetch_assoc($vendors)): 
                                ?>
                                    <option value="<?php echo $vendor['id']; ?>" 
                                            <?php echo ($edit_product && isset($edit_product['vendor_id']) && $edit_product['vendor_id'] == $vendor['id']) ? 'selected' : (($edit_product && isset($edit_product['vendor_name']) && $edit_product['vendor_name'] == $vendor['name']) ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($vendor['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <p class="text-gray-400 text-xs mt-1">Select a vendor from the list</p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-300 mb-2">Promotion Status</label>
                            <div class="bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 space-y-3">
                                <?php 
                                $selectedPromotions = [];
                                if ($edit_product && isset($edit_product['promotion_status']) && !empty($edit_product['promotion_status'])) {
                                    $selectedPromotions = explode(',', $edit_product['promotion_status']);
                                }
                                ?>
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-slate-600 rounded-lg p-2 -mx-2 transition-colors">
                                    <input type="checkbox" name="promotion_status[]" value="hot_deals" 
                                           <?php echo in_array('hot_deals', $selectedPromotions) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-green-500 bg-slate-600 border-slate-500 rounded focus:ring-green-500 focus:ring-2">
                                    <span class="text-white flex items-center gap-2">
                                        <i class="fas fa-fire text-red-500"></i>
                                        Hot Deals
                                    </span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-slate-600 rounded-lg p-2 -mx-2 transition-colors">
                                    <input type="checkbox" name="promotion_status[]" value="popular" 
                                           <?php echo in_array('popular', $selectedPromotions) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-green-500 bg-slate-600 border-slate-500 rounded focus:ring-green-500 focus:ring-2">
                                    <span class="text-white flex items-center gap-2">
                                        <i class="fas fa-star text-blue-500"></i>
                                        Popular
                                    </span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-slate-600 rounded-lg p-2 -mx-2 transition-colors">
                                    <input type="checkbox" name="promotion_status[]" value="new_arrival" 
                                           <?php echo in_array('new_arrival', $selectedPromotions) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-green-500 bg-slate-600 border-slate-500 rounded focus:ring-green-500 focus:ring-2">
                                    <span class="text-white flex items-center gap-2">
                                        <i class="fas fa-sparkles text-green-500"></i>
                                        New Arrival
                                    </span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer hover:bg-slate-600 rounded-lg p-2 -mx-2 transition-colors">
                                    <input type="checkbox" name="promotion_status[]" value="best_sellers" 
                                           <?php echo in_array('best_sellers', $selectedPromotions) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-green-500 bg-slate-600 border-slate-500 rounded focus:ring-green-500 focus:ring-2">
                                    <span class="text-white flex items-center gap-2">
                                        <i class="fas fa-trophy text-yellow-500"></i>
                                        Best Sellers
                                    </span>
                                </label>
                            </div>
                            <p class="text-gray-400 text-xs mt-1">Select one or more promotion statuses for this product</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Product Image <?php echo $edit_product ? '' : '*'; ?></label>
                        <input type="file" name="product_image" accept="image/*" <?php echo $edit_product ? '' : 'required'; ?>
                               class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-500 file:text-white hover:file:bg-green-600">
                        <?php if ($edit_product && $edit_product['image_path']): ?>
                            <p class="text-gray-400 text-sm mt-2">Current image: 
                                <a href="<?php echo htmlspecialchars($edit_product['image_path']); ?>" target="_blank" class="text-green-400 hover:underline">
                                    <?php echo basename($edit_product['image_path']); ?>
                                </a>
                            </p>
                            <img src="<?php echo htmlspecialchars($edit_product['image_path']); ?>" 
                                 alt="Current product image" 
                                 class="mt-2 w-32 h-32 object-cover rounded-lg border border-slate-600">
                            <p class="text-gray-400 text-xs mt-1">Leave empty to keep current image</p>
                        <?php endif; ?>
                        <p class="text-gray-400 text-sm mt-1">Supported formats: JPG, JPEG, PNG, WEBP, GIF (Max 2MB)</p>
                        <?php if (!$edit_product): ?>
                            <p class="text-yellow-400 text-xs mt-1">⚠️ Make sure to select an image file before submitting</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-4">
                        <?php if ($edit_product): ?>
                            <button type="submit" name="update_product" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="admin-products.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors font-semibold flex items-center">
                                Cancel
                            </a>
                        <?php else: ?>
                            <button type="submit" name="add_product" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-plus"></i> Add Product
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Products List -->
            <div class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-6">All Products</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-600">
                                <th class="text-left py-3 px-4 text-gray-300">Image</th>
                                <th class="text-left py-3 px-4 text-gray-300">Name</th>
                                <th class="text-left py-3 px-4 text-gray-300">Category</th>
                                <th class="text-left py-3 px-4 text-gray-300">Price</th>
                                <th class="text-left py-3 px-4 text-gray-300">Discount</th>
                                <th class="text-left py-3 px-4 text-gray-300">Vendor</th>
                                <th class="text-left py-3 px-4 text-gray-300">Promotion</th>
                                <th class="text-left py-3 px-4 text-gray-300">Quantity</th>
                                <th class="text-left py-3 px-4 text-gray-300">Created</th>
                                <th class="text-left py-3 px-4 text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($products) > 0): ?>
                                <?php 
                                mysqli_data_seek($products, 0);
                                while ($product = mysqli_fetch_assoc($products)): 
                                ?>
                                <tr class="border-b border-slate-700 hover:bg-slate-700">
                                    <td class="py-3 px-4">
                                        <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="w-16 h-16 object-cover rounded-lg">
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-slate-600 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td class="py-3 px-4 text-gray-400">
                                        <?php echo $product['category_name'] ? htmlspecialchars($product['category_name']) : 'Uncategorized'; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-green-400 font-semibold">$<?php echo number_format($product['price'], 2); ?></div>
                                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                            <div class="text-red-400 text-sm line-through">$<?php echo number_format($product['price'], 2); ?></div>
                                            <div class="text-green-400 font-semibold">$<?php echo number_format($product['discount_price'], 2); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($product['discount_percentage'] && $product['discount_percentage'] > 0): ?>
                                            <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                                -<?php echo number_format($product['discount_percentage'], 1); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500 text-xs">No discount</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-gray-400">
                                        <?php echo $product['vendor_name'] ? htmlspecialchars($product['vendor_name']) : '-'; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $promotionStatus = $product['promotion_status'] ?? '';
                                        if (!empty($promotionStatus)) {
                                            $promotions = explode(',', $promotionStatus);
                                            $promotionLabels = [
                                                'hot_deals' => ['label' => 'Hot Deals', 'color' => 'bg-red-500', 'icon' => 'fa-fire'],
                                                'popular' => ['label' => 'Popular', 'color' => 'bg-blue-500', 'icon' => 'fa-star'],
                                                'new_arrival' => ['label' => 'New Arrival', 'color' => 'bg-green-500', 'icon' => 'fa-sparkles'],
                                                'best_sellers' => ['label' => 'Best Sellers', 'color' => 'bg-yellow-500', 'icon' => 'fa-trophy']
                                            ];
                                            foreach ($promotions as $promo) {
                                                $promo = trim($promo);
                                                if (isset($promotionLabels[$promo])) {
                                                    $label = $promotionLabels[$promo];
                                                    echo '<span class="' . $label['color'] . ' text-white px-2 py-1 rounded text-xs font-semibold mr-1 mb-1 inline-block">
                                                        <i class="fas ' . $label['icon'] . '"></i> ' . $label['label'] . '
                                                    </span>';
                                                }
                                            }
                                        } else {
                                            echo '<span class="text-gray-500 text-xs">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="py-3 px-4"><?php echo $product['quantity']; ?></td>
                                    <td class="py-3 px-4 text-gray-400 text-sm"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="?edit=<?php echo $product['id']; ?>" class="bg-transparent hover:bg-blue-500/20 text-blue-600 px-3 py-1 rounded text-sm transition-colors font-medium">
                                                Edit
                                            </a>
                                            <button type="button" 
                                               onclick="showDeleteModal(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')"
                                               class="bg-transparent hover:bg-red-500/20 text-red-600 px-3 py-1 rounded text-sm transition-colors font-medium">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-gray-400">No products found. Add your first product above.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4 border border-slate-700 shadow-2xl">
            <div class="flex items-center mb-4">
                <div class="bg-red-500 bg-opacity-0 rounded-full p-2 mr-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Delete Product</h3>
            </div>
            <p class="text-gray-300 mb-2">
                Are you sure you want to delete the product <span class="font-semibold text-white" id="productName"></span>?
            </p>
            <p class="text-gray-400 text-sm mb-6">
                This action cannot be undone and will also delete the associated image file.
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button" 
                        onclick="hideDeleteModal()"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" 
                        onclick="confirmDelete()"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors font-medium">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        // Calculate discount price automatically
        function calculateDiscountPrice() {
            const priceInput = document.getElementById('price');
            const discountInput = document.getElementById('discount_percentage');
            const discountPriceInput = document.getElementById('discount_price');
            
            if (!priceInput || !discountInput || !discountPriceInput) {
                return;
            }
            
            const price = parseFloat(priceInput.value) || 0;
            const discountPercentage = parseFloat(discountInput.value) || 0;
            
            if (price > 0 && discountPercentage > 0 && discountPercentage <= 100) {
                const discountAmount = (price * discountPercentage) / 100;
                const discountPrice = price - discountAmount;
                discountPriceInput.value = discountPrice.toFixed(2);
            } else {
                discountPriceInput.value = '';
            }
        }
        
        // Initialize discount price calculation on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateDiscountPrice();
        });
        
        let deleteProductId = null;

        function showDeleteModal(id, name) {
            deleteProductId = id;
            document.getElementById('productName').textContent = '"' + name + '"';
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteProductId = null;
        }

        function confirmDelete() {
            if (deleteProductId) {
                window.location.href = '?delete=' + deleteProductId;
            }
        }

        // Close modal on background click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDeleteModal();
            }
        });
    </script>
</body>
</html>

