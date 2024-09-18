
<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM it_course WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: courses.php");
        exit();
    } else {
        echo "Error deleting course: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $id = $_GET['id'];
}

$conn->close();
?>

<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <p>Are you sure you want to delete this course?</p>
    <input type="submit" value="Delete">
    <a href="courses.php">Cancel</a>
</form>
