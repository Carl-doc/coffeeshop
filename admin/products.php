<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$editMode = false;
$editProduct = null;
$openModal = false;

/* DELETE PRODUCT */
if (isset($_GET["delete"])) {
    $delete_id = intval($_GET["delete"]);

    $getImage = mysqli_query($conn, "SELECT image FROM products WHERE product_id = $delete_id");
    if ($getImage && mysqli_num_rows($getImage) > 0) {
        $imgRow = mysqli_fetch_assoc($getImage);
        if (!empty($imgRow["image"])) {
            $imgPath = __DIR__ . "/../assets/products/" . $imgRow["image"];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }
    }

    if (mysqli_query($conn, "DELETE FROM products WHERE product_id = $delete_id")) {
        header("Location: products.php?success=deleted");
        exit();
    } else {
        $error = "Failed to delete product.";
    }
}

/* OPEN ADD MODAL */
if (isset($_GET["add"])) {
    $openModal = true;
}

/* OPEN EDIT MODAL */
if (isset($_GET["edit"])) {
    $edit_id = intval($_GET["edit"]);
    $editQuery = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $edit_id LIMIT 1");

    if ($editQuery && mysqli_num_rows($editQuery) === 1) {
        $editProduct = mysqli_fetch_assoc($editQuery);
        $editMode = true;
        $openModal = true;
    }
}

/* ADD PRODUCT */
if (isset($_POST["add_product"])) {
    $name = mysqli_real_escape_string($conn, trim($_POST["product_name"]));
    $category = mysqli_real_escape_string($conn, trim($_POST["category"]));
    $description = mysqli_real_escape_string($conn, trim($_POST["description"]));
    $price = floatval($_POST["price"]);
    $stock = intval($_POST["stock"]);
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    $imageName = null;

    if (!empty($_FILES["image"]["name"])) {
        $targetDir = __DIR__ . "/../assets/products/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid image type. Only JPG, JPEG, PNG, and WEBP are allowed.";
            $openModal = true;
        } else {
            $imageName = uniqid("prod_", true) . "." . $ext;
            $targetFile = $targetDir . $imageName;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $error = "Failed to upload image.";
                $openModal = true;
            }
        }
    }

    if (empty($error)) {
        $query = "INSERT INTO products (product_name, category, description, price, image, stock, status)
                  VALUES ('$name', '$category', '$description', $price, " .
                  ($imageName ? "'$imageName'" : "NULL") . ", $stock, '$status')";

        if (mysqli_query($conn, $query)) {
            header("Location: products.php?success=added");
            exit();
        } else {
            $error = "Database error: " . mysqli_error($conn);
            $openModal = true;
        }
    }
}

/* UPDATE PRODUCT */
if (isset($_POST["update_product"])) {
    $product_id = intval($_POST["product_id"]);
    $name = mysqli_real_escape_string($conn, trim($_POST["product_name"]));
    $category = mysqli_real_escape_string($conn, trim($_POST["category"]));
    $description = mysqli_real_escape_string($conn, trim($_POST["description"]));
    $price = floatval($_POST["price"]);
    $stock = intval($_POST["stock"]);
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    $editMode = true;
    $openModal = true;

    $oldImageQuery = mysqli_query($conn, "SELECT image FROM products WHERE product_id = $product_id LIMIT 1");
    $oldImageRow = mysqli_fetch_assoc($oldImageQuery);
    $imageName = $oldImageRow["image"];

    if (!empty($_FILES["image"]["name"])) {
        $targetDir = __DIR__ . "/../assets/products/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid image type. Only JPG, JPEG, PNG, and WEBP are allowed.";
        } else {
            $newImageName = uniqid("prod_", true) . "." . $ext;
            $targetFile = $targetDir . $newImageName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                if (!empty($imageName)) {
                    $oldPath = $targetDir . $imageName;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $imageName = $newImageName;
            } else {
                $error = "Failed to upload new image.";
            }
        }
    }

    if (empty($error)) {
        $updateQuery = "UPDATE products
                        SET product_name = '$name',
                            category = '$category',
                            description = '$description',
                            price = $price,
                            image = " . ($imageName ? "'$imageName'" : "NULL") . ",
                            stock = $stock,
                            status = '$status'
                        WHERE product_id = $product_id";

        if (mysqli_query($conn, $updateQuery)) {
            header("Location: products.php?success=updated");
            exit();
        } else {
            $error = "Failed to update product.";
        }
    }

    $editQuery = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $product_id LIMIT 1");
    if ($editQuery && mysqli_num_rows($editQuery) === 1) {
        $editProduct = mysqli_fetch_assoc($editQuery);
    }
}

