<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM it_course WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Course deleted successfully";
    } else {
        echo "Error deleting course: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: " . course_url());
    exit();
} else {
    echo "No ID provided for deletion";
}
?>
