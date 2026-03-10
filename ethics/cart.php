<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login for any cart access
if (!isLoggedIn()) {
    if (isset($_GET['action']) && $_GET['action'] == 'add') {
        $_SESSION['pending_cart'] = [
            'product_id' => isset($_GET['id']) ? (int)$_GET['id'] : 0,
            'quantity'   => isset($_GET['qty']) ? (int)$_GET['qty'] : 1,
            'size'       => isset($_GET['size']) ? $_GET['size'] : null,
            'color'      => isset($_GET['color']) ? $_GET['color'] : null
        ];
        header('Location: login.php?redirect=cart');
        exit;
    } else {
        header('Location: login.php?redirect=cart');
        exit;
    }
}

// Process pending cart action after login
if (isset($_SESSION['pending_cart'])) {
    $pending = $_SESSION['pending_cart'];
    unset($_SESSION['pending_cart']);
    if ($pending['product_id']) {
        addToCart($pdo, $pending['product_id'], $pending['quantity'], $pending['size'], $pending['color']);
    }
}

include 'includes/header.php';

$items = getCartItems($pdo);
$subtotal_exclusive = getCartTotal($items);
$item_count = array_sum(array_column($items, 'quantity'));
?>

<div class="container">
    <h1>SHOPPING CART</h1>

    <?php if (empty($items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-bag"></i>
            <p>Your cart is empty.</p>
            <a href="products.php" class="btn">CONTINUE SHOPPING</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items">
                <div class="cart-header">
                    <div class="cart-col product">Product</div>
                    <div class="cart-col price">Price</div>
                    <div class="cart-col quantity">Quantity</div>
                    <div class="cart-col subtotal">Subtotal</div>
                    <div class="cart-col action"></div>
                </div>

                <div id="cart-items-container">
                    <?php foreach ($items as $item): 
                        $productImages = getProductImages($item['name']);
                        $image = !empty($productImages) ? $productImages[0] : 'assets/images/products/placeholder.jpg';
                    ?>
                    <div class="cart-item" data-cart-id="<?= $item['id'] ?>" id="cart-item-<?= $item['id'] ?>">
                        <div class="cart-col product">
                            <div class="product-info">
                                <img src="<?= $image ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image">
                                <div>
                                    <h3><a href="product.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h3>
                                    <?php if ($item['size']): ?><p>Size: <?= htmlspecialchars($item['size']) ?></p><?php endif; ?>
                                    <?php if ($item['color']): ?><p>Color: <?= htmlspecialchars($item['color']) ?></p><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="cart-col price" data-price="<?= $item['price'] ?>">
                            R<?= number_format($item['price'], 2) ?>
                        </div>
                        <div class="cart-col quantity">
                            <div class="quantity-form">
                                <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" min="0" data-cart-id="<?= $item['id'] ?>">
                                <button class="update-btn" data-cart-id="<?= $item['id'] ?>">Update</button>
                            </div>
                        </div>
                        <div class="cart-col subtotal" id="subtotal-<?= $item['id'] ?>">
                            R<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>
                        <div class="cart-col action">
                            <a href="#" class="remove-item" data-cart-id="<?= $item['id'] ?>"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-actions">
                    <a href="products.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-line">
                    <span>Subtotal (<?= $item_count ?> items)</span>
                    <span id="summary-subtotal">R<?= number_format($subtotal_exclusive, 2) ?></span>
                </div>
                <div class="summary-line">
                    <span>Estimated VAT (15%)</span>
                    <span id="summary-vat">R<?= number_format($subtotal_exclusive * 0.15, 2) ?></span>
                </div>
                <div class="summary-line">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-line total">
                    <span>Estimated Total</span>
                    <span id="summary-total">R<?= number_format($subtotal_exclusive * 1.15, 2) ?></span>
                </div>
                <p class="shipping-note">Shipping cost will be added based on your location at checkout.</p>
                <a href="checkout.php" class="btn checkout-btn">PROCEED TO CHECKOUT</a>

                <div class="promo-code">
                    <h3>Have a promo code?</h3>
                    <form>
                        <input type="text" placeholder="Enter code">
                        <button type="submit" class="btn-outline">Apply</button>
                    </form>
                    <p class="promo-note">(Not functional – for demonstration)</p>
                </div>
            </div>
        </div>

        <!-- Recently Viewed Section -->
        <div class="recently-viewed">
            <h2 class="section-title"><span>RECENTLY</span> VIEWED</h2>
            <div class="product-grid" id="recentlyViewed"></div>
        </div>
    <?php endif; ?>
</div>

<script>
// AJAX cart automation
document.addEventListener('DOMContentLoaded', function() {
    // Handle update button clicks
    document.querySelectorAll('.update-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.dataset.cartId;
            const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
            const newQty = parseInt(input.value, 10);
            updateCartItem(cartId, newQty);
        });
    });

    // Handle remove clicks
    document.querySelectorAll('.remove-item').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Remove this item?')) {
                const cartId = this.dataset.cartId;
                updateCartItem(cartId, 0); // quantity 0 = remove
            }
        });
    });

    // Also handle Enter key on quantity input (optional)
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const cartId = this.dataset.cartId;
                const newQty = parseInt(this.value, 10);
                updateCartItem(cartId, newQty);
            }
        });
    });

    function updateCartItem(cartId, qty) {
        fetch('cart_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&id=${cartId}&qty=${qty}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in header
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cartCount;
                }

                // Update summary
                document.getElementById('summary-subtotal').textContent = 'R' + data.subtotal;
                document.getElementById('summary-vat').textContent = 'R' + data.estimatedVat;
                document.getElementById('summary-total').textContent = 'R' + data.estimatedTotal;

                // Update or remove item rows
                if (qty <= 0) {
                    // Remove the item row
                    const itemRow = document.getElementById(`cart-item-${cartId}`);
                    if (itemRow) itemRow.remove();

                    // If cart is empty, reload page to show empty state
                    if (data.cartCount === 0) {
                        location.reload();
                    }
                } else {
                    // Update the item's subtotal
                    const subtotalSpan = document.getElementById(`subtotal-${cartId}`);
                    if (subtotalSpan) {
                        // Find the item in data.items that matches cartId
                        const updatedItem = data.items.find(item => item.id == cartId);
                        if (updatedItem) {
                            subtotalSpan.textContent = 'R' + updatedItem.subtotal;
                        }
                    }
                }

                // Optionally update the item count in summary line
                const summaryLine = document.querySelector('.summary-line span:first-child');
                if (summaryLine) {
                    summaryLine.textContent = `Subtotal (${data.cartCount} items)`;
                }
            } else {
                alert('Error updating cart.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

// Recently viewed rendering
if (typeof renderRecentlyViewed === 'function') {
    renderRecentlyViewed();
}
</script>

<style>
/* Cart page styles */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    margin: 2rem 0;
}

/* Cart Items */
.cart-header {
    display: grid;
    grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
    background: var(--lux-black);
    color: #fff;
    padding: 1rem;
    font-weight: 600;
    border-left: 4px solid var(--lux-gold);
    margin-bottom: 1rem;
}
.cart-item {
    display: grid;
    grid-template-columns: 3fr 1fr 1.5fr 1fr 0.5fr;
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #eee;
    padding: 1rem;
    transition: background 0.2s;
}
.cart-item:hover {
    background: #fafafa;
}
.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.cart-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #eee;
}
.product-info h3 {
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
}
.product-info h3 a {
    color: var(--lux-black);
    text-decoration: none;
}
.product-info h3 a:hover {
    color: var(--lux-gold);
}
.product-info p {
    color: #888;
    font-size: 0.9rem;
    margin: 0.1rem 0;
}
.quantity-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.quantity-input {
    width: 60px;
    padding: 0.3rem;
    border: 1px solid #ddd;
    text-align: center;
}
.update-btn {
    background: var(--lux-gold);
    color: #000;
    border: none;
    padding: 0.3rem 0.8rem;
    cursor: pointer;
    font-size: 0.8rem;
    transition: background 0.2s;
}
.update-btn:hover {
    background: #b38f40;
}
.remove-item {
    color: #b71c1c;
    font-size: 1.2rem;
    transition: color 0.2s;
}
.remove-item:hover {
    color: #8b0000;
}
.cart-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-start;
}

