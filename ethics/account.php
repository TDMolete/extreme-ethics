<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'customer';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Handle profile update
$profile_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);

    // Check if email/username already exists for another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
    $stmt->execute([$email, $username, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['account_error'] = 'Email or username already in use.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, username = ? WHERE id = ?");
        $stmt->execute([$fullname, $email, $phone, $username, $user_id]);
        $_SESSION['user_name'] = $fullname;
        $_SESSION['username'] = $username;
        $_SESSION['account_success'] = 'Profile updated successfully.';
    }
    header('Location: account.php?tab=profile');
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!password_verify($current, $user['password'])) {
        $_SESSION['account_error'] = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
        $_SESSION['account_error'] = 'New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $_SESSION['account_error'] = 'Password must be at least 6 characters.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        $_SESSION['account_success'] = 'Password changed successfully.';
    }
    header('Location: account.php?tab=profile');
    exit;
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch orders
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$orderStmt->execute([$user_id]);
$orders = $orderStmt->fetchAll();

// Get messages from session
$success = $_SESSION['account_success'] ?? '';
$error = $_SESSION['account_error'] ?? '';
unset($_SESSION['account_success'], $_SESSION['account_error']);

include 'includes/header.php';
?>

<div class="account-container">
    <!-- Sidebar -->
    <div class="account-sidebar">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3><?= htmlspecialchars($user['fullname']) ?></h3>
            <p class="user-role"><?= ucfirst($user['role']) ?></p>
        </div>
        <ul class="account-nav">
            <li><a href="account.php?tab=overview" class="<?= $tab == 'overview' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="account.php?tab=profile" class="<?= $tab == 'profile' ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="account.php?tab=orders" class="<?= $tab == 'orders' ? 'active' : '' ?>"><i class="fas fa-shopping-bag"></i> Orders</a></li>
            <?php if ($role == 'admin'): ?>
            <li><a href="admin.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
            <li><a href="index.php?preview=1"><i class="fas fa-eye"></i> Preview Store</a></li>
            <?php endif; ?>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="account-content">
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($tab == 'overview'): ?>
            <h1>Welcome, <?= htmlspecialchars($user['fullname']) ?></h1>
            <div class="overview-stats">
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <div>
                        <span class="stat-value"><?= count($orders) ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
                <?php
                $totalSpent = 0;
                foreach ($orders as $order) $totalSpent += $order['total'];
                ?>
                <div class="stat-card">
                    <i class="fas fa-rand"></i>
                    <div>
                        <span class="stat-value">R<?= number_format($totalSpent, 2) ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                </div>
                <?php if ($role == 'admin'): ?>
                <div class="stat-card admin-highlight">
                    <i class="fas fa-crown"></i>
                    <div>
                        <span class="stat-value">Admin</span>
                        <span class="stat-label"><a href="admin.php">Go to Admin Panel →</a></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <h2>Recent Orders</h2>
            <?php if (empty($orders)): ?>
                <p>You haven't placed any orders yet. <a href="products.php">Start shopping</a>.</p>
            <?php else: ?>
                <div class="recent-orders">
                    <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <span class="order-date"><?= date('d M Y', strtotime($order['order_date'])) ?></span>
                            <span class="status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span>
                        </div>
                        <div class="order-body">
                            <p><strong>Total:</strong> R<?= number_format($order['total'], 2) ?></p>
                            <a href="receipt.php?id=<?= $order['id'] ?>" class="btn-small">View Receipt</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($orders) > 3): ?>
                <a href="account.php?tab=orders" class="btn-outline">View All Orders</a>
                <?php endif; ?>
            <?php endif; ?>

        <?php elseif ($tab == 'profile'): ?>
            <h1>Profile Settings</h1>
            <div class="profile-section">
                <h2>Edit Profile</h2>
                <form method="post" class="profile-form">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn">Update Profile</button>
                </form>
            </div>

            <div class="profile-section">
                <h2>Change Password</h2>
                <form method="post" class="profile-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn">Change Password</button>
                </form>
            </div>

        <?php elseif ($tab == 'orders'): ?>
            <h1>Order History</h1>
            <?php if (empty($orders)): ?>
                <p>You haven't placed any orders yet. <a href="products.php">Start shopping</a>.</p>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <span class="order-date"><?= date('d M Y', strtotime($order['order_date'])) ?></span>
                            <span class="status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span>
                        </div>
                        <div class="order-body">
                            <p><strong>Items:</strong> <?= $order['subtotal'] ?> (subtotal excl. VAT)</p>
                            <p><strong>VAT:</strong> R<?= number_format($order['vat'] ?? 0, 2) ?></p>
                            <p><strong>Delivery:</strong> R<?= number_format($order['delivery_fee'], 2) ?></p>
                            <p><strong>Total:</strong> R<?= number_format($order['total'], 2) ?></p>
                            <a href="receipt.php?id=<?= $order['id'] ?>" class="btn-small">View Receipt</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* Account page specific styles */
