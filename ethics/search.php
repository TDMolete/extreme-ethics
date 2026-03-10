<?php
require_once 'includes/config.php';
redirectAdminFromCustomerPages();
include 'includes/header.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

$results = [];
$categories = [];

if (!empty($query)) {
    // Base query
    $sql = "SELECT * FROM products WHERE (name LIKE :q OR description LIKE :q)";
    $params = ['q' => "%$query%"];

    // Category filter
    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }

    // Sorting
    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY price DESC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY name DESC";
            break;
        case 'name_asc':
        default:
            $sql .= " ORDER BY name ASC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all categories for filter dropdown
    $catStmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="container">
    <h1 class="fade-in">Search Results</h1>
    <p class="search-query">Showing results for: <strong>"<?= htmlspecialchars($query) ?>"</strong></p>

    <?php if (!empty($results)): ?>
        <!-- Filter Bar -->
        <div class="search-filters">
            <form method="GET" action="search.php" class="filter-form">
                <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
                
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category == $cat ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($cat)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort">
                        <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                        <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                    </select>
                </div>

                <button type="submit" class="btn filter-btn">Apply Filters</button>
            </form>
        </div>

        <!-- Results Grid -->
        <div class="product-grid">
            <?php foreach ($results as $product): 
                $images = getProductImages($product['name']);
                $image = !empty($images) ? $images[0] : 'assets/images/products/placeholder.jpg';
            ?>
            <div class="product-card urban-card">
                <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                <h3><a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                <p class="price">R<?= $product['price'] ?></p>
                <a href="product.php?id=<?= $product['id'] ?>" class="btn-wa">VIEW DETAILS</a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <p>No products found matching your search.</p>
            <a href="products.php" class="btn">BROWSE ALL PRODUCTS</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>