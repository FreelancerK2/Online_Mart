<?php
include('config.php');
session_start();

// Google OAuth callback handler using Google Sign-In with ID tokens
if (isset($_GET['credential'])) {
    $credential = $_GET['credential'];

    // Decode the JWT token (simplified - in production, verify the signature)
    $parts = explode('.', $credential);
    if (count($parts) === 3) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

        if ($payload && isset($payload['sub']) && isset($payload['email'])) {
            $google_id = mysqli_real_escape_string($conn, $payload['sub']);
            $email = mysqli_real_escape_string($conn, $payload['email']);
            $first_name = isset($payload['given_name']) ? mysqli_real_escape_string($conn, $payload['given_name']) : '';
            $last_name = isset($payload['family_name']) ? mysqli_real_escape_string($conn, $payload['family_name']) : '';
            $name = isset($payload['name']) ? mysqli_real_escape_string($conn, $payload['name']) : ($first_name . ' ' . $last_name);

            // Check if user exists with this Google ID or email
            $check_query = "SELECT * FROM users WHERE google_id='$google_id' OR email='$email'";
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) > 0) {
                // User exists, log them in
                $user = mysqli_fetch_assoc($check_result);

                // Update Google ID if not set
                if (empty($user['google_id'])) {
                    $update_query = "UPDATE users SET google_id='$google_id' WHERE id=" . $user['id'];
                    mysqli_query($conn, $update_query);
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];

                header("Location: index.php");
                exit;
            } else {
                // Create new user
                // Generate username from email
                $username = explode('@', $email)[0];
                $base_username = $username;
                $counter = 1;

                // Ensure username is unique
                while (true) {
                    $username_check = "SELECT * FROM users WHERE username='$username'";
                    $username_result = mysqli_query($conn, $username_check);
                    if (mysqli_num_rows($username_result) == 0) {
                        break;
                    }
                    $username = $base_username . $counter;
                    $counter++;
                }

                // Insert new user
                $insert_query = "INSERT INTO users (username, email, google_id, first_name, last_name, password) 
                                VALUES ('$username', '$email', '$google_id', '$first_name', '$last_name', '')";

                if (mysqli_query($conn, $insert_query)) {
                    $user_id = mysqli_insert_id($conn);

                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_first_name'] = $first_name;
                    $_SESSION['user_last_name'] = $last_name;

                    header("Location: index.php");
                    exit;
                } else {
                    $_SESSION['oauth_error'] = "Failed to create account. Please try again.";
                    header("Location: login.php");
                    exit;
                }
            }
        } else {
            $_SESSION['oauth_error'] = "Failed to retrieve user information from Google.";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['oauth_error'] = "Invalid Google credential.";
        header("Location: login.php");
        exit;
    }
} else {
    // No credential, redirect to login
    header("Location: login.php");
    exit;
}