.account-container {
    display: flex;
    min-height: 80vh;
    background: #f9f9f9;
}
.account-sidebar {
    width: 280px;
    background: #fff;
    border-right: 1px solid #eee;
    padding: 2rem 1rem;
}
.user-info {
    text-align: center;
    margin-bottom: 2rem;
}
.user-avatar {
    font-size: 4rem;
    color: var(--lux-gold);
    margin-bottom: 1rem;
}
.user-info h3 {
    font-size: 1.3rem;
    margin-bottom: 0.2rem;
}
.user-role {
    color: #888;
    font-size: 0.9rem;
    text-transform: uppercase;
}
.account-nav {
    list-style: none;
    padding: 0;
}
.account-nav li {
    margin-bottom: 0.3rem;
}
.account-nav a {
    display: block;
    padding: 0.8rem 1rem;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
}
.account-nav a i {
    width: 25px;
    color: var(--lux-gold);
}
.account-nav a:hover,
.account-nav a.active {
    background: var(--lux-offwhite);
    color: var(--lux-gold);
}
.account-content {
    flex: 1;
    padding: 2rem;
}
.message {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}
.message.success {
    background: #e0f2e0;
    color: #2e7d32;
    border-left: 4px solid #2e7d32;
}
.message.error {
    background: #fde0e0;
    color: #b71c1c;
    border-left: 4px solid #b71c1c;
}
.overview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}
.stat-card {
    background: #fff;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.stat-card i {
    font-size: 2.5rem;
    color: var(--lux-gold);
}
.stat-value {
    font-size: 1.8rem;
    font-weight: 600;
    display: block;
}
.stat-label {
    color: #888;
    font-size: 0.9rem;
}
.admin-highlight {
    background: linear-gradient(135deg, var(--lux-gold) 0%, #d4af37 100%);
}
.admin-highlight i {
    color: #000;
}
.admin-highlight a {
    color: #000;
    text-decoration: underline;
}
.recent-orders,
.orders-list {
    margin: 1rem 0;
}
.order-card {
    background: #fff;
    border: 1px solid #eee;
    margin-bottom: 1rem;
    padding: 1rem;
    border-radius: 8px;
}
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.8rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed #ddd;
}
.order-id {
    font-weight: 600;
    color: var(--lux-gold);
}
.order-date {
    color: #888;
    font-size: 0.9rem;
}
.order-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.order-body p {
    margin: 0;
}
.profile-section {
    background: #fff;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #eee;
}
.profile-section h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}
.profile-form .form-group {
    margin-bottom: 1.5rem;
}
.profile-form label {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 500;
    color: #555;
}
.profile-form input {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    font-family: var(--font-body);
}
.profile-form input:focus {
    outline: none;
    border-color: var(--lux-gold);
}
.btn-small {
    background: var(--lux-gold);
    color: #000;
    padding: 0.3rem 1rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-block;
}
@media (max-width: 700px) {
    .account-container {
        flex-direction: column;
    }
    .account-sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #eee;
    }
    .account-content {
        padding: 1rem;
    }
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
    }
    .order-body {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include 'includes/footer.php'; ?>