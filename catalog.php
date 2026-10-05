<?php
require_once "auth_check.php";
require_once "db.php";

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Plush Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('product_images/underwater_bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .plush-card-img {
            height: 220px;
            object-fit: cover;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
            cursor: pointer;
        }
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            background-color: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(5px);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2) !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="catalog.php">🐟 Fin-Tastic Plushies</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Hi, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="view_products.php" class="btn btn-warning btn-sm me-2 fw-bold">Admin Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-white" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.8);">Our Fish Plush Collection</h3>
    </div>

    <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm plush-card" 
                         onclick="openModal('<?= htmlspecialchars($row['image_path'] ?? '') ?>', '<?= htmlspecialchars(addslashes($row['product_name'])) ?>', 'RM <?= number_format((float)$row['price'], 2) ?>', '<?= htmlspecialchars(addslashes($row['description'] ?? '')) ?>')">
                        
                        <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                            <img src="<?= htmlspecialchars($row['image_path']) ?>" class="card-img-top plush-card-img" alt="<?= htmlspecialchars($row['product_name']) ?>">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center plush-card-img">
                                <span>No Photo Available</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold"><?= htmlspecialchars($row['product_name']) ?></h5>
                                <span class="badge bg-primary fs-6">RM <?= number_format((float)$row['price'], 2) ?></span>
                            </div>
                            <p class="card-text text-secondary small flex-grow-1">
                                <?= htmlspecialchars($row['description']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-white py-5">
                <h4>No fish plushies available right now.</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalTitle"></h5>
                    <span id="modalPrice" class="badge bg-primary fs-6"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <img id="modalImg" src="" class="img-fluid" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer justify-content-start">
                <p id="modalDesc" class="mb-0 text-secondary"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(imgSrc, title, price, desc) {
    document.getElementById('modalImg').src = imgSrc;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalPrice').textContent = price;
    document.getElementById('modalDesc').textContent = desc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
</body>
</html>