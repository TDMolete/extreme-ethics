<?php
require_once 'includes/config.php';
include 'includes/header.php';
redirectAdminFromCustomerPages();
$category = isset($_GET['category']) ? $_GET['category'] : '';
$products = getProducts($pdo, $category);
?>

<div class="container">
    <?php if (!isPreviewMode()): ?>
<a href="product.php?id=<?= $product['id'] ?>" class="btn-wa">VIEW DETAILS</a>
<?php else: ?>
<a href="product.php?id=<?= $product['id'] ?>" class="btn-wa" style="pointer-events: none; opacity: 0.5;">PREVIEW ONLY</a>
<?php endif; ?>
    <h1 class="fade-in">COLLECTIONS</h1>
    
    <!-- Category Filter -->
    <div class="category-filter slide-up">
        <a href="?category=caps" class="filter-btn <?= $category == 'caps' ? 'active' : '' ?>">Caps</a>
        <a href="?category=tees" class="filter-btn <?= $category == 'tees' ? 'active' : '' ?>">Tees</a>
        <a href="?category=hoodies" class="filter-btn <?= $category == 'hoodies' ? 'active' : '' ?>">Hoodies</a>
        <a href="?category=jackets" class="filter-btn <?= $category == 'jackets' ? 'active' : '' ?>">Jackets</a>
        <a href="?category=accessories" class="filter-btn <?= $category == 'accessories' ? 'active' : '' ?>">Accessories</a>
        <a href="?category=bottoms" class="filter-btn <?= $category == 'bottoms' ? 'active' : '' ?>">Bottoms</a>
        <a href="?category=sets" class="filter-btn <?= $category == 'sets' ? 'active' : '' ?>">Sets</a>
        <a href="collections.php" class="filter-btn <?= $category == '' ? 'active' : '' ?>">All</a>
    </div>

    <!-- Size Guide Modal Trigger -->
    <button class="btn size-guide-btn" id="sizeGuideBtn"><i class="fas fa-ruler"></i> SIZE GUIDE</button>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <p class="no-products">No products found in this category.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): 
                $images = getProductImages($product['name']);
                $image = !empty($images) ? $images[0] : 'assets/images/products/placeholder.jpg';
            ?>
            <div class="product-card urban-card" 
                 data-product-id="<?= $product['id'] ?>" 
                 data-product-name="<?= htmlspecialchars($product['name']) ?>" 
                 data-product-price="<?= $product['price'] ?>">
                <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                <h3><a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                <p class="price">R<?= $product['price'] ?></p>
                <a href="product.php?id=<?= $product['id'] ?>" class="btn-wa">VIEW DETAILS</a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Size Guide Modal (reused) -->
<div class="modal" id="sizeGuideModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Size Guide (cm)</h2>
        <table class="size-table">
            <tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th></tr>
            <tr><td>XS</td><td>86-91</td><td>71-76</td><td>86-91</td></tr>
            <tr><td>S</td><td>91-96</td><td>76-81</td><td>91-96</td></tr>
            <tr><td>M</td><td>96-101</td><td>81-86</td><td>96-101</td></tr>
            <tr><td>L</td><td>101-106</td><td>86-91</td><td>101-106</td></tr>
            <tr><td>XL</td><td>106-111</td><td>91-96</td><td>106-111</td></tr>
            <tr><td>XXL</td><td>111-117</td><td>96-102</td><td>111-117</td></tr>
        </table>
        <p>* Measurements are approximate. For oversized fits, size up.</p>
    </div>
</div>

<script>
// Recently viewed tracking
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' || e.target.closest('a')) return;
        const id = this.dataset.productId;
        const name = this.dataset.productName;
        const price = this.dataset.productPrice;
        if (!id) return;
        
        let recent = JSON.parse(localStorage.getItem('recentProducts')) || [];
        recent = recent.filter(p => p.id != id);
        recent.unshift({ id, name, price });
        recent = recent.slice(0, 6);
        localStorage.setItem('recentProducts', JSON.stringify(recent));
    });
});

// Size Guide Modal
const modal = document.getElementById('sizeGuideModal');
const btn = document.getElementById('sizeGuideBtn');
const closeBtn = document.querySelector('.close-modal');

if (btn && modal && closeBtn) {
    btn.addEventListener('click', () => {
        modal.classList.add('show');
    });
    closeBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
}
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' || e.target.closest('a')) return;
        const id = this.dataset.productId;
        const name = this.dataset.productName;
        const price = this.dataset.productPrice;
        const image = this.querySelector('img')?.src || 'assets/images/products/placeholder.jpg';
        if (!id) return;
        addToRecentlyViewed({ id, name, price, image });
    });
});
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' || e.target.closest('a')) return;
        const id = this.dataset.productId;
        const name = this.dataset.productName;
        const price = this.dataset.productPrice;
        const image = this.querySelector('img')?.src || 'assets/images/products/placeholder.jpg';
        if (!id) return;
        addToRecentlyViewed({ id, name, price, image });
    });
});
</script>
 <script src="/extreme/ethics/assets/js/script.js"></script>
    <script src="/extreme/ethics/assets/js/animations.js"></script>
<?php include 'includes/footer.php'; ?>