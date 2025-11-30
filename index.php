<?php
session_start();
// Allow public access to homepage, all-products, shop, about, contact, hot-deals, and checkout
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if ($page !== 'home' && $page !== 'all-products' && $page !== 'shop' && $page !== 'about' && $page !== 'contact' && $page !== 'hot-deals' && $page !== 'product-detail' && $page !== 'checkout' && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" style="overflow-y: auto; height: auto;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Mart - Online Grocery Store</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50" style="overflow-x: hidden; overflow-y: auto; height: auto;">
    <main class="bg-white rounded-b-[6rem]">
        <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';

            switch ($page) {
                case "home":
                    include "home.php";
                    break;
                case "all-products":
                    include "all-products.php";
                    break;
                case "product":
                    include "product.php";
                    break;
                case "product-detail":
                    include "product-detail.php";
                    break;
                case "contact":
                    include "contact.php";  
                    break;
                case "about":
                    include "about.php";  
                    break;
                case "shop":
                    include "shop.php";
                    break;
                case "hot-deals":
                    include "hot-deals.php";
                    break;
                case "checkout":
                    include "checkout.php";
                    break;
                default:
                    echo "<section class='not-found'><h2>404 - Page not found</h2></section>";
            }
        ?>
    </main>

    <footer>
        <?php include("footer.php"); ?>
    </footer>
</body>
</html>
