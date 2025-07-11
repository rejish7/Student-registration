<?php
session_start();
include '../../../config/config.php';
include '../../../config/url_helpers.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}

$errors = array();
$student = null;
$student_image = null;
$student_courses = array();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get student information
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    
    if (!$student) {
        $errors['not_found'] = "Student not found.";
    } else {
        // Get student image
        $sql_image = "SELECT * FROM images WHERE student_id = ?";
        $stmt_image = $conn->prepare($sql_image);
        if ($stmt_image) {
            $stmt_image->bind_param("i", $id);
            $stmt_image->execute();
            $result_image = $stmt_image->get_result();
            $student_image = $result_image->fetch_assoc();
            $stmt_image->close();
        }
        
        // Get student courses
        $sql_courses = "SELECT sc.*, c.title, c.duration, c.price 
                       FROM student_course sc 
                       LEFT JOIN it_course c ON sc.course_id = c.id 
                       WHERE sc.student_id = ?";
        $stmt_courses = $conn->prepare($sql_courses);
        if ($stmt_courses) {
            $stmt_courses->bind_param("i", $id);
            $stmt_courses->execute();
            $result_courses = $stmt_courses->get_result();
            while ($row = $result_courses->fetch_assoc()) {
                $student_courses[] = $row;
            }
            $stmt_courses->close();
        } else {
            $errors['db_error'] = "Database error: " . $conn->error;
        }
    }
    $stmt->close();
} else {
    $errors['no_id'] = "No student ID provided.";
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>View Student - Student Registration System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/bootstrap.min.css') ?>">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f8fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-container {
            display: flex;
            flex: 1;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s ease;
            padding: 20px;
        }
        
        .topbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 30px;
            border-bottom: none;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .student-info {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .student-photo {
            text-align: center;
        }
        
        .student-photo img {
            width: 180px;
            height: 180px;
            border-radius: 15px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 5px solid white;
        }
        
        .student-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .detail-item {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 16px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .courses-table {
            margin-top: 20px;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .status-inactive {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.5);
            color: white;
            text-decoration: none;
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: white;
            border: none;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .btn-danger {
            background: var(--danger-color);
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
            
            .student-info {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .student-details {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .detail-item {
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include '../sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <div class="overlay" id="overlay"></div>
            
            <!-- Top Bar -->
            <div class="topbar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4><i class="fas fa-eye mr-2"></i>View Student</h4>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($student): ?>
                <!-- Student Profile Card -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-graduate mr-2"></i>Student Profile</h3>
                        <p class="mb-0">Complete student information and enrollment details</p>
                    </div>
                    <div class="card-body">
                        <div class="student-info">
                            <div class="student-photo">
                                <?php if ($student_image && !empty($student_image['images'])): ?>
                                    <img src="<?= asset_url('picture/' . $student_image['images']) ?>" alt="Student Photo">
                                <?php else: ?>
                                    <img src="<?= asset_url('picture/default-avatar.png') ?>" alt="Default Photo">
                                <?php endif; ?>
                                <p class="mt-2 text-muted">Student ID: #<?= $student['id'] ?></p>
                            </div>
                            
                            <div class="student-details">
                                <div class="detail-item">
                                    <div class="detail-label">Full Name</div>
                                    <div class="detail-value"><?= htmlspecialchars($student['fullname']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Email Address</div>
                                    <div class="detail-value"><?= htmlspecialchars($student['email']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Phone Number</div>
                                    <div class="detail-value"><?= htmlspecialchars($student['phone']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Gender</div>
                                    <div class="detail-value"><?= ucfirst(htmlspecialchars($student['gender'])) ?></div>
                                </div>
                                
                                <div class="detail-item" style="grid-column: 1 / -1;">
                                    <div class="detail-label">Address</div>
                                    <div class="detail-value"><?= htmlspecialchars($student['address']) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">Registration Date</div>
                                    <div class="detail-value">
                                        <?php 
                                        if (isset($student['created_at']) && !empty($student['created_at'])) {
                                            echo date('F j, Y', strtotime($student['created_at']));
                                        } else {
                                            echo 'Date not available';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <a href="<?= student_url($student['id'], 'edit') ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Student
                            </a>
                            <a href="<?= student_course_url($student['id']) ?>" class="btn btn-primary">
                                <i class="fas fa-book"></i> Manage Courses
                            </a>
                            <a href="<?= request_url($student['id']) ?>" class="btn btn-info">
                                <i class="fas fa-envelope"></i> View Requests
                            </a>
                            <a href="<?= student_url() ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Enrolled Courses Card -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-graduation-cap mr-2"></i>Enrolled Courses</h3>
                        <p class="mb-0">Current course enrollments and progress</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($student_courses)): ?>
                            <div class="courses-table">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Course Name</th>
                                            <th>Duration</th>
                                            <th>Price</th>
                                            <th>Enrollment Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($student_courses as $course): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($course['course_name'] ?? 'Course name not available') ?></strong>
                                                </td>
                                                <td><?= htmlspecialchars($course['course_duration'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php 
                                                    if (isset($course['course_price']) && is_numeric($course['course_price'])) {
                                                        echo '$' . number_format($course['course_price'], 2);
                                                    } else {
                                                        echo 'Price not available';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (isset($course['created_at']) && !empty($course['created_at'])) {
                                                        echo date('M j, Y', strtotime($course['created_at']));
                                                    } else {
                                                        echo 'Date not available';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-active">Active</span>
                                                </td>
                                                <td>
                                                    <?php if (isset($course['course_id'])): ?>
                                                        <a href="<?= payment_url($student['id'], $course['course_id']) ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="fas fa-credit-card"></i> Payments
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No course ID</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No courses enrolled</h5>
                                <p class="text-muted">This student hasn't enrolled in any courses yet.</p>
                                <a href="<?= student_course_url($student['id'], 'add') ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Course
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="<?= asset_url('js/jquery-3.3.1.min.js') ?>"></script>
    <script src="<?= asset_url('js/popper.min.js') ?>"></script>
    <script src="<?= asset_url('js/bootstrap.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            // Toggle sidebar on mobile
            $('#menu-toggle').click(function() {
                $('#sidebar').toggleClass('active');
                $('#overlay').toggleClass('active');
            });
            
            // Close sidebar when clicking overlay
            $('#overlay').click(function() {
                $('#sidebar').removeClass('active');
                $('#overlay').removeClass('active');
            });
            
            // Handle window resize
            $(window).resize(function() {
                if ($(window).width() > 992) {
                    $('#sidebar').removeClass('active');
                    $('#overlay').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>
