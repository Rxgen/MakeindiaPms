<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!empty($_SESSION['client_id']) && !empty($_SESSION['client_name'])) {
    $name = htmlspecialchars($_SESSION['client_name'], ENT_QUOTES, 'UTF-8');
    $folio = !empty($_SESSION['client_folio_number']) ? htmlspecialchars($_SESSION['client_folio_number'], ENT_QUOTES, 'UTF-8') : '';
    echo 'Welcome, ' . $name;
    if ($folio !== '') {
        echo ' | Folio No: ' . $folio;
    }
    echo ' | <a href="/client_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>';
} else {
    echo '<a href="/client_login.php"><i class="fas fa-sign-in-alt"></i> Client Login</a> | ';
    echo '<a href="/login.php"><i class="fas fa-user-shield"></i> Admin Login</a>';
}
