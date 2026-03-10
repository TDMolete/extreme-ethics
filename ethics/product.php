<?php
require_once 'includes/config.php';
require_once 'includes/auth.php'; // for preview mode functions
redirectAdminFromCustomerPages();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProductById($pdo, $id);
if (!$product) {
    header('Location: products.php');
    exit;
}

$needsSize = in_array($product['category'], ['tees', 'hoodies', 'jackets', 'sweaters', 'bottoms', 'sets']);

// Get all images for this product (using naming convention)
$productImages = getProductImages($product['name']);
$mainImage = !empty($productImages) ? $productImages[0] : 'assets/images/products/placeholder.jpg';

// Get related products (same category, exclude current)
$stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category'], $id]);
$related = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isPreview = isPreviewMode();

include 'includes/header.php';
?>

<div class="container">
    <div class="product-detail">
        <!-- Left Column: Image Gallery -->
        <div class="product-images">
            <div class="main-image-container">
                <img src="<?= $mainImage ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-product-image" id="mainProductImage">
            </div>
            <?php if (count($productImages) > 1): ?>
            <div class="thumbnail-gallery">
                <h4>More Views:</h4>
                <div class="thumbnail-grid">
                    <?php foreach ($productImages as $index => $imgPath): ?>
                    <div class="thumbnail-item <?= $index == 0 ? 'active' : '' ?>" data-image="<?= $imgPath ?>">
                        <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Product Info -->
        <div class="product-info">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price">R<?= $product['price'] ?></p>
            <p class="product-description"><?= htmlspecialchars($product['description'] ?: 'Premium quality streetwear from Extreme Ethics.') ?></p>
            
            <?php if ($isPreview): ?>
                <div class="preview-notice">
                    <i class="fas fa-info-circle"></i> You are in preview mode. Add to cart is disabled.
                </div>
            <?php endif; ?>

            <form id="addToCartForm">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <?php if ($needsSize): ?>
                <div class="form-group">
                    <label for="size">Size:</label>
                    <select name="size" id="size" <?= $isPreview ? 'disabled' : '' ?>>
                        <option value="">Select size</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                    </select>
                </div>
                <?php else: ?>
                <input type="hidden" name="size" value="">
                <?php endif; ?>

                <div class="form-group">
                    <label for="color">Color:</label>
                    <select name="color" id="colorSelect" <?= $isPreview ? 'disabled' : '' ?>>
                        <option value="">Select color (optional)</option>
                        <option value="Black">Black</option>
                        <option value="Red">Red</option>
                        <option value="White">White</option>
                        <option value="Blue">Blue</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="10" <?= $isPreview ? 'disabled' : '' ?>>
                </div>

                <?php if (!$isPreview): ?>
                    <button type="submit" class="btn">ADD TO CART</button>
                <?php else: ?>
                    <button type="button" class="btn" style="opacity:0.6; cursor:not-allowed;" disabled>ADD TO CART (Preview)</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- You May Also Like Section -->
    <?php if (!empty($related)): ?>
    <div class="related-products">
        <h2 class="section-title"><span>YOU MAY</span> ALSO LIKE</h2>
        <div class="product-grid">
            <?php foreach ($related as $rel): 
                $relImages = getProductImages($rel['name']);
                $relImage = !empty($relImages) ? $relImages[0] : 'assets/images/products/placeholder.jpg';
            ?>
            <div class="product-card urban-card">
                <img src="<?= $relImage ?>" alt="<?= htmlspecialchars($rel['name']) ?>" class="product-image">
                <h3><a href="product.php?id=<?= $rel['id'] ?>"><?= htmlspecialchars($rel['name']) ?></a></h3>
                <p class="price">R<?= $rel['price'] ?></p>
                <a href="product.php?id=<?= $rel['id'] ?>" class="btn-wa">VIEW DETAILS</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recently Viewed Section -->
    <div class="recently-viewed">
        <h2 class="section-title"><span>RECENTLY</span> VIEWED</h2>
        <div class="product-grid" id="recentlyViewed"></div>
    </div>
</div>

<script>
// Thumbnail click handler
document.querySelectorAll('.thumbnail-item').forEach(item => {
    item.addEventListener('click', function() {
        const newImage = this.dataset.image;
        document.getElementById('mainProductImage').src = newImage;
        document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});

// Add to cart (only if not preview mode)
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    <?php if ($isPreview): ?>
        e.preventDefault();
        alert('Add to cart is disabled in preview mode.');
        return;
    <?php else: ?>
        e.preventDefault();
        const pid = this.product_id.value;
        const size = this.size ? this.size.value : '';
        const color = this.colorSelect ? this.colorSelect.value : '';
        const qty = this.quantity.value;
        let url = `cart.php?action=add&id=${pid}&qty=${qty}`;
        if (size) url += `&size=${encodeURIComponent(size)}`;
        if (color) url += `&color=${encodeURIComponent(color)}`;
        window.location.href = url;
    <?php endif; ?>
});

// Recently viewed (store product info)
const productId = <?= $product['id'] ?>;
const productName = "<?= addslashes($product['name']) ?>";
const productPrice = "<?= $product['price'] ?>";
const productImage = document.getElementById('mainProductImage').src;

// Add to recently viewed (function defined in script.js)
if (typeof addToRecentlyViewed === 'function') {
    addToRecentlyViewed({ id: productId, name: productName, price: productPrice, image: productImage });
}

// Render recently viewed
if (typeof renderRecentlyViewed === 'function') {
    renderRecentlyViewed();
}
</script>

<?php include 'includes/footer.php'; ?>