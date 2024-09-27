
<?php
session_start();
include 'config/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT s.*, i.images FROM students s LEFT JOIN images i ON s.id = i.Student_id WHERE s.id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Student Profile</h1>
        <div class="card">
            <div class="card-body">
                <?php if ($student['images']): ?>
                    <img src="picture/<?php echo htmlspecialchars($student['images']); ?>" alt="Profile Picture" class="img-thumbnail mb-3" style="max-width: 200px;">
                <?php endif; ?>
                <h5>Name: <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></h5>
                <p>Email: <?php echo htmlspecialchars($student['email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($student['phone']); ?></p>
                <p>Address: <?php echo htmlspecialchars($student['address']); ?></p>
                <p>Gender: <?php echo htmlspecialchars($student['gender']); ?></p>
            </div>
        </div>
        <a href="student_dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
