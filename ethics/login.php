<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin.php');
    } else {
        // If there's a pending cart action, process it (for customers)
        if (isset($_SESSION['pending_cart'])) {
            $pending = $_SESSION['pending_cart'];
            unset($_SESSION['pending_cart']);
            if ($pending['product_id']) {
                addToCart($pdo, $pending['product_id'], $pending['quantity'], $pending['size'], $pending['color']);
            }
        }
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
        header("Location: $redirect");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $result = loginUser($pdo, $email, $password);

    if ($result === true) {
        // Login successful – determine role
        if (isAdmin()) {
            header('Location: admin.php');
        } else {
            // Customer: handle pending cart
            if (isset($_SESSION['pending_cart'])) {
                $pending = $_SESSION['pending_cart'];
                unset($_SESSION['pending_cart']);
                if ($pending['product_id']) {
                    addToCart($pdo, $pending['product_id'], $pending['quantity'], $pending['size'], $pending['color']);
                }
            }
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: $redirect");
        }
        exit;
    } else {
        $error = $result;
    }
}

include 'includes/header.php';
?>
<div class="container">
    <div class="login-card">
        <h2>LOGIN</h2>
        <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post">
            <div class="input-group">
                <input type="email" name="email" required placeholder=" ">
                <label>Email</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" required placeholder=" ">
                <label>Password</label>
            </div>
            <button type="submit" class="btn" style="width:100%;">Sign In</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="signup.php">Register</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>