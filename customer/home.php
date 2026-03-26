<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$customer_name = $_SESSION["full_name"];

$popular = mysqli_query($conn, "
    SELECT 
        p.product_id,
        p.product_name,
        p.price,
        p.image,
        COALESCE(SUM(oi.quantity), 0) AS total_ordered
    FROM products p
    INNER JOIN order_items oi ON p.product_id = oi.product_id
    WHERE p.status = 'available'
    GROUP BY p.product_id, p.product_name, p.price, p.image
    HAVING total_ordered > 0
    ORDER BY total_ordered DESC, p.product_id DESC
    LIMIT 3
");
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
            <a href="#" onclick="openLogoutModal(); return false;">Logout</a>
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
        <p>Top 3 most ordered drinks</p>
    </div>

    <div class="featured-grid">
        <?php if ($popular && mysqli_num_rows($popular) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($popular)): ?>
                <div class="featured-card">
                    <div class="featured-image">
                        <?php if (!empty($row["image"]) && file_exists(__DIR__ . "/../assets/products/" . $row["image"])): ?>
                            <img src="../assets/products/<?php echo htmlspecialchars($row["image"]); ?>" alt="<?php echo htmlspecialchars($row["product_name"]); ?>">
                        <?php else: ?>
                            <div class="no-image">No Image</div>
                        <?php endif; ?>
                    </div>

                    <h3><?php echo htmlspecialchars($row["product_name"]); ?></h3>
                    <p>₱<?php echo number_format($row["price"], 2); ?></p>

                    <button 
    class="popular-cart-btn add-cart-btn" 
    data-id="<?php echo $row["product_id"]; ?>">
    Add to Cart
</button>
</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-cart-box">
                <h3>No popular drinks yet</h3>
                <p>Popular drinks will appear once customers start ordering.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="logout-modal-overlay" id="logoutModal">
    <div class="logout-modal-box">
        <div class="logout-modal-header">
            <h3>Logout</h3>
            <button class="logout-close-btn" onclick="closeLogoutModal()">&times;</button>
        </div>

        <div class="logout-modal-body">
            <p>Are you sure you want to logout from your account?</p>
        </div>

        <div class="logout-modal-actions">
            <button class="logout-cancel-btn" onclick="closeLogoutModal()">Cancel</button>
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
    const logoutModal = document.getElementById("logoutModal");
    if (e.target === logoutModal) {
        closeLogoutModal();
    }
});
</script>

<script>
document.querySelectorAll(".add-cart-btn").forEach(btn => {
    btn.addEventListener("click", function() {

        const productId = this.getAttribute("data-id");
        const button = this;

        fetch("add_to_cart.php?id=" + productId)
        .then(response => response.text())
        .then(data => {

            // success effect
            button.innerText = "Added ✓";
            button.style.background = "#28a745";

            setTimeout(() => {
                button.innerText = "Add to Cart";
                button.style.background = "#111";
            }, 1500);

        })
        .catch(() => {
            alert("Failed to add to cart");
        });

    });
});
</script>
</body>
</html>