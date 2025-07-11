<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        header("Location: " . user_url() . "?delete=success");
        exit();
    } else {
        header("Location: " . user_url() . "?delete=error");
        exit();
    }

    $stmt->close();
} else {
    header("Location: " . user_url() . "?delete=error");
    exit();
}

$conn->close();
?>
