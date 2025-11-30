<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Create hero_section table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS hero_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_line1 VARCHAR(255) NOT NULL,
    title_line2 VARCHAR(255) NOT NULL,
    title_color VARCHAR(50) DEFAULT 'text-green-600',
    subtitle TEXT,
    description TEXT,
    image_path VARCHAR(500),
    button_text VARCHAR(100) DEFAULT 'Shop Now',
    button_link VARCHAR(255) DEFAULT '#',
    background_color VARCHAR(50) DEFAULT 'from-green-50 to-green-100',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

mysqli_query($conn, $createTable);

// Initialize default hero data if table is empty
$checkData = mysqli_query($conn, "SELECT COUNT(*) as count FROM hero_section");
$row = mysqli_fetch_assoc($checkData);
if ($row['count'] == 0) {
    mysqli_query($conn, "INSERT INTO hero_section (title_line1, title_line2, subtitle, description, button_text, button_link) 
                         VALUES ('Fresh Vegetables', 'Big discount', 'Save up to 50% off on your first order', '', 'Shop Now', '#')");
}

// Handle hero section form submission
$message = '';
$messageType = '';

// Get message from session if exists (for persistence after redirect)
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    $messageType = $_SESSION['admin_message_type'];
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_hero'])) {
    $title_line1 = mysqli_real_escape_string($conn, $_POST['title_line1']);
    $title_line2 = mysqli_real_escape_string($conn, $_POST['title_line2']);
    $title_color = mysqli_real_escape_string($conn, $_POST['title_color']);
    $subtitle = mysqli_real_escape_string($conn, $_POST['subtitle']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $button_text = mysqli_real_escape_string($conn, $_POST['button_text']);
    $button_link = mysqli_real_escape_string($conn, $_POST['button_link']);
    $background_color = mysqli_real_escape_string($conn, $_POST['background_color']);
    
    // Handle image upload
    $image_path = '';
    
    // Keep existing image if no new image uploaded
    $existing = mysqli_query($conn, "SELECT image_path FROM hero_section LIMIT 1");
    if ($existingRow = mysqli_fetch_assoc($existing)) {
        $image_path = $existingRow['image_path'];
    }
    
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == 0 && $_FILES['hero_image']['size'] > 0) {
        $uploadDir = 'image/hero/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $message = 'Error: Could not create upload directory: ' . $uploadDir;
                $messageType = 'error';
            }
        }
        
        // Check if directory is writable
        if (file_exists($uploadDir) && is_writable($uploadDir)) {
            $fileExtension = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
            // Support all common image formats
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg', 'ico', 'tiff', 'tif', 'heic', 'heif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = 'hero_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetPath)) {
                    // Verify file was uploaded
                    if (file_exists($targetPath)) {
                        // Delete old image if exists and different
                        if ($image_path && file_exists($image_path) && $image_path != $targetPath) {
                            @unlink($image_path);
                        }
                        $image_path = $targetPath;
                        $message = 'Image uploaded successfully! File: ' . basename($targetPath);
                        $messageType = 'success';
                    } else {
                        $message = 'Error: File upload failed. File does not exist after upload.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Error: Failed to move uploaded file. Temp file: ' . $_FILES['hero_image']['tmp_name'] . ', Target: ' . $targetPath . '. Check file permissions.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Error: Invalid file format (' . $fileExtension . '). Supported formats: JPG, JPEG, PNG, WEBP, GIF, BMP, SVG, ICO, TIFF.';
                $messageType = 'error';
            }
        } elseif (!is_writable($uploadDir)) {
            $message = 'Error: Upload directory is not writable: ' . $uploadDir . '. Please check permissions.';
            $messageType = 'error';
        }
    } elseif (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] != 0 && $_FILES['hero_image']['error'] != 4) {
        // Handle upload errors (but ignore error code 4 = "No file was uploaded" - this is normal when just updating other fields)
        $uploadErrors = [
            1 => 'The uploaded file exceeds the upload_max_filesize directive.',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
            3 => 'The uploaded file was only partially uploaded.',
            6 => 'Missing a temporary folder.',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.'
        ];
        $errorMsg = isset($uploadErrors[$_FILES['hero_image']['error']]) ? $uploadErrors[$_FILES['hero_image']['error']] : 'Unknown upload error.';
        $message = 'Error: ' . $errorMsg;
        $messageType = 'error';
    }
    // If error code is 4 (no file uploaded) or no file field, we silently keep the existing image
    
    // Update hero section (always include image_path if it exists, otherwise keep existing)
    $imagePathUpdate = $image_path ? "image_path = '$image_path'," : "";
    $updateQuery = "UPDATE hero_section SET 
                    title_line1 = '$title_line1',
                    title_line2 = '$title_line2',
                    title_color = '$title_color',
                    subtitle = '$subtitle',
                    description = '$description',
                    $imagePathUpdate
                    button_text = '$button_text',
                    button_link = '$button_link',
                    background_color = '$background_color'
                    WHERE id = 1";
    
    // Update database
    if (mysqli_query($conn, $updateQuery)) {
        // If no message was set from image upload, set success message
        if (empty($message)) {
            $message = 'Hero section updated successfully!';
            $messageType = 'success';
        } else if (strpos($message, 'Image uploaded') !== false) {
            // If image was uploaded, combine messages
            $message = 'Hero section updated successfully! ' . $message;
        }
        // Refresh hero data to show new image
        $heroData = mysqli_query($conn, "SELECT * FROM hero_section LIMIT 1");
        $hero = mysqli_fetch_assoc($heroData);
    } else {
        // Database update failed
        if (empty($message)) {
            $message = 'Error updating hero section: ' . mysqli_error($conn);
            $messageType = 'error';
        } else {
            // Image upload succeeded but database update failed
            $message = 'Image uploaded but database update failed: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
    
    // Always store message in session for display after page reload (even if empty, to ensure redirect happens)
    if (!empty($message)) {
        $_SESSION['admin_message'] = $message;
        $_SESSION['admin_message_type'] = $messageType;
    } else {
        // Default success message if somehow message is still empty
        $_SESSION['admin_message'] = 'Hero section updated successfully!';
        $_SESSION['admin_message_type'] = 'success';
    }
    // Redirect to clear POST data and show message
    header("Location: admin-home.php");
    exit;
}

// Get current hero data
$heroData = mysqli_query($conn, "SELECT * FROM hero_section LIMIT 1");
$hero = mysqli_fetch_assoc($heroData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Home Page - Admin</title>
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
                <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-slate-700 transition-colors">
                    <i class="fas fa-arrow-left w-5"></i>
                    <span>Back to Dashboard</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Manage Home Page</h1>
                        <p class="text-gray-400">Edit sections and content for the home page</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="index.php?page=home" target="_blank" class="px-4 py-2 border border-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-eye"></i>
                        <span>Preview</span>
                    </a>
                    </div>
                </div>

                <!-- Toast Notification Container (hidden by default, shown via JavaScript) -->
                <div id="toast-notification" class="fixed top-4 right-4 z-[99999] transform translate-x-full transition-transform duration-300 ease-in-out" style="display: none;">
                    <div id="toast-content" class="flex items-center gap-4 px-6 py-4 rounded-lg shadow-2xl min-w-[320px] max-w-[500px]">
                        <div id="toast-icon" class="flex-shrink-0 text-2xl"></div>
                        <div class="flex-1">
                            <p id="toast-message" class="text-white font-semibold"></p>
            </div>
                        <button onclick="closeToast()" class="text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
        </div>
    </div>

                <!-- Hero Section Management -->
                <div class="card rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-image text-green-500"></i>
                            Hero Section
                        </h2>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6" id="heroForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column - Text Content -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Title Line 1</label>
                                    <input type="text" name="title_line1" value="<?php echo htmlspecialchars($hero['title_line1']); ?>" 
                                           class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500" required>
                                </div>

                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Title Line 2</label>
                                    <input type="text" name="title_line2" value="<?php echo htmlspecialchars($hero['title_line2']); ?>" 
                                           class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500" required>
                                </div>

                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Title Line 2 Color</label>
                                    <select name="title_color" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                        <option value="text-green-600" <?php echo $hero['title_color'] == 'text-green-600' ? 'selected' : ''; ?>>Green</option>
                                        <option value="text-green-700" <?php echo $hero['title_color'] == 'text-green-700' ? 'selected' : ''; ?>>Green Deep</option>
                                        <option value="text-emerald-600" <?php echo $hero['title_color'] == 'text-emerald-600' ? 'selected' : ''; ?>>Emerald</option>
                                        <option value="text-blue-600" <?php echo $hero['title_color'] == 'text-blue-600' ? 'selected' : ''; ?>>Blue</option>
                                        <option value="text-blue-700" <?php echo $hero['title_color'] == 'text-blue-700' ? 'selected' : ''; ?>>Blue Deep</option>
                                        <option value="text-cyan-600" <?php echo $hero['title_color'] == 'text-cyan-600' ? 'selected' : ''; ?>>Cyan</option>
                                        <option value="text-indigo-600" <?php echo $hero['title_color'] == 'text-indigo-600' ? 'selected' : ''; ?>>Indigo</option>
                                        <option value="text-red-600" <?php echo $hero['title_color'] == 'text-red-600' ? 'selected' : ''; ?>>Red</option>
                                        <option value="text-red-700" <?php echo $hero['title_color'] == 'text-red-700' ? 'selected' : ''; ?>>Red Deep</option>
                                        <option value="text-rose-600" <?php echo $hero['title_color'] == 'text-rose-600' ? 'selected' : ''; ?>>Rose</option>
                                        <option value="text-orange-600" <?php echo $hero['title_color'] == 'text-orange-600' ? 'selected' : ''; ?>>Orange</option>
                                        <option value="text-orange-700" <?php echo $hero['title_color'] == 'text-orange-700' ? 'selected' : ''; ?>>Orange Deep</option>
                                        <option value="text-amber-600" <?php echo $hero['title_color'] == 'text-amber-600' ? 'selected' : ''; ?>>Amber</option>
                                        <option value="text-purple-600" <?php echo $hero['title_color'] == 'text-purple-600' ? 'selected' : ''; ?>>Purple</option>
                                        <option value="text-purple-700" <?php echo $hero['title_color'] == 'text-purple-700' ? 'selected' : ''; ?>>Purple Deep</option>
                                        <option value="text-violet-600" <?php echo $hero['title_color'] == 'text-violet-600' ? 'selected' : ''; ?>>Violet</option>
                                        <option value="text-pink-600" <?php echo $hero['title_color'] == 'text-pink-600' ? 'selected' : ''; ?>>Pink</option>
                                        <option value="text-pink-700" <?php echo $hero['title_color'] == 'text-pink-700' ? 'selected' : ''; ?>>Pink Deep</option>
                                        <option value="text-yellow-600" <?php echo $hero['title_color'] == 'text-yellow-600' ? 'selected' : ''; ?>>Yellow</option>
                                        <option value="text-teal-600" <?php echo $hero['title_color'] == 'text-teal-600' ? 'selected' : ''; ?>>Teal</option>
                                        <option value="text-gray-800" <?php echo $hero['title_color'] == 'text-gray-800' ? 'selected' : ''; ?>>Dark Gray</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Subtitle</label>
                                    <input type="text" name="subtitle" value="<?php echo htmlspecialchars($hero['subtitle']); ?>" 
                                           class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Description</label>
                                    <textarea name="description" rows="3" 
                                              class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500"><?php echo htmlspecialchars($hero['description']); ?></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Button Text</label>
                                        <input type="text" name="button_text" value="<?php echo htmlspecialchars($hero['button_text']); ?>" 
                                               class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Button Link</label>
                                        <input type="text" name="button_link" value="<?php echo htmlspecialchars($hero['button_link']); ?>" 
                                               class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Background Gradient</label>
                                    <select name="background_color" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                                        <optgroup label="Light Gradients">
                                            <option value="from-green-50 to-green-100" <?php echo $hero['background_color'] == 'from-green-50 to-green-100' ? 'selected' : ''; ?>>Green Gradient (Light)</option>
                                            <option value="from-blue-50 to-blue-100" <?php echo $hero['background_color'] == 'from-blue-50 to-blue-100' ? 'selected' : ''; ?>>Blue Gradient (Light)</option>
                                            <option value="from-purple-50 to-purple-100" <?php echo $hero['background_color'] == 'from-purple-50 to-purple-100' ? 'selected' : ''; ?>>Purple Gradient (Light)</option>
                                            <option value="from-orange-50 to-orange-100" <?php echo $hero['background_color'] == 'from-orange-50 to-orange-100' ? 'selected' : ''; ?>>Orange Gradient (Light)</option>
                                            <option value="from-pink-50 to-pink-100" <?php echo $hero['background_color'] == 'from-pink-50 to-pink-100' ? 'selected' : ''; ?>>Pink Gradient (Light)</option>
                                            <option value="from-cyan-50 to-cyan-100" <?php echo $hero['background_color'] == 'from-cyan-50 to-cyan-100' ? 'selected' : ''; ?>>Cyan Gradient (Light)</option>
                                        </optgroup>
                                        <optgroup label="Deep Gradients">
                                            <option value="from-green-400 to-emerald-600" <?php echo $hero['background_color'] == 'from-green-400 to-emerald-600' ? 'selected' : ''; ?>>Green Deep Gradient</option>
                                            <option value="from-green-500 to-green-700" <?php echo $hero['background_color'] == 'from-green-500 to-green-700' ? 'selected' : ''; ?>>Green Rich Gradient</option>
                                            <option value="from-blue-400 to-blue-600" <?php echo $hero['background_color'] == 'from-blue-400 to-blue-600' ? 'selected' : ''; ?>>Blue Deep Gradient</option>
                                            <option value="from-blue-500 to-indigo-700" <?php echo $hero['background_color'] == 'from-blue-500 to-indigo-700' ? 'selected' : ''; ?>>Blue Rich Gradient</option>
                                            <option value="from-purple-400 to-purple-600" <?php echo $hero['background_color'] == 'from-purple-400 to-purple-600' ? 'selected' : ''; ?>>Purple Deep Gradient</option>
                                            <option value="from-purple-500 to-violet-700" <?php echo $hero['background_color'] == 'from-purple-500 to-violet-700' ? 'selected' : ''; ?>>Purple Rich Gradient</option>
                                            <option value="from-orange-400 to-orange-600" <?php echo $hero['background_color'] == 'from-orange-400 to-orange-600' ? 'selected' : ''; ?>>Orange Deep Gradient</option>
                                            <option value="from-orange-500 to-amber-700" <?php echo $hero['background_color'] == 'from-orange-500 to-amber-700' ? 'selected' : ''; ?>>Orange Rich Gradient</option>
                                            <option value="from-red-400 to-rose-600" <?php echo $hero['background_color'] == 'from-red-400 to-rose-600' ? 'selected' : ''; ?>>Red Deep Gradient</option>
                                            <option value="from-pink-400 to-pink-600" <?php echo $hero['background_color'] == 'from-pink-400 to-pink-600' ? 'selected' : ''; ?>>Pink Deep Gradient</option>
                                            <option value="from-cyan-400 to-teal-600" <?php echo $hero['background_color'] == 'from-cyan-400 to-teal-600' ? 'selected' : ''; ?>>Cyan Deep Gradient</option>
                                            <option value="from-indigo-400 to-indigo-600" <?php echo $hero['background_color'] == 'from-indigo-400 to-indigo-600' ? 'selected' : ''; ?>>Indigo Deep Gradient</option>
                                        </optgroup>
                                        <optgroup label="Vibrant Gradients">
                                            <option value="from-emerald-400 via-green-500 to-emerald-600" <?php echo $hero['background_color'] == 'from-emerald-400 via-green-500 to-emerald-600' ? 'selected' : ''; ?>>Emerald Vibrant</option>
                                            <option value="from-blue-500 via-cyan-500 to-blue-600" <?php echo $hero['background_color'] == 'from-blue-500 via-cyan-500 to-blue-600' ? 'selected' : ''; ?>>Blue Vibrant</option>
                                            <option value="from-purple-500 via-pink-500 to-purple-600" <?php echo $hero['background_color'] == 'from-purple-500 via-pink-500 to-purple-600' ? 'selected' : ''; ?>>Purple Pink Vibrant</option>
                                            <option value="from-orange-500 via-red-500 to-orange-600" <?php echo $hero['background_color'] == 'from-orange-500 via-red-500 to-orange-600' ? 'selected' : ''; ?>>Orange Red Vibrant</option>
                                            <option value="from-teal-500 via-cyan-500 to-teal-600" <?php echo $hero['background_color'] == 'from-teal-500 via-cyan-500 to-teal-600' ? 'selected' : ''; ?>>Teal Vibrant</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column - Image Upload -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-300 text-sm font-medium mb-2">Hero Image</label>
                                    <?php 
                                    // Check if image exists
                                    $currentImagePath = isset($hero['image_path']) ? $hero['image_path'] : '';
                                    $imageExists = false;
                                    if ($currentImagePath) {
                                        if (file_exists($currentImagePath)) {
                                            $imageExists = true;
                                        } elseif (file_exists(__DIR__ . '/' . $currentImagePath)) {
                                            $imageExists = true;
                                        }
                                    }
                                    ?>
                                    <?php if ($imageExists && $currentImagePath): ?>
                                        <div class="mb-4">
                                            <label class="block text-gray-300 text-sm font-medium mb-2">Current Image</label>
                                            <img src="<?php echo htmlspecialchars($hero['image_path']); ?>" alt="Current Hero Image" 
                                                 class="w-full h-64 object-cover rounded-lg border border-slate-600"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div style="display:none;" class="p-4 bg-slate-700 rounded-lg border border-slate-600">
                                                <p class="text-red-400 text-sm">Image not found at: <?php echo htmlspecialchars($hero['image_path']); ?></p>
                                            </div>
                                            <p class="text-gray-400 text-xs mt-2">Path: <?php echo htmlspecialchars($hero['image_path']); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-4 p-4 bg-slate-700 rounded-lg border border-slate-600">
                                            <p class="text-gray-400 text-sm">No image uploaded yet.</p>
                                            <?php if ($currentImagePath): ?>
                                                <p class="text-red-400 text-xs mt-1">Path in DB: <?php echo htmlspecialchars($currentImagePath); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="hero_image" accept="image/*" 
                                           class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-500 file:text-white hover:file:bg-green-600">
                                    <p class="text-gray-400 text-xs mt-2">Recommended size: 1200x600px. Supported formats: JPG, JPEG, PNG, WEBP, GIF, BMP, SVG, ICO, TIFF</p>
                                    <p class="text-gray-500 text-xs mt-1">
                                        Upload directory: <code class="bg-slate-800 px-2 py-1 rounded text-green-400">image/hero/</code>
                                        <?php 
                                        $uploadDir = 'image/hero/';
                                        if (file_exists($uploadDir)) {
                                            echo '<span class="text-green-400 ml-2">✓ Directory exists</span>';
                                            if (is_writable($uploadDir)) {
                                                echo ' <span class="text-green-400">✓ Writable</span>';
                                            } else {
                                                echo ' <span class="text-red-400">✗ Not writable (chmod 755 recommended)</span>';
                                            }
                                        } else {
                                            echo '<span class="text-yellow-400 ml-2">⚠ Will be created on upload</span>';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4 border-t border-slate-700">
                            <button type="submit" name="save_hero" class="bg-green-500 hover:bg-green-600 text-white py-3 px-6 rounded-lg transition-colors font-semibold flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Save Hero Section</span>
                            </button>
                    </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <!-- Toast Notification Script -->
    <script>
        <?php if ($message && $message != ''): ?>
        // Show toast notification on page load
        (function() {
            function showToastOnLoad() {
                const message = <?php echo json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                const messageType = <?php echo json_encode($messageType, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                
                if (message && message.trim() !== '') {
                    // Small delay to ensure DOM is ready
                    setTimeout(function() {
                        showToast(message, messageType);
                    }, 100);
                }
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showToastOnLoad);
            } else {
                showToastOnLoad();
            }
        })();
        <?php endif; ?>

        function showToast(message, type) {
            const toast = document.getElementById('toast-notification');
            const toastContent = document.getElementById('toast-content');
            const toastIcon = document.getElementById('toast-icon');
            const toastMessage = document.getElementById('toast-message');
            
            if (!toast || !toastContent || !toastIcon || !toastMessage) {
                console.error('Toast elements not found');
                return;
            }
            
            // Set colors and icon based on type
            if (type === 'success') {
                toastContent.className = 'flex items-center gap-4 px-6 py-4 rounded-lg shadow-2xl min-w-[320px] max-w-[500px] bg-gradient-to-r from-green-500 to-emerald-600';
                toastIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
            } else {
                toastContent.className = 'flex items-center gap-4 px-6 py-4 rounded-lg shadow-2xl min-w-[320px] max-w-[500px] bg-gradient-to-r from-red-500 to-rose-600';
                toastIcon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
            }
            
            toastMessage.textContent = message;
            toast.style.display = 'block';
            
            // Force reflow to ensure display is applied
            toast.offsetHeight;
            
            // Slide in animation
            setTimeout(function() {
                toast.classList.remove('translate-x-full');
                toast.classList.add('translate-x-0');
            }, 10);
            
            // Auto close after 5 seconds
            setTimeout(function() {
                closeToast();
            }, 5000);
        }
        
        function closeToast() {
            const toast = document.getElementById('toast-notification');
            if (!toast) return;
            
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
            setTimeout(function() {
                toast.style.display = 'none';
            }, 300);
        }
    </script>
</body>
</html>
