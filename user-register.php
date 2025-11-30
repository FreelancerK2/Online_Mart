<?php
include('config.php');
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id']) || isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username or email already exists!";
        } else {
            // Hash password
            $hashed_password = md5($password);
            
            // Insert user
            $insert_query = "INSERT INTO users (username, email, password, first_name, last_name) 
                            VALUES ('$username', '$email', '$hashed_password', '$first_name', '$last_name')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - Mini Mart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="bg-green-600 rounded-lg p-4 w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h1>
            <p class="text-gray-600">Sign up to start shopping</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" placeholder="First Name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" placeholder="Last Name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2 text-green-600"></i>Username
                </label>
                <input type="text" name="username" placeholder="Choose a username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-green-600"></i>Email
                </label>
                <input type="email" name="email" placeholder="Enter your email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-green-600"></i>Password
                </label>
                <input type="password" name="password" placeholder="Enter password (min 6 characters)" required minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-green-600"></i>Confirm Password
                </label>
                <input type="password" name="confirm_password" placeholder="Confirm password" required minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <button type="submit" name="register" 
                class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-100 flex items-center justify-center gap-2">
                <i class="fas fa-user-plus"></i>
                <span>Create Account</span>
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <p class="text-gray-600 text-sm">Already have an account?</p>
            <a href="login.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                <i class="fas fa-sign-in-alt mr-1"></i>Login here
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

