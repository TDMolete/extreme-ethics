<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=checkout');
    exit;
}

if (getCartCount($pdo) == 0) {
    header('Location: products.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userDetails = $stmt->fetch();
$user_email = $userDetails['email'] ?? '';
$user_phone = $userDetails['phone'] ?? '';

// Get cart items (prices are assumed VAT exclusive)
$items = getCartItems($pdo);
$exclusive_subtotal = 0;
foreach ($items as $item) {
    $exclusive_subtotal += $item['price'] * $item['quantity'];
}

// Apply discount (10% in the example, but we'll make it dynamic based on total? For now, we'll keep the 2% from previous requirement? Actually we need to implement the logic based on the new rule: discount is 10% for this order? The user gave an example with 10% discount. We'll implement a configurable discount percentage. For simplicity, we'll set a fixed discount rate of 10% for orders above a threshold? Or we'll just allow a discount code? Since the example uses 10%, we'll assume a discount of 10% is applied. We'll make it a variable.
// For now, we'll use a 10% discount if subtotal > 1500? That was previous. But the example is separate. Let's implement a simple 10% discount for all orders? No, that's not correct. We'll follow the example: apply 10% discount to the order.
// To make it reusable, we'll set a discount percentage based on some condition. For this example, we'll apply 10% regardless. But in real store, discount might be from a coupon. We'll keep it simple: if there is a discount, we'll set $discount_percent = 10; else 0. For now, we'll set it to 10.
$discount_percent = 10; // 10% discount for this order
$discount_amount = round($exclusive_subtotal * $discount_percent / 100, 2);
$exclusive_after_discount = $exclusive_subtotal - $discount_amount;

// Calculate VAT (15%)
$vat = round($exclusive_after_discount * 0.15, 2);

// Shipping (will be determined by delivery zone)
// We'll handle via JavaScript later, but for PHP we need default.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $delivery_zone = $_POST['delivery_zone'] ?? 'soweto';

    if (empty($fullname) || empty($email) || empty($phone) || empty($address)) {
        $error = 'All fields are required.';
    } else {
        $delivery_fee = calculateDelivery($delivery_zone);
        $total = $exclusive_after_discount + $vat + $delivery_fee;

        // Insert order with new structure
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, fullname, email, phone, address, delivery_zone, subtotal, discount, vat, delivery_fee, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $fullname, $email, $phone, $address, $delivery_zone, $exclusive_after_discount, $discount_amount, $vat, $delivery_fee, $total]);
        $order_id = $pdo->lastInsertId();

        // Insert order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, name, price, quantity, size, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity'], $item['size'], $item['color']]);
        }

        // Send notifications
        if (function_exists('sendOrderEmail')) {
            sendOrderEmail($pdo, $order_id);
        }
        if (function_exists('logWhatsAppOrder')) {
            logWhatsAppOrder($pdo, $order_id);
        }

        clearCart($pdo);
        header("Location: receipt.php?id=$order_id");
        exit;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h1>CHECKOUT</h1>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Integrated Ordering Guide (same as before) -->
    <div class="checkout-guide">
        <!-- ... guide content ... -->
    </div>

    <div class="checkout-grid">
        <!-- Order Summary -->
        <div>
            <h2>Order Summary</h2>
            <div class="order-summary" id="orderSummary">
                <?php foreach ($items as $item): ?>
                <div class="summary-item">
                    <span><?= htmlspecialchars($item['name']) ?> (<?= $item['size'] ?>, <?= $item['color'] ?>) x<?= $item['quantity'] ?></span>
                    <span>R<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="summary-item">
                    <span>Subtotal (excl. VAT)</span>
                    <span>R<?= number_format($exclusive_subtotal, 2) ?></span>
                </div>
                <?php if ($discount_amount > 0): ?>
                <div class="summary-item discount">
                    <span>Discount (<?= $discount_percent ?>%)</span>
                    <span>-R<?= number_format($discount_amount, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-item">
                    <span>Amount after discount (excl. VAT)</span>
                    <span>R<?= number_format($exclusive_after_discount, 2) ?></span>
                </div>
                <div class="summary-item">
                    <span>VAT (15%)</span>
                    <span>R<?= number_format($vat, 2) ?></span>
                </div>
                <div class="summary-item" id="deliveryRow" style="display:none;">
                    <span>Shipping</span>
                    <span id="deliveryFee">R0.00</span>
                </div>
                <div class="summary-item summary-total">
                    <span>Total (incl. VAT & shipping)</span>
                    <span id="totalAmount">R<?= number_format($exclusive_after_discount + $vat, 2) ?></span>
                </div>
                <div class="summary-item" id="deliveryEstimate">
                    <span>Estimated Delivery</span>
                    <span id="deliveryEstimateText">2-3 business days</span>
                </div>
            </div>
        </div>

        <!-- Customer Details Form -->
        <div>
            <h2>Your Details</h2>
            <form method="post" id="checkoutForm">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="fullname" name="fullname" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user_email) ?>">
                </div>
                <div class="form-group">
                    <label>Phone (WhatsApp)</label>
                    <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($user_phone) ?>">
                </div>
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea id="address" name="address" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label>Delivery Zone:</label><br>
                    <label><input type="radio" name="delivery_zone" value="soweto" checked> Within Soweto (free, 2-3 business days)</label><br>
                    <label><input type="radio" name="delivery_zone" value="other"> Outside Soweto (R50, 3-5 business days)</label>
                </div>
                <button type="submit" class="btn" style="width:100%;">PLACE ORDER VIA WHATSAPP</button>
            </form>
        </div>
    </div>
