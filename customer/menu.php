<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

/* CATEGORY FILTER */
$allowed_categories = ["Coffee Based", "Non-Coffee", "Sparkling Routes Flavor"];
$selected_category = isset($_GET["category"]) ? trim($_GET["category"]) : "All";

/* GET PRODUCTS */
if ($selected_category !== "All" && in_array($selected_category, $allowed_categories)) {
    $safe_category = mysqli_real_escape_string($conn, $selected_category);
    $products = mysqli_query($conn, "SELECT * FROM products WHERE category = '$safe_category' ORDER BY product_id DESC");
} else {
    $selected_category = "All";
    $products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");
}
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
            <a href="#" onclick="openLogoutModal(); return false;">Logout</a>
        </nav>
    </aside>

    <main class="customer-main">

        <div class="customer-topbar">
            <div>
                <h1>Menu</h1>
                <p>Browse available drinks and add them to your cart.</p>
            </div>
        </div>

        <!-- CATEGORY FILTER -->
        <div class="menu-filter">
            <a href="menu.php" class="filter-btn <?php echo ($selected_category === 'All') ? 'active' : ''; ?>">All</a>
            <a href="menu.php?category=Coffee%20Based" class="filter-btn <?php echo ($selected_category === 'Coffee Based') ? 'active' : ''; ?>">Coffee Based</a>
            <a href="menu.php?category=Non-Coffee" class="filter-btn <?php echo ($selected_category === 'Non-Coffee') ? 'active' : ''; ?>">Non-Coffee</a>
            <a href="menu.php?category=Sparkling%20Routes%20Flavor" class="filter-btn <?php echo ($selected_category === 'Sparkling Routes Flavor') ? 'active' : ''; ?>">Sparkling Routes Flavor</a>
        </div>

        <div class="menu-grid">
            <?php if ($products && mysqli_num_rows($products) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($products)): ?>
                    <div class="menu-card">

                        <div class="menu-image">
                            <?php if (!empty($row["image"]) && file_exists(__DIR__ . "/../assets/products/" . $row["image"])): ?>
                                <img src="../assets/products/<?php echo htmlspecialchars($row["image"]); ?>" alt="<?php echo htmlspecialchars($row["product_name"]); ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </div>

                        <h3><?php echo htmlspecialchars($row["product_name"]); ?></h3>
                        <p class="menu-category"><?php echo htmlspecialchars($row["category"]); ?></p>
                        <p><?php echo htmlspecialchars($row["description"]); ?></p>
                        <span>₱<?php echo number_format($row["price"], 2); ?></span>
                        <small class="stock-text">Stock: <?php echo (int)$row["stock"]; ?></small>

                        <?php if ($row["status"] === "available" && (int)$row["stock"] > 0): ?>
                            <button 
    class="add-btn add-cart-btn"
    data-id="<?php echo $row["product_id"]; ?>">
    Add to Cart
</button>
                        <?php else: ?>
                            <button class="add-btn unavailable-btn" disabled>Out of Stock</button>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-cart-box">
                    <h3>No products available</h3>
                    <p>No items found in this category.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- LOGOUT MODAL -->
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

<!-- ADD TO CART TOAST -->
<div id="cartToast" class="cart-toast">✔ Added to cart</div>

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
<script>
document.querySelectorAll(".add-cart-btn").forEach(btn => {
    btn.addEventListener("click", function() {

        const productId = this.getAttribute("data-id");
        const button = this;

        fetch("add_to_cart.php?id=" + productId)
        .then(res => res.text())
        .then(() => {

            // BUTTON EFFECT
            button.classList.add("success");
            button.innerText = "Added ✓";

            // TOAST
            const toast = document.getElementById("cartToast");
            toast.classList.add("show");

            // RESET
            setTimeout(() => {
                button.classList.remove("success");
                button.innerText = "Add to Cart";
                toast.classList.remove("show");
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