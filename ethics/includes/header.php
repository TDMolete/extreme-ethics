<?php
$cartCount = getCartCount($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Extreme Ethics · Luxury Streetwear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/extreme/ethics/assets/css/styles.css">
</head>
<body>
    <?php if (isLoggedIn() && isAdmin() && isPreviewMode()): ?>
        <div class="preview-banner">
            <div class="preview-container">
                <span><i class="fas fa-eye"></i> You are in preview mode – browsing as a customer. Purchasing is disabled.</span>
                <a href="exit_preview.php" class="preview-exit-btn">Exit Preview</a>
            </div>
        </div>
    <?php endif; ?>

    <a href="https://wa.me/27692070042" class="whatsapp-float" target="_blank"><i class="fab fa-whatsapp"></i></a>

    <header class="luxury-header">
        <div class="header-container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo/EE-logo.jpg" alt="Extreme Ethics">
                    <span>EXTREME ETHICS</span>
                </a>
            </div>

            <div class="header-right">
                <!-- Desktop Navigation -->
                <nav class="desktop-nav">
                    <ul class="nav-links">
                        <li><a href="index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">HOME</a></li>
                        <li><a href="products.php" class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">PRODUCTS</a></li>
                        <li><a href="collections.php" class="<?= $currentPage == 'collections.php' ? 'active' : '' ?>">COLLECTIONS</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="logout.php">LOGOUT</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">LOGIN</a></li>
                            <li><a href="signup.php" class="<?= $currentPage == 'signup.php' ? 'active' : '' ?>">REGISTER</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Icons -->
                <div class="header-icons">
                    <div class="search-icon" id="searchToggle">
                        <i class="fas fa-search"></i>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="user-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <?php endif; ?>
                    <div class="cart-icon">
                        <a href="cart.php">
                            <i class="fas fa-shopping-bag"></i>
                            <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?= $cartCount ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar with Autocomplete -->
        <div class="search-bar" id="searchBar">
            <div class="search-container">
                <form action="search.php" method="GET" id="searchForm" autocomplete="off">
                    <input type="text" name="q" id="searchInput" placeholder="Search products..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <div class="autocomplete-suggestions" id="autocompleteSuggestions"></div>
            </div>
        </div>

        <!-- Mobile Navigation (Slide-out) -->
        <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
        <div class="mobile-nav" id="mobileNav">
            <div class="mobile-nav-header">
                <div class="logo">EXTREME ETHICS</div>
                <div class="close-mobile" id="closeMobile"><i class="fas fa-times"></i></div>
            </div>
            <ul class="mobile-nav-links">
                <li><a href="index.php">HOME</a></li>
                <li><a href="products.php">PRODUCTS</a></li>
                <li><a href="collections.php">COLLECTIONS</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">LOGOUT (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">LOGIN</a></li>
                    <li><a href="signup.php">REGISTER</a></li>
                <?php endif; ?>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="contact.php">CONTACT</a></li>
                <li><a href="size-guide.php">SIZE GUIDE</a></li>
            </ul>
        </div>
    </header>

    <main>