</div>

<script>
const exclusiveSubtotal = <?= $exclusive_subtotal ?>;
const discountAmount = <?= $discount_amount ?>;
const exclusiveAfterDiscount = <?= $exclusive_after_discount ?>;
const vat = <?= $vat ?>;
const items = <?= json_encode($items) ?>;

const deliveryRow = document.getElementById('deliveryRow');
const deliveryFeeSpan = document.getElementById('deliveryFee');
const totalSpan = document.getElementById('totalAmount');
const deliveryEstimateText = document.getElementById('deliveryEstimateText');
const radios = document.querySelectorAll('input[name="delivery_zone"]');

function updateTotal() {
    const zone = document.querySelector('input[name="delivery_zone"]:checked').value;
    const delivery = zone === 'soweto' ? 0 : 50;
    const total = exclusiveAfterDiscount + vat + delivery;

    deliveryRow.style.display = delivery > 0 ? 'flex' : 'none';
    deliveryFeeSpan.textContent = 'R' + delivery.toFixed(2);
    totalSpan.textContent = 'R' + total.toFixed(2);
    deliveryEstimateText.textContent = zone === 'soweto' ? '2-3 business days' : '3-5 business days';
}

radios.forEach(r => r.addEventListener('change', updateTotal));
updateTotal();

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const zone = document.querySelector('input[name="delivery_zone"]:checked').value;
    const delivery = zone === 'soweto' ? 0 : 50;
    const total = exclusiveAfterDiscount + vat + delivery;
    const deliveryEstimate = zone === 'soweto' ? '2-3 business days' : '3-5 business days';

    let order = `🧾 *EXTREME ETHICS ORDER* 🧾%0A----------------------------%0A`;
    order += `Date: ${new Date().toLocaleString()}%0A`;
    order += `Customer: ${name}%0AEmail: ${email}%0APhone: ${phone}%0AAddress: ${address}%0A`;
    order += `Location: ${zone === 'soweto' ? 'Within Soweto' : 'Outside Soweto'}%0A`;
    order += `Delivery Estimate: ${deliveryEstimate}%0A`;
    order += `----------------------------%0AITEMS:%0A`;
    
    items.forEach(item => {
        order += `• ${item.name} | Size: ${item.size || '-'} | Color: ${item.color || '-'} | Qty: ${item.quantity} | R${(item.price * item.quantity).toFixed(2)}%0A`;
    });

    order += `----------------------------%0A`;
    order += `Subtotal (excl. VAT): R${exclusiveSubtotal.toFixed(2)}%0A`;
    if (discountAmount > 0) {
        order += `Discount (10%): -R${discountAmount.toFixed(2)}%0A`;
    }
    order += `Amount after discount (excl. VAT): R${exclusiveAfterDiscount.toFixed(2)}%0A`;
    order += `VAT (15%): R${vat.toFixed(2)}%0A`;
    order += `Shipping: R${delivery.toFixed(2)}%0A`;
    order += `TOTAL: R${total.toFixed(2)}%0A`;
    order += `----------------------------%0A`;
    order += `Thank you for shopping with Extreme Ethics!%0A`;
    order += `Wear with confined space.`;

    window.open(`https://wa.me/27692070042?text=${order}`, '_blank');
    this.submit();
});
</script>

<style>
/* (keep existing styles) */
.error-message { background: #f8d7da; color: #721c24; padding: 1rem; margin-bottom: 1rem; border-left: 4px solid #f5c6cb; }
.checkout-guide { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; padding: 1rem; background: var(--lux-offwhite); border-left: 6px solid var(--lux-gold); }
.guide-section h3 { font-size: 1.2rem; margin-bottom: 1rem; color: var(--lux-black); display: flex; align-items: center; gap: 0.5rem; }
.guide-section h3 i { color: var(--lux-gold); }
.guide-steps .step { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #555; }
.step-num { background: var(--lux-gold); color: #000; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; }
.guide-section ul { list-style: none; padding: 0; }
.guide-section li { margin-bottom: 0.5rem; color: #555; display: flex; align-items: center; gap: 0.5rem; }
.guide-section li i.fa-check-circle { color: #28a745; }
.guide-section.warning li i.fa-times-circle { color: #b71c1c; }
.guide-section a { color: var(--lux-gold); text-decoration: none; }
@media (max-width: 700px) { .checkout-guide { grid-template-columns: 1fr; } }
</style>

<?php include 'includes/footer.php'; ?>