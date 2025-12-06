<?php
include('config.php');
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$error = '';
if (isset($_SESSION['oauth_error'])) {
    $error = $_SESSION['oauth_error'];
    unset($_SESSION['oauth_error']);
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Mini Mart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body class="bg-gradient-to-br from-green-50 to-emerald-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="bg-green-600 rounded-lg p-4 w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-user text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">User Login</h1>
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

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with</span>
            </div>
        </div>

        <!-- Google Sign In Button -->
        <?php if (!empty($google_client_id)): ?>
            <div id="google-signin-button" class="w-full flex justify-center my-4"></div>
        <?php endif; ?>

        <div class="mt-6 text-center space-y-2">
            <p class="text-gray-600 text-sm">Don't have an account?</p>
            <a href="user-register.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                <i class="fas fa-user-plus mr-1"></i>Create Account
            </a>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="login.php" class="text-gray-600 hover:text-gray-700 text-sm">
                    <i class="fas fa-user-shield mr-1"></i>Admin Login
                </a>
            </div>
            <div class="mt-2">
                <a href="index.php" class="text-gray-600 hover:text-gray-700 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Store
                </a>
            </div>
        </div>
    </div>

    <script>
        function handleGoogleSignIn(response) {
            // Send the credential to the server
            if (response.credential) {
                // Redirect to Google auth handler with the credential
                window.location.href = 'google-auth.php?credential=' + encodeURIComponent(response.credential);
            }
        }

        // Wait for Google Identity Services library to load
        function initializeGoogleSignIn() {
            if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                google.accounts.id.initialize({
                    client_id: '<?php echo htmlspecialchars($google_client_id); ?>',
                    callback: handleGoogleSignIn
                });

                // Render the button if container exists
                var buttonContainer = document.getElementById('google-signin-button');
                if (buttonContainer) {
                    google.accounts.id.renderButton(buttonContainer, {
                        type: 'standard',
                        size: 'large',
                        theme: 'outline',
                        text: 'sign_in_with',
                        shape: 'rectangular',
                        logo_alignment: 'left'
                    });
                }
            } else {
                // Retry if library not loaded yet (max 50 attempts = 5 seconds)
                if (typeof retryCount === 'undefined') {
                    window.retryCount = 0;
                }
                if (window.retryCount < 50) {
                    window.retryCount++;
                    setTimeout(initializeGoogleSignIn, 100);
                } else {
                    console.error('Google Sign-In library failed to load');
                }
            }
        }

        // Start initialization
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeGoogleSignIn);
        } else {
            initializeGoogleSignIn();
        }
    </script>
</body>

</html>