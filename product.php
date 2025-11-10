<?php
include('config.php');

// --- ADD PRODUCT ---
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $query = "INSERT INTO products (name, price, quantity) VALUES ('$name', '$price', '$quantity')";
    mysqli_query($conn, $query);
    header("Location: index.php");
    exit;
}

// --- DELETE PRODUCT ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: product.php");
    exit;
}

// --- FETCH PRODUCT FOR EDIT ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($result);
}

// --- UPDATE PRODUCT ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    mysqli_query($conn, "UPDATE products SET name='$name', price='$price', quantity='$quantity' WHERE id=$id");
    header("Location: product.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛍️ Product Management</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f5f6fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        section {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2d3436;
        }

        /* Form Section */
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-container h3 {
            margin-bottom: 15px;
            color: #0984e3;
        }

        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        input[type="text"], input[type="number"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #0984e3;
        }

        button, .btn-cancel {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            background: #0984e3;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            text-align: center;
        }

        button:hover, .btn-cancel:hover {
            background: #74b9ff;
        }

        .btn-cancel {
            background: #d63031;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card h3 {
            color: #2d3436;
            margin-bottom: 10px;
        }

        .product-card p {
            margin: 5px 0;
            color: #636e72;
        }

        .product-card small {
            display: block;
            margin-bottom: 10px;
            color: #b2bec3;
        }

        .btn-edit, .btn-delete {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-edit {
            background: #00b894;
        }

        .btn-delete {
            background: #d63031;
        }

        .btn-edit:hover {
            background: #55efc4;
        }

        .btn-delete:hover {
            background: #ff7675;
        }

        /* Responsive */
        @media (max-width: 600px) {
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<section>
    <h2>🛒 Manage Products</h2>

    <!-- ADD / EDIT FORM -->
    <div class="form-container">
        <h3><?php echo isset($edit_data) ? '✏️ Edit Product' : '➕ Add New Product'; ?></h3>
        <form method="post">
            <?php if (isset($edit_data)) { ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <?php } ?>

            <input type="text" name="name" placeholder="Product Name" required 
                   value="<?php echo isset($edit_data) ? $edit_data['name'] : ''; ?>">

            <input type="number" step="0.01" name="price" placeholder="Price ($)" required 
                   value="<?php echo isset($edit_data) ? $edit_data['price'] : ''; ?>">

            <input type="number" name="quantity" placeholder="Quantity" required 
                   value="<?php echo isset($edit_data) ? $edit_data['quantity'] : ''; ?>">

            <?php if (isset($edit_data)) { ?>
                <button type="submit" name="update">💾 Update</button>
                <a href="product.php" class="btn-cancel">Cancel</a>
            <?php } else { ?>
                <button type="submit" name="add">➕ Add Product</button>
            <?php } ?>
        </form>
    </div>

    <!-- PRODUCT LIST -->
    <div class="product-grid">
        <?php
        $result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "
                <div class='product-card'>
                    <h3>{$row['name']}</h3>
                    <p><strong>💰 Price:</strong> $ {$row['price']}</p>
                    <p><strong>📦 Quantity:</strong> {$row['quantity']}</p>
                    <small>🕒 Added: {$row['created_at']}</small>
                    <a href='product.php?edit={$row['id']}' class='btn-edit'>Edit</a>
                    <a href='product.php?delete={$row['id']}' class='btn-delete' onclick='return confirm(\"Delete this product?\")'>Delete</a>
                </div>
                ";
            }
        } else {
            echo "<p style='text-align:center; color:#636e72;'>No products found.</p>";
        }
        ?>
    </div>
</section>

</body>
</html>
