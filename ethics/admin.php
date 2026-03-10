<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Only admin can access this page
requireAdmin();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Users
    if (isset($_POST['add_user'])) {
        $fullname = trim($_POST['fullname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $stmt = $pdo->prepare("INSERT INTO users (fullname, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $username, $email, $phone, $password, $role]);
        header('Location: admin.php?page=users');
        exit;
    }
    if (isset($_POST['edit_user'])) {
        $id = (int)$_POST['id'];
        $fullname = trim($_POST['fullname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $role = $_POST['role'];
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET fullname=?, username=?, email=?, phone=?, role=?, password=? WHERE id=?");
            $stmt->execute([$fullname, $username, $email, $phone, $role, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET fullname=?, username=?, email=?, phone=?, role=? WHERE id=?");
            $stmt->execute([$fullname, $username, $email, $phone, $role, $id]);
        }
        header('Location: admin.php?page=users');
        exit;
    }
    if (isset($_POST['delete_user'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role!='admin'");
        $stmt->execute([$id]);
        header('Location: admin.php?page=users');
        exit;
    }

    // Products
    if (isset($_POST['add_product'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category = $_POST['category'];
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $category]);
        header('Location: admin.php?page=products');
        exit;
    }
    if (isset($_POST['edit_product'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category = $_POST['category'];
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category=? WHERE id=?");
        $stmt->execute([$name, $description, $price, $category, $id]);
        header('Location: admin.php?page=products');
        exit;
    }
    if (isset($_POST['delete_product'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
        $stmt->execute([$id]);
        header('Location: admin.php?page=products');
        exit;
    }

    // Orders
    if (isset($_POST['update_order_status'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->execute([$status, $id]);
        header('Location: admin.php?page=orders');
        exit;
    }

    // Settings
    if (isset($_POST['update_settings'])) {
        $admin_email = trim($_POST['admin_email']);
        $admin_whatsapp = trim($_POST['admin_whatsapp']);
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_email'");
        $stmt->execute([$admin_email]);
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_whatsapp'");
        $stmt->execute([$admin_whatsapp]);
        header('Location: admin.php?page=settings&saved=1');
        exit;
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-container">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-logo">EXTREME ETHICS</div>
        <div class="admin-user">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <span class="admin-role">Admin</span>
        </div>
        <ul class="admin-nav">
            <li><a href="admin.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin.php?page=users" class="<?= $page == 'users' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin.php?page=products" class="<?= $page == 'products' ? 'active' : '' ?>"><i class="fas fa-tshirt"></i> Products</a></li>
            <li><a href="admin.php?page=orders" class="<?= $page == 'orders' ? 'active' : '' ?>"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="admin.php?page=settings" class="<?= $page == 'settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="index.php?preview=1" class="preview-link"><i class="fas fa-eye"></i> Preview Store</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <h1><?= ucfirst($page) ?> Management</h1>
            <?php if ($page != 'dashboard' && $page != 'settings' && $action != 'view'): ?>
                <?php if ($action == ''): ?>
                <a href="admin.php?page=<?= $page ?>&action=add" class="btn-small">Add New</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php
        // ========== DASHBOARD ==========
        if ($page == 'dashboard'):
            $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $revenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn() ?: 0;
            $recentOrders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5")->fetchAll();
        ?>
        <div class="stats-grid">
            <div class="stat-card"><i class="fas fa-users"></i><div><span class="stat-value"><?= $totalUsers ?></span><span class="stat-label">Users</span></div></div>
            <div class="stat-card"><i class="fas fa-tshirt"></i><div><span class="stat-value"><?= $totalProducts ?></span><span class="stat-label">Products</span></div></div>
            <div class="stat-card"><i class="fas fa-shopping-cart"></i><div><span class="stat-value"><?= $totalOrders ?></span><span class="stat-label">Orders</span></div></div>
            <div class="stat-card"><i class="fas fa-rand"></i><div><span class="stat-value">R<?= number_format($revenue, 2) ?></span><span class="stat-label">Revenue</span></div></div>
        </div>
        <h2>Recent Orders</h2>
        <table class="admin-table">
            <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Date</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($order['fullname']) ?></td>
                <td>R<?= number_format($order['total'], 2) ?></td>
                <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                <td><span class="status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span></td>
                <td><a href="admin.php?page=orders&action=view&id=<?= $order['id'] ?>" class="btn-small">View</a></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php
        // ========== USERS ==========
        elseif ($page == 'users'):
            $limit = 10;
            $currentPageNum = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $offset = ($currentPageNum - 1) * $limit;
            $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $totalPages = ceil($totalUsers / $limit);
            if ($action == 'edit' && $id) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $editUser = $stmt->fetch();
            }
        ?>
        <?php if ($action == 'add' || $action == 'edit'): ?>
            <div class="admin-form">
                <h3><?= $action == 'edit' ? 'Edit User' : 'Add New User' ?></h3>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">
                    <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required value="<?= htmlspecialchars($editUser['fullname'] ?? '') ?>"></div>
                    <div class="form-group"><label>Username</label><input type="text" name="username" required value="<?= htmlspecialchars($editUser['username'] ?? '') ?>"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= htmlspecialchars($editUser['email'] ?? '') ?>"></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone" required value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>"></div>
                    <div class="form-group"><label>Password <?= $action == 'edit' ? '(leave blank to keep unchanged)' : '' ?></label><input type="password" name="password" <?= $action != 'edit' ? 'required' : '' ?>></div>
                    <div class="form-group"><label>Role</label><select name="role"><option value="customer">Customer</option><option value="admin" <?= (isset($editUser) && $editUser['role']=='admin')?'selected':'' ?>>Admin</option></select></div>
                    <button type="submit" name="<?= $action == 'edit' ? 'edit_user' : 'add_user' ?>" class="btn"><?= $action == 'edit' ? 'Update' : 'Add' ?></button>
                    <a href="admin.php?page=users" class="btn-outline">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindParam(1, $limit, PDO::PARAM_INT);
                $stmt->bindParam(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                while ($user = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><span class="role role-<?= $user['role'] ?>"><?= $user['role'] ?></span></td>
                    <td>
                        <a href="admin.php?page=users&action=edit&id=<?= $user['id'] ?>" class="btn-small">Edit</a>
                        <?php if ($user['role'] != 'admin'): ?>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" name="delete_user" class="btn-small btn-danger">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="admin.php?page=users&p=<?= $i ?>" class="<?= $i == $currentPageNum ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php
        // ========== PRODUCTS ==========
        elseif ($page == 'products'):
            $limit = 10;
            $currentPageNum = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $offset = ($currentPageNum - 1) * $limit;
            $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $totalPages = ceil($totalProducts / $limit);
            $categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
            if ($action == 'edit' && $id) {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $editProduct = $stmt->fetch();
            }
        ?>
        <?php if ($action == 'add' || $action == 'edit'): ?>
            <div class="admin-form">
                <h3><?= $action == 'edit' ? 'Edit Product' : 'Add New Product' ?></h3>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $editProduct['id'] ?? '' ?>">
                    <div class="form-group"><label>Product Name</label><input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>"></div>
                    <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Price (R)</label><input type="number" step="0.01" name="price" required value="<?= $editProduct['price'] ?? '' ?>"></div>
                    <div class="form-group"><label>Category</label><select name="category"><option value="">Select</option><?php foreach ($categories as $cat): ?><option value="<?= $cat ?>" <?= (isset($editProduct) && $editProduct['category'] == $cat) ? 'selected' : '' ?>><?= ucfirst($cat) ?></option><?php endforeach; ?></select></div>
                    <button type="submit" name="<?= $action == 'edit' ? 'edit_product' : 'add_product' ?>" class="btn"><?= $action == 'edit' ? 'Update' : 'Add' ?></button>
                    <a href="admin.php?page=products" class="btn-outline">Cancel</a>
                </form>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindParam(1, $limit, PDO::PARAM_INT);
                $stmt->bindParam(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                while ($product = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= ucfirst($product['category']) ?></td>
                    <td>R<?= number_format($product['price'], 2) ?></td>
                    <td>
                        <a href="admin.php?page=products&action=edit&id=<?= $product['id'] ?>" class="btn-small">Edit</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" name="delete_product" class="btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="admin.php?page=products&p=<?= $i ?>" class="<?= $i == $currentPageNum ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php
        // ========== ORDERS ==========
        elseif ($page == 'orders'):
            $limit = 10;
            $currentPageNum = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            $offset = ($currentPageNum - 1) * $limit;
            $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $totalPages = ceil($totalOrders / $limit);

            if ($action == 'view' && $id) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch();
                $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$id]);
                $items = $itemsStmt->fetchAll();
            }
        ?>
        <?php if ($action == 'view' && $order): ?>
            <h2>Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h2>
            <div class="order-detail">
                <p><strong>Customer:</strong> <?= htmlspecialchars($order['fullname']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                <p><strong>Delivery Zone:</strong> <?= $order['delivery_zone'] == 'soweto' ? 'Within Soweto' : 'Outside Soweto' ?></p>
                <p><strong>Date:</strong> <?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
                <p><strong>Status:</strong> <span class="status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span></p>
                <h3>Items</h3>
                <table class="admin-table">
                    <tr><th>Product</th><th>Size</th><th>Color</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
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
                </table>
                <div class="order-summary">
                    <p><strong>Subtotal:</strong> R<?= number_format($order['subtotal'], 2) ?></p>
                    <p><strong>Delivery:</strong> R<?= number_format($order['delivery_fee'], 2) ?></p>
                    <p><strong>Total:</strong> R<?= number_format($order['total'], 2) ?></p>
                </div>
                <form method="post" class="status-form">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                    <label>Update Status:</label>
                    <select name="status">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    </select>
                    <button type="submit" name="update_order_status" class="btn">Update</button>
                    <a href="admin.php?page=orders" class="btn-outline">Back</a>
                </form>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Date</th><th>Status</th><th>Action</th></tr>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY order_date DESC LIMIT ? OFFSET ?");
                $stmt->bindParam(1, $limit, PDO::PARAM_INT);
                $stmt->bindParam(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                while ($order = $stmt->fetch()):
                ?>
                <tr>
                    <td>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($order['fullname']) ?></td>
                    <td>R<?= number_format($order['total'], 2) ?></td>
                    <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                    <td><span class="status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span></td>
                    <td><a href="admin.php?page=orders&action=view&id=<?= $order['id'] ?>" class="btn-small">View</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="admin.php?page=orders&p=<?= $i ?>" class="<?= $i == $currentPageNum ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php
        // ========== SETTINGS ==========
        elseif ($page == 'settings'):
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_email','admin_whatsapp')");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            if (isset($_GET['saved'])) echo '<p class="success">Settings updated successfully.</p>';
        ?>
        <div class="admin-form">
            <h3>Admin Settings</h3>
            <form method="post">
                <div class="form-group"><label>Admin Email</label><input type="email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>" required></div>
                <div class="form-group"><label>Admin WhatsApp (number only)</label><input type="text" name="admin_whatsapp" value="<?= htmlspecialchars($settings['admin_whatsapp'] ?? '') ?>" required></div>
                <button type="submit" name="update_settings" class="btn">Save Settings</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>