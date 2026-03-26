<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$success = "";

/* UPDATE STATUS */
if (isset($_POST["update_status"])) {
    $order_id = intval($_POST["order_id"]);
    $allowed_statuses = ["pending", "preparing", "out_for_delivery", "completed", "cancelled"];
    $order_status = $_POST["order_status"] ?? "";

    if (!in_array($order_status, $allowed_statuses, true)) {
        header("Location: orders.php?success=invalid");
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE order_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $order_status, $order_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: orders.php?success=updated");
        exit();
    } else {
        die("Update failed: " . mysqli_error($conn));
    }
}

/* FETCH ORDERS */
$query = "
    SELECT 
        orders.order_id,
        orders.user_id,
        orders.total_amount,
        orders.delivery_address,
        orders.contact_number,
        orders.payment_method,
        orders.order_status,
        orders.created_at,
        users.full_name
    FROM orders
    INNER JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.order_id DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Cafe Cruise</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-brand">
            <img src="../assets/logo/logo.jpg" alt="Cafe Cruise Logo">
            <div>
                <h2>Cafe Cruise</h2>
                <p>Admin Panel</p>
            </div>
        </div>

        <nav class="admin-menu">
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="orders.php" class="active">Orders</a>
    <a href="customers.php">Customers</a>
    <a href="reports.php">Reports</a>
    <a href="#" onclick="openLogoutModal(); return false;">Logout</a>
</nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1>Orders</h1>
                <p>Track and update customer orders.</p>
            </div>
        </div>

        <?php if (!empty($success)) : ?>
            <div class="success-message" style="margin-bottom: 16px;"><?php echo $success; ?></div>
        <?php endif; ?>

        <section class="panel-card">
            <div class="panel-header">
                <h3>Order List</h3>
                <span>All customer orders</span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $row["order_id"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                    <td>₱<?php echo number_format($row["total_amount"], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row["payment_method"]); ?></td>
                                    <td>
                                        <span class="status <?php echo $row["order_status"]; ?>">
                                           <?php echo ucwords(str_replace("_", " ", $row["order_status"])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row["created_at"]; ?></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $row["order_id"]; ?>">
                                            <select name="order_status">
    <option value="pending" <?php echo $row["order_status"] == "pending" ? "selected" : ""; ?>>Pending</option>
    <option value="preparing" <?php echo $row["order_status"] == "preparing" ? "selected" : ""; ?>>Preparing</option>
    <option value="out_for_delivery" <?php echo $row["order_status"] == "out_for_delivery" ? "selected" : ""; ?>>Out for Delivery</option>
    <option value="completed" <?php echo $row["order_status"] == "completed" ? "selected" : ""; ?>>Completed</option>
    <option value="cancelled" <?php echo $row["order_status"] == "cancelled" ? "selected" : ""; ?>>Cancelled</option>
</select>
                                            <button type="submit" name="update_status" class="table-action-btn edit-btn">Save</button>
                                        </form>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="7" class="order-details-cell">
                                        <div class="order-details-box">
                                            <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($row["delivery_address"]); ?></p>
                                            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($row["contact_number"]); ?></p>

                                            <div class="mini-order-items">
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
                                    </td>
                                </tr>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-cell">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div class="logout-modal-overlay" id="logoutModal">
    <div class="logout-modal-box">
        <div class="logout-modal-header">
            <h3>Logout</h3>
            <button type="button" class="logout-close-btn" onclick="closeLogoutModal()">&times;</button>
        </div>

        <div class="logout-modal-body">
            <p>Are you sure you want to logout from your admin account?</p>
        </div>

        <div class="logout-modal-actions">
            <button type="button" class="logout-cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <a href="logout.php" class="logout-confirm-btn">Yes, Logout</a>
        </div>
    </div>
</div>

<script>
function openLogoutModal() {
    document.getElementById("logoutModal").classList.add("show");
}

function closeLogoutModal() {
    document.getElementById("logoutModal").classList.remove("show");
}

document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        closeLogoutModal();
    }
});

document.addEventListener("click", function (e) {
    const logoutModal = document.getElementById("logoutModal");
    if (e.target === logoutModal) {
        closeLogoutModal();
    }
});
</script>

</body>
</html>