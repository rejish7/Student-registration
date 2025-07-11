<?php
include '../../config/config.php'; 
include '../../config/url_helpers.php'; // Include the URL helpers

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($id > 0 && $scid > 0) {
    // First get the payment details before deleting
    $sql = "SELECT amount FROM payments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();

    // Delete the payment record
    $sql = "DELETE FROM payments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
  
    if ($stmt->execute()) {
        // Use redirect function instead of header
        redirect("admin/payments/paymentviews?id=$id&scid=$scid");
    } else {
        die("Error deleting record: " . $conn->error);
    }
    $stmt->close();
} else {
    die("Invalid payment ID or student course ID");
}

$conn->close();
?>
