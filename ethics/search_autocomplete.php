<?php
require_once 'includes/config.php';
redirectAdminFromCustomerPages();

if (isset($_GET['term'])) {
    $term = '%' . $_GET['term'] . '%';
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE name LIKE ? OR description LIKE ? LIMIT 5");
    $stmt->execute([$term, $term]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($results);
} else {
    echo json_encode([]);
}
?>
