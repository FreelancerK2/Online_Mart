<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Create vendors table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(50),
    type_of_product VARCHAR(255),
    location VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

mysqli_query($conn, $createTable);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_vendor'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
        $type_of_product = mysqli_real_escape_string($conn, $_POST['type_of_product']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        
        $query = "INSERT INTO vendors (name, email, phone_number, type_of_product, location) 
                  VALUES ('$name', '$email', '$phone_number', '$type_of_product', '$location')";
        
        if (mysqli_query($conn, $query)) {
            $message = 'Vendor added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_vendor'])) {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
        $type_of_product = mysqli_real_escape_string($conn, $_POST['type_of_product']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        
        $query = "UPDATE vendors SET 
                  name='$name', 
                  email='$email', 
                  phone_number='$phone_number',
                  type_of_product='$type_of_product',
                  location='$location'
                  WHERE id=$id";
        
        if (mysqli_query($conn, $query)) {
            $message = 'Vendor updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM vendors WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $message = 'Vendor deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Get vendor for edit
$edit_vendor = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM vendors WHERE id=$id");
    $edit_vendor = mysqli_fetch_assoc($result);
}

// Fetch all vendors
$vendors = mysqli_query($conn, "SELECT * FROM vendors ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors Management - Mini Mart</title>
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
                <a href="admin-products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white transition-colors">
                    <i class="fas fa-box w-5"></i>
                    <span>Products</span>
                </a>
                <a href="admin-vendors.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-500 text-white">
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
                <h1 class="text-3xl font-bold text-white mb-2">Vendors Management</h1>
                <p class="text-gray-400">Create and manage vendors</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-500 bg-opacity-20 border border-green-500' : 'bg-red-500 bg-opacity-20 border border-red-500'; ?>">
                <p class="text-white"><?php echo htmlspecialchars($message); ?></p>
            </div>
            <?php endif; ?>

            <!-- Add/Edit Vendor Form -->
            <div class="card rounded-xl p-6 mb-8 max-w-2xl">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <?php echo $edit_vendor ? 'Edit Vendor' : 'Add New Vendor'; ?>
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if ($edit_vendor): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_vendor['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-2">Name *</label>
                            <input type="text" name="name" required 
                                   value="<?php echo $edit_vendor ? htmlspecialchars($edit_vendor['name']) : ''; ?>"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-300 mb-2">Email *</label>
                            <input type="email" name="email" required 
                                   value="<?php echo $edit_vendor ? htmlspecialchars($edit_vendor['email']) : ''; ?>"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-300 mb-2">Phone Number</label>
                            <input type="tel" name="phone_number" 
                                   value="<?php echo $edit_vendor ? htmlspecialchars($edit_vendor['phone_number']) : ''; ?>"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-300 mb-2">Type of Product</label>
                            <input type="text" name="type_of_product" 
                                   value="<?php echo $edit_vendor ? htmlspecialchars($edit_vendor['type_of_product']) : ''; ?>"
                                   placeholder="e.g., Fresh Fruits, Vegetables, Dairy"
                                   class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-300 mb-2">Location</label>
                        <textarea name="location" rows="3"
                                  placeholder="Enter vendor location/address"
                                  class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-green-500"><?php echo $edit_vendor ? htmlspecialchars($edit_vendor['location']) : ''; ?></textarea>
                    </div>
                    
                    <div class="flex gap-4">
                        <?php if ($edit_vendor): ?>
                            <button type="submit" name="update_vendor" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-save"></i> Update Vendor
                            </button>
                            <a href="admin-vendors.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors font-semibold flex items-center">
                                Cancel
                            </a>
                        <?php else: ?>
                            <button type="submit" name="add_vendor" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-plus"></i> Add Vendor
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Vendors List -->
            <div class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-6">All Vendors</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-600">
                                <th class="text-left py-3 px-4 text-gray-300">ID</th>
                                <th class="text-left py-3 px-4 text-gray-300">Name</th>
                                <th class="text-left py-3 px-4 text-gray-300">Email</th>
                                <th class="text-left py-3 px-4 text-gray-300">Phone Number</th>
                                <th class="text-left py-3 px-4 text-gray-300">Type of Product</th>
                                <th class="text-left py-3 px-4 text-gray-300">Location</th>
                                <th class="text-left py-3 px-4 text-gray-300">Created</th>
                                <th class="text-left py-3 px-4 text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($vendors) > 0): ?>
                                <?php 
                                mysqli_data_seek($vendors, 0);
                                while ($vendor = mysqli_fetch_assoc($vendors)): 
                                ?>
                                <tr class="border-b border-slate-700 hover:bg-slate-700">
                                    <td class="py-3 px-4"><?php echo $vendor['id']; ?></td>
                                    <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($vendor['name']); ?></td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo htmlspecialchars($vendor['email']); ?></td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo $vendor['phone_number'] ? htmlspecialchars($vendor['phone_number']) : '-'; ?></td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo $vendor['type_of_product'] ? htmlspecialchars($vendor['type_of_product']) : '-'; ?></td>
                                    <td class="py-3 px-4 text-gray-400"><?php echo $vendor['location'] ? htmlspecialchars($vendor['location']) : '-'; ?></td>
                                    <td class="py-3 px-4 text-gray-400 text-sm"><?php echo date('M d, Y', strtotime($vendor['created_at'])); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="?edit=<?php echo $vendor['id']; ?>" class="bg-transparent hover:bg-blue-500/20 text-blue-600 px-3 py-1 rounded text-sm transition-colors font-medium">
                                                Edit
                                            </a>
                                            <button type="button" 
                                               onclick="showDeleteModal(<?php echo $vendor['id']; ?>, '<?php echo addslashes($vendor['name']); ?>')"
                                               class="bg-transparent hover:bg-red-500/20 text-red-600 px-3 py-1 rounded text-sm transition-colors font-medium">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400">No vendors found. Add your first vendor above.</td>
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
                <h3 class="text-xl font-bold text-white">Delete Vendor</h3>
            </div>
            <p class="text-gray-300 mb-2">
                Are you sure you want to delete the vendor <span class="font-semibold text-white" id="vendorName"></span>?
            </p>
            <p class="text-gray-400 text-sm mb-6">
                This action cannot be undone.
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
        let deleteVendorId = null;

        function showDeleteModal(id, name) {
            deleteVendorId = id;
            document.getElementById('vendorName').textContent = '"' + name + '"';
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            deleteVendorId = null;
        }

        function confirmDelete() {
            if (deleteVendorId) {
                window.location.href = '?delete=' + deleteVendorId;
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

