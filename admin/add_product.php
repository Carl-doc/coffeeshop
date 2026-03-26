<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}


$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, trim($_POST["product_name"]));
    $category = mysqli_real_escape_string($conn, trim($_POST["category"]));
    $description = mysqli_real_escape_string($conn, trim($_POST["description"]));
    $price = floatval($_POST["price"]);
    $stock = intval($_POST["stock"]);
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    // Image upload
    $imageName = null;

    if (!empty($_FILES["image"]["name"])) {
        $targetDir = __DIR__ . "/../assets/products/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed = ["jpg","jpeg","png","webp"];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid image type. Only JPG, PNG, WEBP allowed.";
        } else {
            $imageName = uniqid("prod_", true) . "." . $ext;
            $targetFile = $targetDir . $imageName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $error = "Failed to upload image.";
            }
        }
    }

    if (empty($error)) {
        $query = "INSERT INTO products (product_name, category, description, price, image, stock, status)
                  VALUES ('$name', '$category', '$description', $price, " .
                  ($imageName ? "'$imageName'" : "NULL") . ", $stock, '$status')";

        if (mysqli_query($conn, $query)) {
            $success = "Product added successfully!";
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Cafe Cruise</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-brand">
            <img src="../assets/logo/logo.jpg" alt="Logo">
            <div>
                <h2>Cafe Cruise</h2>
                <p>Admin Panel</p>
            </div>
        </div>

        <nav class="admin-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php" class="active">Products</a>
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
            <a href="reports.php">Reports</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="admin-main">

        <div class="admin-topbar">
            <div>
                <h1>Add Product</h1>
                <p>Create a new menu item</p>
            </div>
        </div>

        <section class="panel-card">

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="form-grid">

                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="product_name" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Coffee">Coffee Based</option>
                        <option value="Non-Coffee">Non-Coffee</option>
                        <option value="Matcha">Matcha</option>
                        <option value="Sparkling">Sparkling / Fruit</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" step="0.01" name="price" required>
                </div>

                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="0">
                </div>

                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-group full">
                    <button type="submit" class="primary-action-btn">Save Product</button>
                </div>

            </form>

        </section>

    </main>
</div>

</body>
</html>