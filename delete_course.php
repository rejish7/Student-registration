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

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>


    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <p>Are you sure you want to delete this course?</p>
        <input type="submit" value="Delete">
        <a href="courses.php">Cancel</a>
    </form>