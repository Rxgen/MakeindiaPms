<?php


include 'database.php';

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    $deleteQuery = "DELETE FROM pms_entries WHERE id = ?";
    $stmt = $mysqli->prepare($deleteQuery);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }

    $stmt->close();
}
header("Location: dashboard.php");
exit();
