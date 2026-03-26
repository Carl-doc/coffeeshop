<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$query = "
    SELECT *
    FROM orders
    WHERE user_id = $user_id
    ORDER BY order_id DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Cafe Cruise</title>
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
            <a href="cart.php">Cart</a>
            <a href="orders.php" class="active">My Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="customer-main">
        <div class="customer-topbar">
            <div>
                <h1>My Orders</h1>
                <p>Track your placed orders and delivery details.</p>
            </div>
        </div>

        <div class="orders-list" style="margin-top: 24px;">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <h3>Order #<?php echo $row["order_id"]; ?></h3>
                                <p><?php echo $row["created_at"]; ?></p>
                            </div>
                            <span class="status <?php echo $row["order_status"]; ?>">
                                <?php echo ucfirst($row["order_status"]); ?>
                            </span>
                        </div>

                        <div class="order-card-body">
                            <p><strong>Total:</strong> ₱<?php echo number_format($row["total_amount"], 2); ?></p>
                            <p><strong>Payment:</strong> <?php echo htmlspecialchars($row["payment_method"]); ?></p>
                            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($row["contact_number"]); ?></p>
                            <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($row["delivery_address"]); ?></p>

                            <div class="order-items-box">
                                <strong>Items:</strong>
                                <ul>
                                    <?php
                                    $order_id = $row["order_id"];
                                    $itemsQuery = "
                                        SELECT order_items.quantity, order_items.price, order_items.subtotal, products.product_name
                                        FROM order_items
                                        INNER JOIN products ON order_items.product_id = products.product_id
                                        WHERE order_items.order_id = $order_id
                                    ";
                                    $itemsResult = mysqli_query($conn, $itemsQuery);

                                    if ($itemsResult && mysqli_num_rows($itemsResult) > 0):
                                        while ($item = mysqli_fetch_assoc($itemsResult)):
                                    ?>
                                        <li>
                                            <?php echo htmlspecialchars($item["product_name"]); ?> —
                                            ₱<?php echo number_format($item["price"], 2); ?> ×
                                            <?php echo $item["quantity"]; ?> =
                                            ₱<?php echo number_format($item["subtotal"], 2); ?>
                                        </li>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                        <li>No items found.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-cart-box">
                    <h3>No orders yet</h3>
                    <p>You have not placed any order yet.</p>
                    <a href="menu.php" class="hero-btn">Browse Menu</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>