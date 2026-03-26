<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* UPDATE QUANTITY */
if (isset($_GET["action"]) && isset($_GET["cart_id"])) {
    $cart_id = intval($_GET["cart_id"]);
    $action = $_GET["action"];

    $checkCart = mysqli_query($conn, "SELECT * FROM cart WHERE cart_id = $cart_id AND user_id = $user_id LIMIT 1");

    if ($checkCart && mysqli_num_rows($checkCart) === 1) {
        $cartItem = mysqli_fetch_assoc($checkCart);

        if ($action === "increase") {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE cart_id = $cart_id");
        } elseif ($action === "decrease") {
            if ($cartItem["quantity"] > 1) {
                mysqli_query($conn, "UPDATE cart SET quantity = quantity - 1 WHERE cart_id = $cart_id");
            }
        } elseif ($action === "remove") {
            mysqli_query($conn, "DELETE FROM cart WHERE cart_id = $cart_id");
        }
    }

    header("Location: cart.php");
    exit();
}

/* FETCH CART */
$query = "
    SELECT 
        cart.cart_id,
        cart.quantity,
        products.product_name,
        products.price,
        products.image,
        products.category
    FROM cart
    INNER JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = $user_id
    ORDER BY cart.cart_id DESC
";
$result = mysqli_query($conn, $query);

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Cafe Cruise</title>
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
            <a href="menu.php">Menu</a>
            <a href="cart.php" class="active">Cart</a>
            <a href="orders.php">My Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="customer-main">
        <div class="customer-topbar">
            <div>
                <h1>Your Cart</h1>
                <p>Review your selected drinks before checkout.</p>
            </div>
        </div>

        <div class="cart-layout">

            <section class="cart-items-panel">
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <?php
                            $subtotal = $row["price"] * $row["quantity"];
                            $total += $subtotal;
                        ?>
                        <div class="cart-item-card">
                            <div class="cart-item-image">
                                <?php if (!empty($row["image"])) : ?>
                                    <img src="../assets/products/<?php echo htmlspecialchars($row["image"]); ?>" alt="<?php echo htmlspecialchars($row["product_name"]); ?>">
                                <?php else : ?>
                                    <div class="cart-no-image">No Image</div>
                                <?php endif; ?>
                            </div>

                            <div class="cart-item-details">
                                <span class="cart-category"><?php echo htmlspecialchars($row["category"]); ?></span>
                                <h3><?php echo htmlspecialchars($row["product_name"]); ?></h3>
                                <p>₱<?php echo number_format($row["price"], 2); ?></p>
                            </div>

                            <div class="cart-item-actions">
                                <div class="qty-controls">
                                    <a href="cart.php?action=decrease&cart_id=<?php echo $row["cart_id"]; ?>" class="qty-btn">-</a>
                                    <span><?php echo $row["quantity"]; ?></span>
                                    <a href="cart.php?action=increase&cart_id=<?php echo $row["cart_id"]; ?>" class="qty-btn">+</a>
                                </div>

                                <div class="cart-subtotal">
                                    ₱<?php echo number_format($subtotal, 2); ?>
                                </div>

                                <a href="cart.php?action=remove&cart_id=<?php echo $row["cart_id"]; ?>" class="remove-cart-btn" onclick="return confirm('Remove this item from cart?')">
                                    Remove
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="empty-cart-box">
                        <h3>Your cart is empty</h3>
                        <p>Add drinks from the menu to continue.</p>
                        <a href="menu.php" class="hero-btn">Browse Menu</a>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="cart-summary-panel">
                <div class="cart-summary-card">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Total</span>
                        <strong>₱<?php echo number_format($total, 2); ?></strong>
                    </div>

                    <?php if ($total > 0) : ?>
                        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    <?php else : ?>
                        <button class="checkout-btn disabled-btn" disabled>Proceed to Checkout</button>
                    <?php endif; ?>
                </div>
            </aside>

        </div>
    </main>

</div>

</body>
</html>