if (isset($_GET["success"])) {
    if ($_GET["success"] === "added") {
        $success = "Product added successfully.";
    } elseif ($_GET["success"] === "updated") {
        $success = "Product updated successfully.";
    } elseif ($_GET["success"] === "deleted") {
        $success = "Product deleted successfully.";
    }
}

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Cafe Cruise</title>
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
                <h1>Products</h1>
                <p>Manage your menu items and product availability.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="success-message" style="margin-bottom: 16px;"><?php echo $success; ?></div>
        <?php endif; ?>

        <section class="panel-card">
            <div class="panel-header">
                <h3>Product List</h3>
                <span>All menu items</span>
            </div>

            <div style="margin-bottom: 18px;">
                <a href="products.php?add=1" class="primary-action-btn">+ Add Product</a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><?php echo $row["product_id"]; ?></td>
                                    <td>
                                        <?php if (!empty($row["image"])) : ?>
                                            <img src="../assets/products/<?php echo htmlspecialchars($row["image"]); ?>" class="table-product-img" alt="Product Image">
                                        <?php else : ?>
                                            <div class="table-no-image">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row["product_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["category"]); ?></td>
                                    <td>₱<?php echo number_format($row["price"], 2); ?></td>
                                    <td><?php echo $row["stock"]; ?></td>
                                    <td>
                                        <span class="status <?php echo $row["status"] === 'available' ? 'completed' : 'pending'; ?>">
                                            <?php echo ucfirst($row["status"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="products.php?edit=<?php echo $row["product_id"]; ?>" class="table-action-btn edit-btn">Edit</a>
                                        <a href="products.php?delete=<?php echo $row["product_id"]; ?>" class="table-action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="empty-cell">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php if ($openModal): ?>
<div class="modal-overlay">
    <div class="product-modal">
        <div class="modal-header">
            <h3><?php echo $editMode ? "Edit Product" : "Add Product"; ?></h3>
            <a href="products.php" class="modal-close">&times;</a>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-grid">
            <?php if ($editMode): ?>
                <input type="hidden" name="product_id" value="<?php echo $editProduct['product_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="product_name" required
                    value="<?php echo $editMode ? htmlspecialchars($editProduct['product_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Coffee Based" <?php echo ($editMode && $editProduct['category'] === 'Coffee Based') ? 'selected' : ''; ?>>Coffee Based</option>
                    <option value="Non-Coffee" <?php echo ($editMode && $editProduct['category'] === 'Non-Coffee') ? 'selected' : ''; ?>>Non-Coffee</option>
                    <option value="Sparkling Routes Flavor" <?php echo ($editMode && $editProduct['category'] === 'Sparkling Routes Flavor') ? 'selected' : ''; ?>>Sparkling Routes Flavor</option>
                </select>
            </div>

            <div class="form-group">
                <label>Price (₱)</label>
                <input type="number" step="0.01" name="price" required
                    value="<?php echo $editMode ? htmlspecialchars($editProduct['price']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" required
                    value="<?php echo $editMode ? htmlspecialchars($editProduct['stock']) : '0'; ?>">
            </div>

            <div class="form-group full">
                <label>Description</label>
                <textarea name="description" rows="3"><?php echo $editMode ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="available" <?php echo ($editMode && $editProduct['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                    <option value="unavailable" <?php echo ($editMode && $editProduct['status'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                </select>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            </div>

            <div class="form-group full">
                <?php if ($editMode): ?>
                    <button type="submit" name="update_product" class="primary-action-btn">Update Product</button>
                <?php else: ?>
                    <button type="submit" name="add_product" class="primary-action-btn">Save Product</button>
                <?php endif; ?>

                <a href="products.php" class="secondary-action-btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.querySelector(".modal-overlay");

    if (!overlay) return;

    overlay.addEventListener("click", function (e) {
        if (e.target === overlay) {
            window.location.href = "products.php";
        }
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            window.location.href = "products.php";
        }
    });
});
</script>
<?php endif; ?>

</body>
</html>