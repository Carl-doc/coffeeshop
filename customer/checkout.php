<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";

/* FETCH CART */
$query = "
    SELECT 
        cart.product_id,
        cart.quantity,
        products.product_name,
        products.price,
        products.stock,
        products.status
    FROM cart
    INNER JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = $user_id
";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: cart.php");
    exit();
}

$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $subtotal = $row["price"] * $row["quantity"];
    $total += $subtotal;

    $items[] = [
        "product_id" => $row["product_id"],
        "name" => $row["product_name"],
        "quantity" => $row["quantity"],
        "price" => $row["price"],
        "stock" => $row["stock"],
        "status" => $row["status"],
        "subtotal" => $subtotal
    ];
}

/* HANDLE ORDER SUBMIT */
if (isset($_POST["place_order"])) {
    $delivery_address = mysqli_real_escape_string($conn, trim($_POST["delivery_address"]));
    $contact_number = mysqli_real_escape_string($conn, trim($_POST["contact_number"]));
    $payment_method = "COD";

    if (empty($delivery_address) || empty($contact_number)) {
        $error = "Delivery address and contact number are required.";
    } else {

        /* RECHECK STOCK BEFORE SAVING ORDER */
        foreach ($items as $item) {
            $product_id = $item["product_id"];
            $latestProductQuery = mysqli_query($conn, "SELECT stock, status, product_name FROM products WHERE product_id = $product_id LIMIT 1");
            $latestProduct = mysqli_fetch_assoc($latestProductQuery);

            if (!$latestProduct || $latestProduct["status"] !== "available") {
                $error = $item["name"] . " is no longer available.";
                break;
            }

            if ($item["quantity"] > $latestProduct["stock"]) {
                $error = "Not enough stock for " . $item["name"] . ". Available stock: " . $latestProduct["stock"];
                break;
            }
        }

        if (empty($error)) {
            mysqli_begin_transaction($conn);

            try {
                /* INSERT ORDER */
                mysqli_query($conn, "
                    INSERT INTO orders (user_id, total_amount, delivery_address, contact_number, payment_method)
                    VALUES ($user_id, $total, '$delivery_address', '$contact_number', '$payment_method')
                ");

                $order_id = mysqli_insert_id($conn);

                /* INSERT ORDER ITEMS + DEDUCT STOCK */
                foreach ($items as $item) {
                    $product_id = $item["product_id"];
                    $quantity = $item["quantity"];
                    $price = $item["price"];
                    $subtotal = $item["subtotal"];

                    mysqli_query($conn, "
                        INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
                        VALUES ($order_id, $product_id, $quantity, $price, $subtotal)
                    ");

                    mysqli_query($conn, "
                        UPDATE products
                        SET stock = stock - $quantity
                        WHERE product_id = $product_id
                    ");

                    mysqli_query($conn, "
                        UPDATE products
                        SET status = 'unavailable'
                        WHERE product_id = $product_id AND stock <= 0
                    ");
                }

                /* CLEAR CART */
                mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

                mysqli_commit($conn);

                header("Location: checkout.php?success=1");
                exit();

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Failed to place order. Please try again.";
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
    <title>Checkout | Cafe Cruise</title>
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
            <a href="orders.php">My Orders</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="customer-main">

        <?php if (isset($_GET["success"])): ?>

            <div class="checkout-success">
                <h2>Order Placed!</h2>
                <p>Your order has been successfully placed.</p>
                <a href="orders.php" class="checkout-btn">View Orders</a>
            </div>

        <?php else: ?>

            <div class="customer-topbar">
                <div>
                    <h1>Checkout</h1>
                    <p>Complete your delivery details before confirming your order.</p>
                </div>
            </div>

            <?php if (!empty($error)) : ?>
                <div class="error-message" style="margin-top: 16px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="checkout-layout">

                <div class="checkout-items">
                    <h3 style="margin-bottom: 16px;">Order Items</h3>

                    <?php foreach ($items as $item): ?>
                        <div class="checkout-item">
                            <div>
                                <strong><?php echo htmlspecialchars($item["name"]); ?></strong><br>
                                ₱<?php echo number_format($item["price"], 2); ?> × <?php echo $item["quantity"]; ?>
                                <br>
                                <small>Stock available: <?php echo $item["stock"]; ?></small>
                            </div>
                            <div>
                                ₱<?php echo number_format($item["subtotal"], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <form method="POST" style="margin-top: 22px;">
                        <div class="form-group full">
                            <label for="delivery_address">Delivery Address</label>
                            <textarea name="delivery_address" id="delivery_address" rows="4" required><?php echo isset($_POST["delivery_address"]) ? htmlspecialchars($_POST["delivery_address"]) : ""; ?></textarea>
                        </div>

                        <div class="form-group full" style="margin-top: 14px;">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" required value="<?php echo isset($_POST["contact_number"]) ? htmlspecialchars($_POST["contact_number"]) : ""; ?>">
                        </div>

                        <div class="form-group full" style="margin-top: 14px;">
                            <label for="payment_method">Payment Method</label>
                            <input type="text" id="payment_method" value="Cash on Delivery (COD)" readonly>
                        </div>

                        <div style="margin-top: 18px;">
                            <button type="submit" name="place_order" class="checkout-btn">Confirm Order</button>
                        </div>
                    </form>
                </div>

                <div class="checkout-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Total</span>
                        <strong>₱<?php echo number_format($total, 2); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Payment</span>
                        <strong>COD</strong>
                    </div>
                </div>

            </div>

        <?php endif; ?>

    </main>
</div>

</body>
</html>