<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Create categories table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

mysqli_query($conn, $createTable);

// Add image_path column if it doesn't exist
$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM categories LIKE 'image_path'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE categories ADD COLUMN image_path VARCHAR(500) NULL");
}

// Add vendor_id column if it doesn't exist
$checkColumns = mysqli_query($conn, "SHOW COLUMNS FROM categories LIKE 'vendor_id'");
if (mysqli_num_rows($checkColumns) == 0) {
    mysqli_query($conn, "ALTER TABLE categories ADD COLUMN vendor_id INT NULL");
}

$message = '';
$messageType = '';
$debugInfo = '';
$debugMsg = '';
$debugQuery = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Check what's being received (remove after fixing)
    if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
        $debugInfo = "<!-- DEBUG: POST method detected. Files array: " . (isset($_FILES['category_image']) ? 'YES' : 'NO') . " -->";
        if (isset($_FILES['category_image'])) {
            $debugInfo .= "<!-- DEBUG: File name: " . htmlspecialchars($_FILES['category_image']['name'] ?? 'empty') . ", Error: " . ($_FILES['category_image']['error'] ?? 'unknown') . ", Size: " . ($_FILES['category_image']['size'] ?? 0) . " bytes -->";
        } else {
            $debugInfo .= "<!-- DEBUG: No file in \$_FILES array -->";
        }
    }
    
    if (isset($_POST['add_category'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $vendor_id = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;
        
        $image_path = '';
        $uploadError = '';
        
        // Check if file was sent - simplified check
        $hasFile = false;
        $fileErrorCode = null;
        
        if (isset($_FILES['category_image'])) {
            $fileErrorCode = $_FILES['category_image']['error'] ?? null;
            $fileName = $_FILES['category_image']['name'] ?? '';
            $hasFile = !empty($fileName) && $fileErrorCode !== 4; // Error 4 = no file uploaded
            
            $debugMsg = "<!-- DEBUG: File check - isset: YES, name: '" . htmlspecialchars($fileName) . "', error code: " . ($fileErrorCode ?? 'N/A') . ", size: " . ($_FILES['category_image']['size'] ?? 0) . " bytes, hasFile: " . ($hasFile ? 'YES' : 'NO') . " -->";
        } else {
            $debugMsg = "<!-- DEBUG: \$_FILES['category_image'] is NOT set. POST method: " . ($_SERVER['REQUEST_METHOD'] == 'POST' ? 'YES' : 'NO') . " -->";
        }
        
        // Handle image upload
        if ($hasFile && $fileErrorCode == 0) {
            $uploadDir = 'image/categories/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $uploadError = 'Failed to create upload directory';
                }
            }
            
            if (file_exists($uploadDir) && is_writable($uploadDir)) {
                // We already checked error == 0, so proceed with upload
                $fileExtension = strtolower(pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $fileName = 'category_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $targetPath = $uploadDir . $fileName;
                    
                    $debugMsg .= "<!-- DEBUG: Attempting upload - Target: " . htmlspecialchars($targetPath) . ", Tmp: " . htmlspecialchars($_FILES['category_image']['tmp_name']) . " -->";
                    
                    if (move_uploaded_file($_FILES['category_image']['tmp_name'], $targetPath)) {
                        // Verify file was uploaded
                        if (file_exists($targetPath)) {
                            $image_path = $targetPath;
                            $debugMsg .= "<!-- DEBUG: Upload SUCCESS! Image path: " . htmlspecialchars($image_path) . " -->";
                        } else {
                            $uploadError = 'File upload failed - file not found after move. Target: ' . $targetPath;
                            $debugMsg .= "<!-- DEBUG: Upload FAILED - File not found after move -->";
                        }
                    } else {
                        $uploadError = 'Failed to move uploaded file. Tmp: ' . $_FILES['category_image']['tmp_name'] . ', Target: ' . $targetPath . '. Check permissions.';
                        $debugMsg .= "<!-- DEBUG: Upload FAILED - move_uploaded_file returned false -->";
                    }
                } else {
                    $uploadError = 'Invalid file format: ' . htmlspecialchars($fileExtension) . '. Allowed: ' . implode(', ', $allowedExtensions);
                    $debugMsg .= "<!-- DEBUG: Invalid file extension: " . htmlspecialchars($fileExtension) . " -->";
                }
            } else {
                $uploadError = 'Upload directory is not writable: ' . $uploadDir;
                $debugMsg .= "<!-- DEBUG: Directory not writable or doesn't exist -->";
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
                $debugMsg .= "<!-- DEBUG: File error code: " . $fileErrorCode . " -->";
            }
        }
        
        // Show upload error if any
        if ($uploadError && empty($image_path)) {
            $message = 'Category added but image upload failed: ' . $uploadError;
            $messageType = 'error';
        } elseif ($hasFile && empty($image_path) && empty($uploadError)) {
            // File was sent but nothing happened - this shouldn't happen
            $message = 'Category added but image upload failed: Unknown error. Check debug info in page source.';
            $messageType = 'error';
        }
        
        $imageValue = $image_path ? "'$image_path'" : 'NULL';
        $vendorValue = $vendor_id ? $vendor_id : 'NULL';
        
        // Debug output in query
        $debugQuery = "<!-- DEBUG: Image path: " . htmlspecialchars($image_path ?: 'NULL') . ", Image value: " . htmlspecialchars($imageValue) . " -->";
        
        $query = "INSERT INTO categories (name, description, image_path, vendor_id) VALUES ('$name', '$description', $imageValue, $vendorValue)";
        
        if (mysqli_query($conn, $query)) {
            if (empty($message)) {
                $message = 'Category added successfully' . ($image_path ? ' with image!' : ' (no image uploaded)');
                $messageType = 'success';
            }
            // Add debug info to message if no image
            if (empty($image_path) && $hasFile) {
                $message .= ' File was sent but upload failed. ' . ($uploadError ? 'Error: ' . $uploadError : 'No specific error.');
                $messageType = 'error';
            }
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_category'])) {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $vendor_id = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;
        
        // Get existing image
        $existing = mysqli_query($conn, "SELECT image_path FROM categories WHERE id=$id");
        $existingRow = mysqli_fetch_assoc($existing);
        $image_path = isset($existingRow['image_path']) ? $existingRow['image_path'] : '';
        $uploadError = '';
        
        // Handle image upload
        if (isset($_FILES['category_image']) && !empty($_FILES['category_image']['name'])) {
            $uploadDir = 'image/categories/';
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $uploadError = 'Failed to create upload directory';
                }
            }
            
            if (file_exists($uploadDir) && is_writable($uploadDir)) {
                $fileError = $_FILES['category_image']['error'];
                
                if ($fileError == 0) {
                    $fileExtension = strtolower(pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $fileName = 'category_' . time() . '_' . uniqid() . '.' . $fileExtension;
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['category_image']['tmp_name'], $targetPath)) {
                            // Verify file was uploaded
                            if (file_exists($targetPath)) {
                                // Delete old image
                                if ($image_path && file_exists($image_path)) {
                                    @unlink($image_path);
                                }
                                $image_path = $targetPath;
                                $message = 'Category updated successfully' . ($image_path ? ' with new image!' : '!');
                                $messageType = 'success';
                            } else {
                                $uploadError = 'File upload failed - file not found after move';
                            }
                        } else {
                            $uploadError = 'Failed to move uploaded file. Check permissions.';
                        }
                    } else {
                        $uploadError = 'Invalid file format. Allowed: ' . implode(', ', $allowedExtensions);
                    }
                } else {
                    $uploadErrors = [
                        1 => 'File exceeds upload_max_filesize',
                        2 => 'File exceeds MAX_FILE_SIZE',
                        3 => 'File partially uploaded',
                        4 => 'No file uploaded',
                        6 => 'Missing temporary folder',
                        7 => 'Failed to write file to disk',
                        8 => 'PHP extension stopped upload'
                    ];
                    $uploadError = isset($uploadErrors[$fileError]) ? $uploadErrors[$fileError] : 'Upload error: ' . $fileError;
                }
            } else {
                $uploadError = 'Upload directory is not writable: ' . $uploadDir;
            }
        }
        
        // Show upload error if any
        if ($uploadError && empty($image_path)) {
            $message = 'Category updated but image upload failed: ' . $uploadError;
            $messageType = 'error';
        }
        
        $imageValue = $image_path ? "'$image_path'" : 'NULL';
        $vendorValue = $vendor_id ? $vendor_id : 'NULL';
        $query = "UPDATE categories SET name='$name', description='$description', image_path=$imageValue, vendor_id=$vendorValue WHERE id=$id";
        
        if (mysqli_query($conn, $query)) {
            if (empty($message)) {
                $message = 'Category updated successfully!';
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
    $result = mysqli_query($conn, "SELECT image_path FROM categories WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    if ($row && $row['image_path'] && file_exists($row['image_path'])) {
        @unlink($row['image_path']);
    }
    
    $query = "DELETE FROM categories WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $message = 'Category deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Get category for edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM categories WHERE id=$id");
    $edit_category = mysqli_fetch_assoc($result);
}

