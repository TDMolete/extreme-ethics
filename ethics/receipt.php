<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container">
    <div class="receipt-wrapper">
        <!-- Receipt Header with Logo -->
        <div class="receipt-header">
            <div class="receipt-logo">
                <img src="assets/images/logo/EE-logo.jpg" alt="Extreme Ethics">
                <h1>EXTREME ETHICS</h1>
            </div>
            <p class="receipt-tagline">Wear with confined space</p>
            <div class="receipt-badge">
                <span class="receipt-status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span>
            </div>
        </div>

        <!-- Order Info -->
        <div class="receipt-info-grid">
            <div class="receipt-info-col">
                <h4>Order Number</h4>
                <p>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div class="receipt-info-col">
                <h4>Order Date</h4>
                <p><?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
            </div>
            <div class="receipt-info-col">
                <h4>Payment Method</h4>
                <p>EFT / WhatsApp Order</p>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="receipt-customer">
            <h3>Customer Information</h3>
            <div class="customer-details">
                <p><strong><?= htmlspecialchars($order['fullname']) ?></strong></p>
                <p><?= htmlspecialchars($order['email']) ?></p>
                <p><?= htmlspecialchars($order['phone']) ?></p>
                <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                <p>Delivery Zone: <?= $order['delivery_zone'] == 'soweto' ? 'Within Soweto' : 'Outside Soweto' ?></p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="receipt-items">
            <h3>Order Items</h3>
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Color</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['size'] ?: '-' ?></td>
                        <td><?= $item['color'] ?: '-' ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>R<?= number_format($item['price'], 2) ?></td>
                        <td>R<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Summary -->
        <div class="receipt-summary">
            <div class="summary-line">
                <span>Subtotal (before discount):</span>
                <span>R<?= number_format($order['subtotal'] + $order['discount'], 2) ?></span>
            </div>
            <?php if ($order['discount'] > 0): ?>
            <div class="summary-line discount">
                <span>Discount (5%):</span>
                <span>-R<?= number_format($order['discount'], 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-line">
                <span>Subtotal after discount:</span>
                <span>R<?= number_format($order['subtotal'], 2) ?></span>
            </div>
            <div class="summary-line">
                <span>VAT (15% included):</span>
                <span>R<?= number_format($order['subtotal'] * 15 / 115, 2) ?></span>
            </div>
            <div class="summary-line">
                <span>Delivery:</span>
                <span>R<?= number_format($order['delivery_fee'], 2) ?></span>
            </div>
            <div class="summary-line total">
                <span>Grand Total:</span>
                <span>R<?= number_format($order['total'], 2) ?></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="receipt-actions">
            <button class="btn" id="shareWhatsApp"><i class="fab fa-whatsapp"></i> SHARE RECEIPT</button>
            <a href="products.php" class="btn-outline">CONTINUE SHOPPING</a>
        </div>
    </div>
</div>

<script>
document.getElementById('shareWhatsApp').addEventListener('click', function() {
    let order = `🧾 *EXTREME ETHICS RECEIPT* 🧾%0A`;
    order += `Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>%0A`;
    order += `Date: <?= date('d M Y H:i', strtotime($order['order_date'])) ?>%0A`;
    order += `Status: <?= $order['status'] ?>%0A`;
    order += `----------------------------%0A`;
    order += `Customer: <?= addslashes($order['fullname']) ?>%0A`;
    order += `Email: <?= addslashes($order['email']) ?>%0A`;
    order += `Phone: <?= addslashes($order['phone']) ?>%0A`;
    order += `Address: <?= addslashes(str_replace("\n", ' ', $order['address'])) ?>%0A`;
    order += `----------------------------%0A`;
    order += `ITEMS:%0A`;
    <?php foreach ($items as $item): ?>
    order += `• <?= addslashes($item['name']) ?> | Size: <?= $item['size'] ?: '-' ?> | Color: <?= $item['color'] ?: '-' ?> | Qty: <?= $item['quantity'] ?> | R<?= number_format($item['price'] * $item['quantity'], 2) ?>%0A`;
    <?php endforeach; ?>
    order += `----------------------------%0A`;
    order += `Subtotal (before discount): R<?= number_format($order['subtotal'] + $order['discount'], 2) ?>%0A`;
    <?php if ($order['discount'] > 0): ?>
    order += `Discount (5%): -R<?= number_format($order['discount'], 2) ?>%0A`;
    <?php endif; ?>
    order += `Subtotal after discount: R<?= number_format($order['subtotal'], 2) ?>%0A`;
    order += `VAT (15% included): R<?= number_format($order['subtotal'] * 15 / 115, 2) ?>%0A`;
    order += `Delivery: R<?= number_format($order['delivery_fee'], 2) ?>%0A`;
    order += `TOTAL: R<?= number_format($order['total'], 2) ?>%0A`;
    order += `----------------------------%0A`;
    order += `Thank you for shopping with Extreme Ethics!%0A`;
    order += `Wear with confined space.`;

    window.open(`https://wa.me/27692070042?text=${order}`, '_blank');
});
</script>

<style>
/* Receipt-specific styles */
.receipt-wrapper {
    max-width: 900px;
    margin: 2rem auto;
    background: #fff;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.receipt-header {
    text-align: center;
    border-bottom: 2px solid var(--lux-gold);
    padding-bottom: 1.5rem;
    margin-bottom: 2rem;
}
.receipt-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}
.receipt-logo img {
    max-height: 60px;
}
.receipt-logo h1 {
    font-size: 2rem;
    margin: 0;
    color: var(--lux-black);
}
.receipt-tagline {
    color: #888;
    font-size: 0.9rem;
    letter-spacing: 2px;
    margin-bottom: 1rem;
}
.receipt-badge {
    margin-top: 0.5rem;
}
.receipt-status {
    display: inline-block;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.receipt-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    background: var(--lux-offwhite);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--lux-gold);
}
.receipt-info-col h4 {
    font-size: 0.9rem;
    color: #888;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
}
.receipt-info-col p {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--lux-black);
}

