<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// GET PRODUCTS
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        <div class="customer-topbar">
            <div>
                <h1>Menu</h1>
                <p>Browse available drinks and add them to your cart.</p>
            </div>
        </div>

        <div class="menu-grid">

            <?php if ($products && mysqli_num_rows($products) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($products)) : ?>
                    <div class="menu-card">

                        <div class="menu-image">
                            <?php if (!empty($row["image"])) : ?>
                                <img src="../assets/products/<?php echo htmlspecialchars($row["image"]); ?>" alt="<?php echo htmlspecialchars($row["product_name"]); ?>">
                            <?php else : ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </div>

                        <h3><?php echo htmlspecialchars($row["product_name"]); ?></h3>
                        <p class="menu-category"><?php echo htmlspecialchars($row["category"]); ?></p>
                        <p><?php echo htmlspecialchars($row["description"]); ?></p>
                        <span>₱<?php echo number_format($row["price"], 2); ?></span>
                        <small class="stock-text">Stock: <?php echo (int)$row["stock"]; ?></small>

                        <?php if ($row["status"] === "available" && (int)$row["stock"] > 0): ?>
                            <a href="add_to_cart.php?id=<?php echo $row["product_id"]; ?>" class="add-btn">
                                Add to Cart
                            </a>
                        <?php else: ?>
                            <button class="add-btn unavailable-btn" disabled>Out of Stock</button>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="empty-cart-box">
                    <h3>No products available</h3>
                    <p>Please check again later.</p>
                </div>
            <?php endif; ?>

        </div>

    </main>

</div>

</body>
</html>