
<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($id > 0 && $scid > 0) {
    $sql = "DELETE FROM payments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: paymentviews.php?id=$scid&scid=$scid");
        exit();
    } else {
        die("Error deleting record: " . $conn->error);
    }
    $stmt->close();
} else {
    die("Invalid payment ID or student course ID");
}

$conn->close();
?>