.receipt-customer {
    margin-bottom: 2rem;
}
.receipt-customer h3 {
    font-size: 1.3rem;
    margin-bottom: 1rem;
    color: var(--lux-gold);
}
.customer-details {
    background: #fafafa;
    padding: 1.5rem;
    border: 1px solid #eee;
}
.customer-details p {
    margin: 0.3rem 0;
    color: #555;
}

.receipt-items h3 {
    font-size: 1.3rem;
    margin-bottom: 1rem;
    color: var(--lux-gold);
}
.receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}
.receipt-table th {
    background: var(--lux-black);
    color: #fff;
    padding: 0.8rem;
    text-align: left;
}
.receipt-table td {
    padding: 0.8rem;
    border-bottom: 1px solid #eee;
    color: #555;
}
.receipt-table tr:last-child td {
    border-bottom: none;
}

.receipt-summary {
    width: 350px;
    margin-left: auto;
    margin-top: 2rem;
    background: var(--lux-offwhite);
    padding: 1.5rem;
    border: 1px solid #eee;
}
.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    color: #555;
}
.summary-line.discount {
    color: #28a745;
}
.summary-line.total {
    font-weight: 700;
    font-size: 1.2rem;
    border-top: 2px solid var(--lux-gold);
    padding-top: 1rem;
    margin-top: 0.5rem;
    color: var(--lux-black);
}

.receipt-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 3rem;
}
.receipt-actions .btn-outline {
    border: 2px solid var(--lux-black);
    color: var(--lux-black);
    background: transparent;
    padding: 0.8rem 2rem;
    text-decoration: none;
    transition: var(--transition);
}
.receipt-actions .btn-outline:hover {
    background: var(--lux-black);
    color: #fff;
}

/* Print styles */
@media print {
    .whatsapp-float, .receipt-actions, .preview-banner, header, footer {
        display: none !important;
    }
    .receipt-wrapper {
        box-shadow: none;
        border: none;
        padding: 0;
    }
    body {
        background: #fff;
    }
}

/* Responsive */
@media (max-width: 700px) {
    .receipt-wrapper {
        padding: 1rem;
    }
    .receipt-info-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .receipt-summary {
        width: 100%;
    }
    .receipt-actions {
        flex-direction: column;
        align-items: center;
    }
    .receipt-table {
        font-size: 0.9rem;
    }
    .receipt-table th, .receipt-table td {
        padding: 0.5rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>