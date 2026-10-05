<?php
$adminOnly = true;
require_once "auth_check.php";
require_once "db.php";
require_once "db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: view_products.php");
    exit();
}

$errors = [];
$product_name = $product['product_name'];
$price = $product['price'];
$description = $product['description'];
$current_image = $product['image_path'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name = htmlspecialchars(trim(stripslashes($_POST['product_name'] ?? '')));
    $price = trim($_POST['price'] ?? '');
    $description = htmlspecialchars(trim(stripslashes($_POST['description'] ?? '')));
    $current_image = $_POST['current_image'] ?? '';

    if (empty($product_name)) {
        $errors['product_name'] = "Plush name is required.";
    }
    if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
        $errors['price'] = "Price must be a valid positive number.";
    }

    $image_updated = false;
    $new_target_path = $current_image;

    // Check if new image uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = "Error uploading new file.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "Image size must not exceed 2MB.";
        } elseif (!in_array($ext, $allowedExts)) {
            $errors['image'] = "Only JPG, JPEG, PNG, and WEBP formats allowed.";
        } else {
            $targetDir = "product_images/";
            $newFileName = uniqid("fish_", true) . "." . $ext;
            $new_target_path = $targetDir . $newFileName;
            $image_updated = true;
        }
    }

    if (empty($errors)) {
        if ($image_updated) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $new_target_path)) {
                // Delete physical old file
                if (!empty($current_image) && file_exists($current_image)) {
                    unlink($current_image);
                }
            } else {
                $errors['image'] = "Failed to upload new image.";
            }
        }

        if (empty($errors)) {
            $update_stmt = mysqli_prepare($conn, "UPDATE products SET product_name = ?, price = ?, description = ?, image_path = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "sdssi", $product_name, $price, $description, $new_target_path, $id);

            if (mysqli_stmt_execute($update_stmt)) {
                mysqli_stmt_close($update_stmt);
                header("Location: view_products.php?success=Fish plush updated successfully!");
                exit();
            } else {
                $errors['general'] = "Failed to update record: " . mysqli_error($conn);
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
        <title>Edit Fish Plush - E-Commerce Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('product_images/underwater_bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .plush-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; cursor: pointer; }
        .card, .table-responsive {
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(5px);
        }
    </style>
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand fw-bold" href="view_products.php">🐟 Fin-Tastic Plushies Admin</a>
            </div>
        </nav>

        <div class="container mb-5">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h3 class="card-title fw-bold text-center mb-4">Edit Fish Plush Details</h3>

                            <?php if (isset($errors['general'])): ?>
                                <div class="alert alert-danger"><?= $errors['general'] ?></div>
                            <?php endif; ?>

                            <form action="edit_product.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="current_image" value="<?= htmlspecialchars($current_image) ?>">

                                <div class="mb-3">
                                    <label class="form-label">Plush Name</label>
                                    <input type="text" name="product_name" class="form-control <?= isset($errors['product_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($product_name) ?>" required>
                                    <div class="invalid-feedback"><?= $errors['product_name'] ?? '' ?></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Price (RM)</label>
                                    <input type="number" step="0.01" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($price) ?>" required>
                                    <div class="invalid-feedback"><?= $errors['price'] ?? '' ?></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($description) ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Current Photo</label>
                                    <div class="mb-2">
                                        <?php if (!empty($current_image) && file_exists($current_image)): ?>
                                            <img src="<?= htmlspecialchars($current_image) ?>" class="img-thumbnail" style="height: 100px;">
                                        <?php else: ?>
                                            <span class="text-muted">No photo present</span>
                                        <?php endif; ?>
                                    </div>
                                    <label class="form-label">Replace Photo (Optional)</label>
                                    <input type="file" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept="image/*">
                                    <div class="invalid-feedback"><?= $errors['image'] ?? '' ?></div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="view_products.php" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Plush</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>