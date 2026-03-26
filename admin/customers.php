<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$query = "
    SELECT user_id, full_name, email, is_verified, created_at
    FROM users
    WHERE role = 'customer'
    ORDER BY user_id DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | Cafe Cruise</title>
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
            <a href="customers.php" class="active">Customers</a>
            <a href="reports.php">Reports</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1>Customers</h1>
                <p>View all registered customer accounts.</p>
            </div>
        </div>

        <section class="panel-card">
            <div class="panel-header">
                <h3>Customer List</h3>
                <span>Registered users</span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $row["user_id"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                    <td>
                                        <?php if ($row["is_verified"] == 1): ?>
                                            <span class="status completed">Verified</span>
                                        <?php else: ?>
                                            <span class="status pending">Unverified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row["created_at"]; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-cell">No customers found.</td>
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