// Fetch all categories with vendor names
$categories = mysqli_query($conn, "SELECT c.*, v.name as vendor_name 
                                    FROM categories c 
                                    LEFT JOIN vendors v ON c.vendor_id = v.id 
                                    ORDER BY c.id ASC");

// Fetch all vendors for dropdown
$vendors = mysqli_query($conn, "SELECT * FROM vendors ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - Mini Mart</title>
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
                <a href="admin-categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-500 text-white">
                    <i class="fas fa-tags w-5"></i>
                    <span>Categories</span>
                </a>
                <a href="admin-products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
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
                <h1 class="text-3xl font-bold text-white mb-2">Categories Management</h1>
                <p class="text-gray-400">Create and manage product categories</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-500 bg-opacity-20 border border-green-500' : 'bg-red-500 bg-opacity-20 border border-red-500'; ?>">
                <p class="text-white"><?php echo htmlspecialchars($message); ?></p>
            </div>
            <?php endif; ?>
            
            <?php 
            // Display debug info (temporary)
            if (isset($debugInfo)) echo $debugInfo;
            if (isset($debugMsg)) echo $debugMsg;
            if (isset($debugQuery)) echo $debugQuery;
            ?>

            <!-- Add/Edit Category Form -->
            <div class="card rounded-xl p-6 mb-8 max-w-2xl">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?>
                </h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Category Name *</label>
                        <input type="text" name="name" required 
                               value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>"
                               class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Vendor</label>
                        <select name="vendor_id" 
                                class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                            <option value="">-- Select Vendor --</option>
                            <?php 
                            mysqli_data_seek($vendors, 0);
                            while ($vendor = mysqli_fetch_assoc($vendors)): 
                            ?>
                                <option value="<?php echo $vendor['id']; ?>" 
                                        <?php echo ($edit_category && isset($edit_category['vendor_id']) && $edit_category['vendor_id'] == $vendor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vendor['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <p class="text-gray-400 text-xs mt-1">Select a vendor for this category</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Category Image <?php echo $edit_category ? '' : '*'; ?></label>
                        <input type="file" name="category_image" accept="image/*" <?php echo $edit_category ? '' : 'required'; ?>
                               class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-500 file:text-white hover:file:bg-green-600">
                        <?php if ($edit_category && $edit_category['image_path']): ?>
                            <p class="text-gray-400 text-sm mt-2">Current image: 
                                <a href="<?php echo htmlspecialchars($edit_category['image_path']); ?>" target="_blank" class="text-green-400 hover:underline">
                                    <?php echo basename($edit_category['image_path']); ?>
                                </a>
                            </p>
                            <img src="<?php echo htmlspecialchars($edit_category['image_path']); ?>" 
                                 alt="Current category image" 
                                 class="mt-2 w-32 h-32 object-cover rounded-lg border border-slate-600">
                            <p class="text-gray-400 text-xs mt-1">Leave empty to keep current image</p>
                        <?php endif; ?>
                        <p class="text-gray-400 text-sm mt-1">Supported formats: JPG, JPEG, PNG, WEBP, GIF (Max 2MB)</p>
                        <?php if (!$edit_category): ?>
                            <p class="text-yellow-400 text-xs mt-1">⚠️ Make sure to select an image file before submitting</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-4">
                        <?php if ($edit_category): ?>
                            <button type="submit" name="update_category" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-save"></i> Update Category
                            </button>
                            <a href="admin-categories.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors font-semibold flex items-center">
                                Cancel
                            </a>
                        <?php else: ?>
                            <button type="submit" name="add_category" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-6">All Categories</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-600">
                                <th class="text-left py-3 px-4 text-gray-300">ID</th>
                                <th class="text-left py-3 px-4 text-gray-300">Image</th>
                                <th class="text-left py-3 px-4 text-gray-300">Name</th>
                                <th class="text-left py-3 px-4 text-gray-300">Vendor</th>
                                <th class="text-left py-3 px-4 text-gray-300">Description</th>
                                <th class="text-left py-3 px-4 text-gray-300">Created</th>
                                <th class="text-left py-3 px-4 text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($categories) > 0): ?>
                                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                <tr class="border-b border-slate-700 hover:bg-slate-700">
                                    <td class="py-3 px-4"><?php echo $category['id']; ?></td>
                                    <td class="py-3 px-4">
                                        <?php if ($category['image_path'] && file_exists($category['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($category['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($category['name']); ?>"
                                                 class="w-16 h-16 object-cover rounded-lg">
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-gray-600 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400 text-xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td class="py-3 px-4 text-gray-400">
                                        <?php echo $category['vendor_name'] ? htmlspecialchars($category['vendor_name']) : '-'; ?>
                                    </td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo htmlspecialchars(substr($category['description'], 0, 50)) . (strlen($category['description']) > 50 ? '...' : ''); ?></td>
                                    <td class="py-3 px-4 text-gray-400 text-sm"><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="?edit=<?php echo $category['id']; ?>" class="bg-transparent hover:bg-blue-500/20 text-blue-600 px-3 py-1 rounded text-sm transition-colors font-medium">
                                                Edit
                                            </a>
                                            <button type="button" 
                                               onclick="showDeleteModal(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>')"
                                               class="bg-transparent hover:bg-red-500/20 text-red-600 px-3 py-1 rounded text-sm transition-colors font-medium">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">No categories found. Add your first category above.</td>
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
                <h3 class="text-xl font-bold text-white">Delete Category</h3>
            </div>
            <p class="text-gray-300 mb-2">
                Are you sure you want to delete the category <span class="font-semibold text-white" id="categoryName"></span>?
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
        let deleteCategoryId = null;

        function showDeleteModal(id, name) {
            deleteCategoryId = id;
            document.getElementById('categoryName').textContent = '"' + name + '"';
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteCategoryId = null;
        }

        function confirmDelete() {
            if (deleteCategoryId) {
                window.location.href = '?delete=' + deleteCategoryId;
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

