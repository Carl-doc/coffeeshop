<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

/* TOTAL SALES */
$salesQuery = mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS total_sales
    FROM orders
    WHERE order_status IN ('pending', 'preparing', 'completed')
");
$totalSales = mysqli_fetch_assoc($salesQuery)["total_sales"];

/* TOTAL ORDERS */
$ordersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_orders FROM orders");
$totalOrders = mysqli_fetch_assoc($ordersQuery)["total_orders"];

/* TOTAL CUSTOMERS */
$customersQuery = mysqli_query($conn, "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'");
$totalCustomers = mysqli_fetch_assoc($customersQuery)["total_customers"];

/* COMPLETED ORDERS */
$completedQuery = mysqli_query($conn, "SELECT COUNT(*) AS completed_orders FROM orders WHERE order_status = 'completed'");
$completedOrders = mysqli_fetch_assoc($completedQuery)["completed_orders"];

/* RECENT ORDERS */
$recentOrders = mysqli_query($conn, "
    SELECT orders.order_id, users.full_name, orders.total_amount, orders.order_status, orders.created_at
    FROM orders
    INNER JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.order_id DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Cafe Cruise</title>
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
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
            <a href="reports.php" class="active">Reports</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1>Reports</h1>
                <p>View sales and order performance summary.</p>
            </div>
        </div>

        <section class="admin-stats">
            <div class="stat-card">
                <span class="stat-label">Total Sales</span>
                <h3>₱<?php echo number_format($totalSales, 2); ?></h3>
                <p>All recorded order sales</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Total Orders</span>
                <h3><?php echo $totalOrders; ?></h3>
                <p>All customer orders</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Total Customers</span>
                <h3><?php echo $totalCustomers; ?></h3>
                <p>Registered customers</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Completed Orders</span>
                <h3><?php echo $completedOrders; ?></h3>
                <p>Finished transactions</p>
            </div>
        </section>

        <section class="panel-card" style="margin-top: 24px;">
            <div class="panel-header">
                <h3>Recent Order Summary</h3>
                <span>Latest transactions</span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($recentOrders)): ?>
                                <tr>
                                    <td>#<?php echo $row["order_id"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                    <td>₱<?php echo number_format($row["total_amount"], 2); ?></td>
                                    <td>
                                        <span class="status <?php echo $row["order_status"]; ?>">
                                            <?php echo ucfirst($row["order_status"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row["created_at"]; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-cell">No report data found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</div>

</body>
</html>