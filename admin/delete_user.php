<?php
include '../auth/config.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: manage_users.php?delete=success");
        exit();
    } else {
        header("Location: manage_users.php?delete=error");
        exit();
    }

    $stmt->close();
} else {
    header("Location: manage_users.php?delete=error");
    exit();
}

$conn->close();
?>
