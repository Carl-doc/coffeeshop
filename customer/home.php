<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$customer_name = $_SESSION["full_name"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Cafe Cruise</title>
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
                <a href="home.php" class="active">Home</a>
                <a href="menu.php">Menu</a>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>

        <main class="customer-main">
            <header class="customer-topbar">
                <div>
                    <h1>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h1>
                    <p>Order your favorite drinks quickly and easily.</p>
                </div>
            </header>

            <section class="customer-hero">
                <div class="hero-text">
                    <span class="hero-badge">Cafe Cruise Customer Portal</span>
                    <h2>Fresh Drinks, Fast Orders, Easy Checkout</h2>
                    <p>
                        Browse the menu, add your favorite drinks to cart,
                        and manage your orders in one place.
                    </p>
                    <a href="menu.php" class="hero-btn">Order Now</a>
                </div>
            </section>

            <section class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>Browse Menu</h3>
                    <p>See available coffee, non-coffee, and matcha drinks.</p>
                </div>

                <div class="dashboard-card">
                    <h3>View Cart</h3>
                    <p>Check selected items before placing your order.</p>
                </div>

                <div class="dashboard-card">
                    <h3>Track Orders</h3>
                    <p>Monitor your order status anytime.</p>
                </div>
            </section>

            <section class="featured-section">
                <div class="section-title">
                    <h2>Popular Drinks</h2>
                    <p>Quick picks for your next order</p>
                </div>

                <div class="featured-grid">
                    <div class="featured-card">
                        <div class="featured-image one"></div>
                        <h3>Iced Coffee</h3>
                        <p>₱39 - ₱49</p>
                    </div>

                    <div class="featured-card">
                        <div class="featured-image two"></div>
                        <h3>Coffee-Based Series</h3>
                        <p>₱39 - ₱59</p>
                    </div>

                    <div class="featured-card">
                        <div class="featured-image three"></div>
                        <h3>Non-Coffee Series</h3>
                        <p>₱39 - ₱59</p>
                    </div>

                    <div class="featured-card">
                        <div class="featured-image four"></div>
                        <h3>Specialty Matcha Series</h3>
                        <p>₱49 - ₱59</p>
                    </div>
                </div>
            </section>
        </main>

    </div>

</body>
</html>