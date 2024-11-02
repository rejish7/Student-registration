<?php
session_start();
include '../../config/config.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: ../auth/index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
if (!$stmt) {
    die("Database query failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

$student_id = $student['id'];

$stmt = $conn->prepare("SELECT c.*, COALESCE(SUM(p.amount), 0) as total_paid, c.price as course_price 
        FROM it_course c 
        JOIN student_course sc ON c.id = sc.course_id 
        LEFT JOIN payments p ON p.student_course_id = sc.id 
        WHERE sc.student_id = ? 
        GROUP BY c.id, sc.id");
if (!$stmt) {
    die("Database query failed: " . $conn->error);
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

$sql1 = "SELECT * FROM images WHERE student_id = ?";
$stmt1 = $conn->prepare($sql1);
if (!$stmt1) {
    die("Database query failed: " . $conn->error);
}
$stmt1->bind_param("i", $student_id);
$stmt1->execute();
$result1 = $stmt1->get_result();
$row1 = $result1->fetch_assoc();

$total_course_price = 0;
$total_amount_paid = 0;
$courses_data = [];
while ($course = $enrolled_courses->fetch_assoc()) {
    $total_course_price += $course['course_price'];
    $total_amount_paid += $course['total_paid'];
    $courses_data[] = $course;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Student Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../../views/auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="text-center p-3">
                        <?php if ($row1 && !empty($row1['images'])): ?>
                            <img src="../../public/picture/<?= htmlspecialchars($row1['images']); ?>" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <img src="../../public/picture/default.jpg" alt="Default Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title" style="font-family: 'Roboto', sans-serif; font-weight: 300; color: #2c3e50; text-transform: uppercase; letter-spacing: 1px;"><?= htmlspecialchars($student['fullname']); ?></h5>
                        <a href="views_profile.php?id=<?= $student_id; ?>" class="btn btn-primary btn-block">View Profile</a>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="../courses/user_courseadd.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-warning btn-sm btn-action"><i class="fa fa-plus-circle"></i> Add More Courses</a>
                            </div>
                            <!-- <div class="col-md-6">
                                <a href="../messages/view_messages.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-secondary btn-block mb-2"><i class="fa fa-envelope"></i> View Messages</a>
                            </div> -->
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Your Enrolled Courses</h5>
                        <ul class="list-group">
                            <?php foreach ($courses_data as $course): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($course['title']); ?>
                                    <span class="badge badge-primary badge-pill">Rs<?= number_format($course['course_price'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title">Payment Information</h5>
                                <p class="card-text">Total Amount to pay: <span style="color: black;">Rs<?= number_format($total_course_price, 2); ?></span></p>
                                <p class="card-text">Total Amount paid: <span style="color: green;">Rs<?= number_format($total_amount_paid, 2); ?></span></p>
                                <p class="card-text">Remaining total Amount to be paid: <span style="color: red;">Rs<?= number_format($total_course_price - $total_amount_paid, 2); ?></span></p>
                            </div>
                        </div>
                        
                                                <div class="card mt-4">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Request Form</h5>
                                                        <a href="../requests/request.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-info btn-block mb-2">
                                                            <i class="fa fa-envelope"></i> Make a Request
                                                        </a>
                                                        <a href="../requests/view_request.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-success btn-block">
                                                            <i class="fa fa-eye"></i> View Requests
                                                        </a>
                                                    </div>
                                                </div>
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/popper.min.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>
</body>

</html>
