<?php
include '../../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    $check_sql = "SELECT * FROM student_course WHERE student_id = ? AND course_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $student_id, $course_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        $sql = "INSERT INTO student_course (student_id, course_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $course_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Course added successfully.";
        } else {
            $_SESSION['error'] = "Error adding course: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Student is already enrolled in this course.";
    }
    $check_stmt->close();

    header("Location: ../students/student_dashboard.php");
    exit();
}

$student_id = $_GET['student_id'] ?? '';

if (!$student_id) {
    $_SESSION['error'] = "No student ID provided.";
    header("Location: ../students/student_dashboard.php");
    exit();
}

$courses_sql = "SELECT * FROM it_course WHERE id NOT IN (SELECT course_id FROM student_course WHERE student_id = ?)";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("i", $student_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add More Courses</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Add More Courses</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-4">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
            <div class="form-group">
                <label for="course_id">Select Course:</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">Select a course</option>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Add Course</button>
            </div>
        </form>
    </div>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>
</body>
</html>
