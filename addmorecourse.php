<?php
include 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];   
        $sql = "SELECT * FROM students WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
    } 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['course_id']) && isset($_POST['student_id'])) {
        $course_id = $_POST['course_id'];
        $student_id = $_POST['student_id'];
        
        $sql = "INSERT INTO student_course (student_id, course_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $course_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Course added successfully!'); window.location.href='views.php?id=" . $student_id . "';</script>";
        } else {
            echo "<script>alert('Error adding course: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <title> Add Course</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="css/bootstrap.min.css">

    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section"> Add More course</h2>
                </div>
            </div>
            <div>
<form method="post" action="">
    <label for="course_id">Course Name:</label><br><br>

    <select id="course_id" name="course_id" >
        <option value="">Select an IT course</option>
        <?php
        $sql = "SELECT * FROM it_course";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["title"]) . " (" . htmlspecialchars($row["duration"]) . ", Rs" . htmlspecialchars($row["price"]) . ")</option>";
            }
        }
        ?>
    </select>
    
    <br><br>
    
    <input type="hidden" name="student_id" value="<?php echo isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : ''; ?>">
    
    <br><br>
    <input type="submit" value="Add Course" class ="btn btn-warning">
    <br><br>
</form>
</div>