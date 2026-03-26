<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["full_name"];

/* TOTAL PRODUCTS */
$totalProductsQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total_products
    FROM products
");
$totalProducts = mysqli_fetch_assoc($totalProductsQuery)["total_products"] ?? 0;

/* TOTAL SALES - completed only */
$totalSalesQuery = mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS total_sales
    FROM orders
    WHERE order_status = 'completed'
");
$totalSales = mysqli_fetch_assoc($totalSalesQuery)["total_sales"] ?? 0;

/* TOTAL ORDERS */
$totalOrdersQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total_orders
    FROM orders
");
$totalOrders = mysqli_fetch_assoc($totalOrdersQuery)["total_orders"] ?? 0;

/* TOTAL CUSTOMERS */
$totalCustomersQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS total_customers
    FROM users
    WHERE role = 'customer'
");
$totalCustomers = mysqli_fetch_assoc($totalCustomersQuery)["total_customers"] ?? 0;

/* COMPLETED ORDERS */
$completedOrdersQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS completed_orders
    FROM orders
    WHERE order_status = 'completed'
");
$completedOrders = mysqli_fetch_assoc($completedOrdersQuery)["completed_orders"] ?? 0;

/* DAILY SALES */
$dailySalesQuery = mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS daily_sales
    FROM orders
    WHERE order_status = 'completed'
      AND DATE(created_at) = CURDATE()
");
$dailySales = mysqli_fetch_assoc($dailySalesQuery)["daily_sales"] ?? 0;

/* MONTHLY SALES */
$monthlySalesQuery = mysqli_query($conn, "
    SELECT COALESCE(SUM(total_amount), 0) AS monthly_sales
    FROM orders
    WHERE order_status = 'completed'
      AND MONTH(created_at) = MONTH(CURDATE())
      AND YEAR(created_at) = YEAR(CURDATE())
");
$monthlySales = mysqli_fetch_assoc($monthlySalesQuery)["monthly_sales"] ?? 0;

/* RECENT ORDERS */
$recentOrders = mysqli_query($conn, "
    SELECT 
        orders.order_id,
        users.full_name,
        orders.total_amount,
        orders.order_status
    FROM orders
    INNER JOIN users ON orders.user_id = users.user_id
    ORDER BY orders.order_id DESC
    LIMIT 5
");

/* BEST-SELLING PRODUCTS */
$bestSellingQuery = mysqli_query($conn, "
    SELECT 
        products.product_name,
        SUM(order_items.quantity) AS total_sold
    FROM order_items
    INNER JOIN products ON order_items.product_id = products.product_id
    INNER JOIN orders ON order_items.order_id = orders.order_id
    WHERE orders.order_status = 'completed'
    GROUP BY order_items.product_id, products.product_name
    ORDER BY total_sold DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Cafe Cruise</title>
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
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
            <a href="reports.php">Reports</a>
            <a href="#" onclick="openLogoutModal(); return false;">Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?></h1>
                <p>Manage your coffee shop in one clean workspace.</p>
            </div>

            <div class="admin-topbar-right">
                <span class="admin-chip">Today</span>
            </div>
        </header>

        <section class="admin-hero">
            <div class="admin-hero-text">
                <span class="hero-badge">Cafe Cruise Management</span>
                <h2>Control products, orders, and daily operations</h2>
                <p>
                    Monitor store activity, manage menu items, and review recent transactions
                    with a simple and modern dashboard.
                </p>
            </div>
        </section>

        <section class="admin-stats">
            <div class="stat-card">
                <span class="stat-label">Total Products</span>
                <h3><?php echo $totalProducts; ?></h3>
                <p>Active menu items</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Total Orders</span>
                <h3><?php echo $totalOrders; ?></h3>
                <p>All customer orders</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Customers</span>
                <h3><?php echo $totalCustomers; ?></h3>
                <p>Registered users</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Total Sales</span>
                <h3>₱<?php echo number_format($totalSales, 2); ?></h3>
                <p>Completed order sales</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Daily Sales</span>
                <h3>₱<?php echo number_format($dailySales, 2); ?></h3>
                <p>Today’s completed sales</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Monthly Sales</span>
                <h3>₱<?php echo number_format($monthlySales, 2); ?></h3>
                <p>This month’s completed sales</p>
            </div>

            <div class="stat-card">
                <span class="stat-label">Completed Orders</span>
                <h3><?php echo $completedOrders; ?></h3>
                <p>Finished transactions</p>
            </div>
        </section>

        <section class="admin-grid">
            <div class="panel-card">
                <div class="panel-header">
                    <h3>Recent Orders</h3>
                    <span>Latest activity</span>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($recentOrders)): ?>
                                    <tr>
                                        <td>#<?php echo $row["order_id"]; ?></td>
                                        <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                        <td>Order</td>
                                        <td>
                                            <span class="status <?php echo $row["order_status"]; ?>">
                                                <?php echo ucfirst($row["order_status"]); ?>
                                            </span>
                                        </td>
                                        <td>₱<?php echo number_format($row["total_amount"], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-cell">No recent orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="side-panels">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3>Quick Actions</h3>
                        <span>Shortcuts</span>
                    </div>

                    <div class="action-list">
                        <a href="products.php?add=1" class="action-btn">Add Product</a>
                        <a href="orders.php" class="action-btn">View Orders</a>
                        <a href="customers.php" class="action-btn">Customer List</a>
                        <a href="reports.php" class="action-btn">Open Reports</a>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <h3>Best-Selling Products</h3>
                        <span>Top completed sales</span>
                    </div>

                    <ul class="notes-list">
                        <?php if ($bestSellingQuery && mysqli_num_rows($bestSellingQuery) > 0): ?>
                            <?php while ($best = mysqli_fetch_assoc($bestSellingQuery)): ?>
                                <li>
                                    <?php echo htmlspecialchars($best["product_name"]); ?> —
                                    <?php echo (int)$best["total_sold"]; ?> sold
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>No completed sales data yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
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

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeLogoutModal();
    }
});

document.addEventListener("click", function(e) {
    const modal = document.getElementById("logoutModal");
    if (e.target === modal) {
        closeLogoutModal();
    }
});
</script>

</body>
</html>