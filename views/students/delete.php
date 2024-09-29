<?php

include '../../config/config.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Record deleted successfully";
    } 
    else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location:crud_display.php");
    exit();
} 
else {
    echo "No ID provided for deletion";
}

?>
