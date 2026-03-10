<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $phone = trim($_POST['phone']);

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($pdo, $fullname, $username, $email, $password, $phone);
        if ($result === true) {
            // Registration successful – user is already logged in
            header('Location: index.php');
            exit;
        } else {
            $error = $result;
        }
    }
}

include 'includes/header.php';
?>
<div class="container">
    <div class="login-card" style="max-width:450px;">
        <h2>REGISTER</h2>
        <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post">
            <div class="input-group">
                <input type="text" name="fullname" required placeholder=" ">
                <label>Full Name</label>
            </div>
            <div class="input-group">
                <input type="text" name="username" required placeholder=" ">
                <label>Username</label>
            </div>
            <div class="input-group">
                <input type="email" name="email" required placeholder=" ">
                <label>Email</label>
            </div>
            <div class="input-group">
                <input type="tel" name="phone" required placeholder=" ">
                <label>Phone</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" required placeholder=" ">
                <label>Password</label>
            </div>
            <div class="input-group">
                <input type="password" name="confirm" required placeholder=" ">
                <label>Confirm Password</label>
            </div>
            <button type="submit" class="btn" style="width:100%;">Create Account</button>
        </form>
        <div class="register-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>