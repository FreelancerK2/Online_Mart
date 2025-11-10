<?php
include('config.php');
session_start();

// If already logged in, redirect appropriately
if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    // First check if it's an admin
    $admin_query = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $admin_result = mysqli_query($conn, $admin_query);

    if (mysqli_num_rows($admin_result) == 1) {
        // Admin login
        $_SESSION['admin'] = $username;
        header("Location: admin.php");
        exit;
    } else {
        // Check if it's a regular user
        $user_query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $user_result = mysqli_query($conn, $user_query);

        if (mysqli_num_rows($user_result) == 1) {
            // Regular user login
            $user = mysqli_fetch_assoc($user_result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_first_name'] = $user['first_name'];
            $_SESSION['user_last_name'] = $user['last_name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mini Mart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="mx-auto mb-4 flex items-center justify-center">
                <img src="image/logo1.jpg" alt="Mini Mart Logo" class="h-20 w-auto object-contain">
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Login</h1>
            <p class="text-gray-600">Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2 text-green-600"></i>Username
                </label>
                <input type="text" name="username" placeholder="Enter your username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-green-600"></i>Password
                </label>
                <input type="password" name="password" placeholder="Enter your password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <button type="submit" name="login" 
                class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-100 flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <p class="text-gray-600 text-sm">Don't have an account?</p>
            <a href="user-register.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                <i class="fas fa-user-plus mr-1"></i>Create Account
            </a>
            <div class="mt-4">
                <a href="index.php" class="text-gray-600 hover:text-gray-700 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Store
                </a>
            </div>
        </div>
    </div>
</body>
</html>
