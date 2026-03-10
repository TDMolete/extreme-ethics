<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Only allow logged-in users
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$cart_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$quantity = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

$response = ['success' => false];

if ($action === 'update' && $cart_id > 0) {
    if ($quantity <= 0) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    }
    $response['success'] = true;
} elseif ($action === 'remove' && $cart_id > 0) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $response['success'] = true;
}

if ($response['success']) {
    // Fetch updated cart data
    $items = getCartItems($pdo);
    $subtotal = getCartTotal($items);
    $cartCount = getCartCount($pdo);
    $itemRows = [];

    foreach ($items as $item) {
        $productImages = getProductImages($item['name']);
        $image = !empty($productImages) ? $productImages[0] : 'assets/images/products/placeholder.jpg';
        $itemRows[] = [
            'id' => $item['id'],
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'price' => number_format($item['price'], 2),
            'quantity' => $item['quantity'],
            'subtotal' => number_format($item['price'] * $item['quantity'], 2),
            'size' => $item['size'],
            'color' => $item['color'],
            'image' => $image,
            'delete_url' => 'cart.php?action=remove&id=' . $item['id']
        ];
    }

    $response['items'] = $itemRows;
    $response['subtotal'] = number_format($subtotal, 2);
    $response['cartCount'] = $cartCount;
    $response['estimatedVat'] = number_format($subtotal * 0.15, 2);
    $response['estimatedTotal'] = number_format($subtotal * 1.15, 2);
}

header('Content-Type: application/json');
echo json_encode($response);
?>