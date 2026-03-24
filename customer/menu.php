<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// HANDLE ADD TO CART
if (isset($_GET["add"])) {
    $product_id = intval($_GET["add"]);

    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id");

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)");
    }

    header("Location: menu.php");
    exit();
}

// GET PRODUCTS
$products = mysqli_query($conn, "SELECT * FROM products WHERE status = 'available'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu | Cafe Cruise</title>
    <link rel="stylesheet" href="../assets/css/customer.css">
</head>
<body class="customer-body">

<div class="customer-layout">

    <aside class="customer-sidebar">
        <div class="customer-brand">
            <img src="../assets/logo/logo.jpg" alt="Cafe Cruise Logo">
            <h2>Cafe Cruise</h2>
        </div>

        <nav class="customer-menu">
            <a href="home.php">Home</a>
            <a href="menu.php" class="active">Menu</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="customer-main">

        <h1>Menu</h1>

        <div class="menu-grid">

            <?php while ($row = mysqli_fetch_assoc($products)) : ?>

                <div class="menu-card">
                    <div class="menu-image"></div>

                    <h3><?php echo $row["product_name"]; ?></h3>
                    <p><?php echo $row["description"]; ?></p>
                    <span>₱<?php echo $row["price"]; ?></span>

                    <a href="?add=<?php echo $row["product_id"]; ?>" class="add-btn">
                        Add to Cart
                    </a>
                </div>

            <?php endwhile; ?>

        </div>

    </main>

</div>

</body>
</html>