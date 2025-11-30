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

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == 0) {
        $uploadDir = 'image/hero/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            $fileName = 'hero_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetPath)) {
                // Delete old image if exists
                $oldImage = mysqli_query($conn, "SELECT image_path FROM hero_section LIMIT 1");
                if ($oldRow = mysqli_fetch_assoc($oldImage)) {
                    if ($oldRow['image_path'] && file_exists($oldRow['image_path'])) {
                        unlink($oldRow['image_path']);
                    }
                }
                $image_path = $targetPath;
            }
        }
    } else {
        // Keep existing image if no new image uploaded
        $existing = mysqli_query($conn, "SELECT image_path FROM hero_section LIMIT 1");
        if ($existingRow = mysqli_fetch_assoc($existing)) {
            $image_path = $existingRow['image_path'];
        }
    }
    
    // Update hero section
    if ($image_path) {
        $updateQuery = "UPDATE hero_section SET 
                        title_line1 = '$title_line1',
                        title_line2 = '$title_line2',
                        title_color = '$title_color',
                        subtitle = '$subtitle',
                        description = '$description',
                        image_path = '$image_path',
                        button_text = '$button_text',
                        button_link = '$button_link',
                        background_color = '$background_color'
                        WHERE id = 1";
    } else {
        $updateQuery = "UPDATE hero_section SET 
                        title_line1 = '$title_line1',
                        title_line2 = '$title_line2',
                        title_color = '$title_color',
                        subtitle = '$subtitle',
                        description = '$description',
                        button_text = '$button_text',
                        button_link = '$button_link',
                        background_color = '$background_color'
                        WHERE id = 1";
    }
    
    if (mysqli_query($conn, $updateQuery)) {
        $message = 'Hero section updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error updating hero section: ' . mysqli_error($conn);
        $messageType = 'error';
    }
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
    <title>Manage Hero Section - Admin</title>
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
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-white mb-2">Manage Hero Section</h1>
                <p class="text-gray-400 mb-8">Update the hero section content and image for the home page</p>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="card rounded-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-6">Text Content</h2>
                        
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
                                    <option value="text-blue-600" <?php echo $hero['title_color'] == 'text-blue-600' ? 'selected' : ''; ?>>Blue</option>
                                    <option value="text-red-600" <?php echo $hero['title_color'] == 'text-red-600' ? 'selected' : ''; ?>>Red</option>
                                    <option value="text-orange-600" <?php echo $hero['title_color'] == 'text-orange-600' ? 'selected' : ''; ?>>Orange</option>
                                    <option value="text-purple-600" <?php echo $hero['title_color'] == 'text-purple-600' ? 'selected' : ''; ?>>Purple</option>
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
                                    <option value="from-green-50 to-green-100" <?php echo $hero['background_color'] == 'from-green-50 to-green-100' ? 'selected' : ''; ?>>Green Gradient</option>
                                    <option value="from-blue-50 to-blue-100" <?php echo $hero['background_color'] == 'from-blue-50 to-blue-100' ? 'selected' : ''; ?>>Blue Gradient</option>
                                    <option value="from-purple-50 to-purple-100" <?php echo $hero['background_color'] == 'from-purple-50 to-purple-100' ? 'selected' : ''; ?>>Purple Gradient</option>
                                    <option value="from-orange-50 to-orange-100" <?php echo $hero['background_color'] == 'from-orange-50 to-orange-100' ? 'selected' : ''; ?>>Orange Gradient</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card rounded-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-6">Hero Image</h2>
                        
                        <?php if ($hero['image_path'] && file_exists($hero['image_path'])): ?>
                            <div class="mb-4">
                                <label class="block text-gray-300 text-sm font-medium mb-2">Current Image</label>
                                <img src="<?php echo htmlspecialchars($hero['image_path']); ?>" alt="Current Hero Image" 
                                     class="w-full max-w-md h-64 object-cover rounded-lg border border-slate-600">
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Upload New Image</label>
                            <input type="file" name="hero_image" accept="image/*" 
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                            <p class="text-gray-400 text-xs mt-2">Recommended size: 1200x600px. Supported formats: JPG, PNG, WEBP, GIF</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg transition-colors font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Save Changes</span>
                        </button>
                        <a href="admin.php" class="px-6 py-3 border border-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-semibold flex items-center justify-center gap-2">
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

