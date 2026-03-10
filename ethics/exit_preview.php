<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
if (isLoggedIn() && isAdmin()) {
    exitPreview();
} else {
    header('Location: index.php');
}
?>