<?php
function getCartCount($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn() ?: 0;
    } else {
        $session_id = session_id();
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ?");
        $stmt->execute([$session_id]);
        return $stmt->fetchColumn() ?: 0;
    }
}

function getProducts($pdo, $category = null) {
    if ($category) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY name");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
/**
 * Get all images for a product based on its name
 * @param string $productName The product name (e.g., "Beanie")
 * @return array Array of image file paths
 */
function getProductImages($productName) {
    $basePath = 'assets/images/products/';
    $files = glob($basePath . '*');
    // Normalize product name: remove spaces, lowercase
    $searchName = strtolower(str_replace(' ', '', $productName));
    $pattern = '/^' . preg_quote($searchName, '/') . '\d*\.(jpg|jpeg|png|gif)$/i';
    $matches = [];
    foreach ($files as $file) {
        if (preg_match($pattern, basename($file))) {
            $matches[] = $file;
        }
    }
    sort($matches); // Ensure consistent order
    return $matches;
}
function addToCart($pdo, $product_id, $quantity = 1, $size = null, $color = null) {
    $user_id = $_SESSION['user_id'] ?? null;
    $session_id = $user_id ? null : session_id();

    // Check if same item (with same size/color) already exists
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size <=> ? AND color <=> ?");
        $stmt->execute([$user_id, $product_id, $size, $color]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE session_id = ? AND product_id = ? AND size <=> ? AND color <=> ?");
        $stmt->execute([$session_id, $product_id, $size, $color]);
    }
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?")->execute([$quantity, $existing['id']]);
    } else {
        if ($user_id) {
            $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)")
                ->execute([$user_id, $product_id, $quantity, $size, $color]);
        } else {
            $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)")
                ->execute([$session_id, $product_id, $quantity, $size, $color]);
        }
    }
}

function getCartItems($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.id as product_id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $session_id = session_id();
        $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.id as product_id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ?");
        $stmt->execute([$session_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCartTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function clearCart($pdo) {
    if (isset($_SESSION['user_id'])) {
        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
    } else {
        $pdo->prepare("DELETE FROM cart WHERE session_id = ?")->execute([session_id()]);
    }
}

function calculateDelivery($location) {
    return ($location === 'soweto') ? 0 : 50;
}
/**
 * Send order receipt to admin email
 */
function sendOrderEmail($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$order_id]);
    $items = $itemsStmt->fetchAll();

    $to = ADMIN_EMAIL;
    $subject = "New Order #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
    
    $message = "<h1>New Order Received</h1>";
    $message .= "<p><strong>Customer:</strong> " . htmlspecialchars($order['fullname']) . "</p>";
    $message .= "<p><strong>Email:</strong> " . htmlspecialchars($order['email']) . "</p>";
    $message .= "<p><strong>Phone:</strong> " . htmlspecialchars($order['phone']) . "</p>";
    $message .= "<p><strong>Address:</strong> " . nl2br(htmlspecialchars($order['address'])) . "</p>";
    $message .= "<p><strong>Delivery Zone:</strong> " . ($order['delivery_zone'] == 'soweto' ? 'Within Soweto' : 'Outside Soweto') . "</p>";
    $message .= "<h2>Items</h2>";
    $message .= "<table border='1' cellpadding='5'><tr><th>Product</th><th>Size</th><th>Color</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>";
    foreach ($items as $item) {
        $message .= "<tr>";
        $message .= "<td>" . htmlspecialchars($item['name']) . "</td>";
        $message .= "<td>" . ($item['size'] ?: '-') . "</td>";
        $message .= "<td>" . ($item['color'] ?: '-') . "</td>";
        $message .= "<td>" . $item['quantity'] . "</td>";
        $message .= "<td>R" . number_format($item['price'], 2) . "</td>";
        $message .= "<td>R" . number_format($item['price'] * $item['quantity'], 2) . "</td>";
        $message .= "</tr>";
    }
    $message .= "</table>";
    $message .= "<p><strong>Subtotal:</strong> R" . number_format($order['subtotal'], 2) . "</p>";
    $message .= "<p><strong>Delivery:</strong> R" . number_format($order['delivery_fee'], 2) . "</p>";
    $message .= "<p><strong>Total:</strong> R" . number_format($order['total'], 2) . "</p>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@extremeethics.co.za\r\n";

    return mail($to, $subject, $message, $headers);
}

/**
 * Simulate sending WhatsApp to admin (logs to file)
 * Replace with actual WhatsApp API call if available
 */
function logWhatsAppOrder($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$order_id]);
    $items = $itemsStmt->fetchAll();

    $message = "New Order #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT) . "\n";
    $message .= "Customer: " . $order['fullname'] . "\n";
    $message .= "Email: " . $order['email'] . "\n";
    $message .= "Phone: " . $order['phone'] . "\n";
    $message .= "Address: " . $order['address'] . "\n";
    $message .= "Delivery Zone: " . ($order['delivery_zone'] == 'soweto' ? 'Within Soweto' : 'Outside Soweto') . "\n";
    $message .= "Items:\n";
    foreach ($items as $item) {
        $message .= "- " . $item['name'] . " | Size: " . ($item['size'] ?: '-') . " | Color: " . ($item['color'] ?: '-') . " | Qty: " . $item['quantity'] . " | R" . number_format($item['price'] * $item['quantity'], 2) . "\n";
    }
    $message .= "Subtotal: R" . number_format($order['subtotal'], 2) . "\n";
    $message .= "Delivery: R" . number_format($order['delivery_fee'], 2) . "\n";
    $message .= "Total: R" . number_format($order['total'], 2) . "\n";

    // Log to file (simulate WhatsApp)
    $logFile = __DIR__ . '/../logs/whatsapp_orders.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] To: " . ADMIN_WHATSAPP . "\n$message\n---\n", FILE_APPEND);

    return true;
}
?>