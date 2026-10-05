<?php
$adminOnly = true;
require_once "auth_check.php";
require_once "db.php";
require_once "db.php";

$errors = [];
$product_name = "";
$price = "";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Server-side sanitization
    $product_name = htmlspecialchars(trim(stripslashes($_POST['product_name'] ?? '')));
    $price = trim($_POST['price'] ?? '');
    $description = htmlspecialchars(trim(stripslashes($_POST['description'] ?? '')));

    if (empty($product_name)) {
        $errors['product_name'] = "Fish plush name is required.";
    }
    if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
        $errors['price'] = "Price must be a valid positive number.";
    }
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['image'] = "Product image is required.";
    } else {
        $file = $_FILES['image'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = "Error uploading file.";
        } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors['image'] = "Image size must not exceed 2MB.";
        } elseif (!in_array($ext, $allowedExts)) {
            $errors['image'] = "Only JPG, JPEG, PNG, and WEBP formats are allowed.";
        }
    }

    if (empty($errors)) {
        $targetDir = "product_images/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $newFileName = uniqid("fish_", true) . "." . $ext;
        $targetFilePath = $targetDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (product_name, price, description, image_path) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sdss", $product_name, $price, $description, $targetFilePath);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: view_products.php?success=Fish plush added successfully!");
                exit();
            } else {
                $errors['general'] = "Database insertion failed: " . mysqli_error($conn);
            }
        } else {
            $errors['image'] = "Failed to move uploaded image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Fish Plush - E-Commerce Admin</title>
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
                            <h3 class="card-title fw-bold text-center mb-4">Add New Fish Plush</h3>

                            <?php if (isset($errors['general'])): ?>
                                <div class="alert alert-danger"><?= $errors['general'] ?></div>
                            <?php endif; ?>

                            <form action="add_product.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                                <!-- Product Name -->
                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">Plush Name</label>
                                    <input type="text" name="product_name" id="product_name" class="form-control <?= isset($errors['product_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($product_name) ?>">
                                    <div class="invalid-feedback" id="nameError"><?= $errors['product_name'] ?? 'Plush name is required.' ?></div>
                                </div>

                                <!-- Price -->
                                <div class="mb-3">
                                    <label class="form-label">Price (RM)</label>
                                    <input type="number" step="0.01" name="price" id="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($price) ?>">
                                    <div class="invalid-feedback" id="priceError"><?= $errors['price'] ?? 'Enter a valid positive price.' ?></div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="description" rows="3" class="form-control"><?= htmlspecialchars($description) ?></textarea>
                                </div>

                                <!-- Image Upload -->
                                <div class="mb-3">
                                    <label class="form-label">Plush Photo (Max 2MB)</label>
                                    <input type="file" name="image" id="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept="image/*">
                                    <div class="invalid-feedback" id="imageError"><?= $errors['image'] ?? 'Image is required.' ?></div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="view_products.php" class="btn btn-outline-secondary">Back to Catalog</a>
                                    <button type="submit" class="btn btn-primary">Save Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function validateForm() {
            let isValid = true;
            const name = document.getElementById('product_name');
            const price = document.getElementById('price');
            const image = document.getElementById('image');

            if (name.value.trim() === "") {
                name.classList.add('is-invalid');
                isValid = false;
            } else {
                name.classList.remove('is-invalid');
            }

            if (price.value.trim() === "" || isNaN(price.value) || parseFloat(price.value) <= 0) {
                price.classList.add('is-invalid');
                isValid = false;
            } else {
                price.classList.remove('is-invalid');
            }

            if (image.files.length === 0) {
                image.classList.add('is-invalid');
                isValid = false;
            } else {
                image.classList.remove('is-invalid');
            }

            return isValid;
        }
        </script>
    </body>
</html>