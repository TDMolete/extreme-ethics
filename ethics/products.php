<?php 
require_once 'includes/config.php'; 
redirectAdminFromCustomerPages();
include 'includes/header.php';
$products = getProducts($pdo);

?>
<div class="container">
    <?php if (!isPreviewMode()): ?>
<a href="product.php?id=<?= $product['id'] ?>" class="btn-wa">VIEW DETAILS</a>
<?php else: ?>
<a href="product.php?id=<?= $product['id'] ?>" class="btn-wa" style="pointer-events: none; opacity: 0.5;">PREVIEW ONLY</a>
<?php endif; ?>
    <h1>ALL PRODUCTS</h1>
    <div class="product-grid">
        <?php foreach ($products as $product): 
            $images = getProductImages($product['name']);
            $image = !empty($images) ? $images[0] : 'assets/images/products/placeholder.jpg';
        ?>
        <div class="product-card urban-card" data-product-id="<?= $product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>" data-product-price="<?= $product['price'] ?>">
            <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
            <h3><a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
            <p class="price">R<?= $product['price'] ?></p>
            <a href="product.php?id=<?= $product['id'] ?>" class="btn-wa">VIEW DETAILS</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>