/* Cart Summary */
.cart-summary {
    background: #fff;
    padding: 1.5rem;
    border: 1px solid #eee;
    box-shadow: var(--shadow);
    height: fit-content;
}
.cart-summary h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--lux-gold);
    padding-bottom: 0.5rem;
}
.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: #555;
}
.summary-line.total {
    font-weight: 600;
    font-size: 1.2rem;
    border-top: 2px solid #eee;
    padding-top: 1rem;
    margin-top: 0.5rem;
    color: var(--lux-black);
}
.shipping-note {
    color: #888;
    font-size: 0.9rem;
    margin: 1rem 0;
}
.checkout-btn {
    width: 100%;
    text-align: center;
    margin-top: 1rem;
}
.promo-code {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}
.promo-code h3 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
}
.promo-code form {
    display: flex;
    gap: 0.5rem;
}
.promo-code input {
    flex: 1;
    padding: 0.8rem;
    border: 1px solid #ddd;
}
.promo-code button {
    padding: 0.8rem 1.5rem;
}
.promo-note {
    color: #aaa;
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border: 1px solid #eee;
}
.empty-cart i {
    font-size: 4rem;
    color: var(--lux-gold);
    margin-bottom: 1rem;
}
.empty-cart p {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 2rem;
}

/* Responsive */
@media (max-width: 900px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 700px) {
    .cart-header {
        display: none;
    }
    .cart-item {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.5rem;
    }
    .product-info {
        flex-direction: column;
        text-align: center;
    }
    .quantity-form {
        justify-content: center;
    }
    .cart-col {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cart-col::before {
        content: attr(data-label);
        font-weight: 600;
        margin-right: 1rem;
    }
    .cart-col.price::before { content: "Price:"; }
    .cart-col.quantity::before { content: "Quantity:"; }
    .cart-col.subtotal::before { content: "Subtotal:"; }
    .cart-col.action::before { content: ""; }
}
</style>

<?php include 'includes/footer.php'; ?>

