<?php
/**
 * Authentication functions (no email verification)
 */

/**
 * Register a new user
 * @return bool|string true on success, error message on failure
 */
function registerUser($pdo, $fullname, $username, $email, $password, $phone) {
    // Check if email or username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        return "Email or username already registered.";
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (fullname, username, email, password, phone, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$fullname, $username, $email, $hashed, $phone]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $fullname;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'customer';
        return true;
    } catch (PDOException $e) {
        return "Registration failed. Please try again.";
    }
}

/**
 * Login user
 * @return bool|string true on success, error message on failure
 */
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Transfer guest cart to user
        $session_id = session_id();
        $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?")->execute([$user['id'], $session_id]);
        return true;
    } else {
        return "Invalid email or password.";
    }
}

/**
 * Log out the current user
 */
function logoutUser() {
    $_SESSION = array();
    session_destroy();
}

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}
/**
 * Redirect admin away from customer pages if not in preview mode
 */
function redirectAdminFromCustomerPages() {
    if (isLoggedIn() && isAdmin() && !isPreviewMode()) {
        header('Location: admin.php');
        exit;
    }
}
/**
 * Enable preview mode
 */
function isPreviewMode() {
    return isset($_SESSION['admin_preview']) && $_SESSION['admin_preview'] === true;
}
function enablePreview() {
    $_SESSION['admin_preview'] = true;
}
function disablePreview() {
    unset($_SESSION['admin_preview']);
}
function exitPreview() {
    disablePreview();
    header('Location: admin.php');
    exit;
}
?>