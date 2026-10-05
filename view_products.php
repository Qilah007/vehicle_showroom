<?php
$adminOnly = true;
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
    <title>Product Catalog - Fin-Tastic Plushies</title>
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
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="view_products.php">🐟 Fin-Tastic Plushies Dashboard</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Admin: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a href="catalog.php" class="btn btn-outline-light btn-sm me-2">Customer Catalog View</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-white" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.8);">Fish Plush Inventory</h3>
        <a href="add_product.php" class="btn btn-success fw-bold">+ Add New Plush</a>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Image</th>
                            <th>Plush Name</th>
                            <th>Price (RM)</th>
                            <th>Description</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($row['image_path']) ?>" class="plush-thumb img-thumbnail" alt="Fish Plush" onclick="showModal('<?= htmlspecialchars($row['image_path']) ?>', '<?= htmlspecialchars(addslashes($row['product_name'])) ?>')">
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td>RM <?= number_format((float)$row['price'], 2) ?></td>
                                    <td><?= htmlspecialchars(mb_strimwidth($row['description'] ?? '', 0, 50, '...')) ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_product.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this fish plush? Physical photo will be permanently removed.')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No fish plush products found in inventory.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0 bg-dark">
                <img id="modalImg" src="" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showModal(src, title) {
    document.getElementById('modalImg').src = src;
    document.getElementById('modalTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>

</body>
</html>