<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

session_start(); // Start session
// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}

$query = "SELECT username FROM users WHERE id = ? AND role = 'admin'";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $admin_username = $row['username'];
    }

    $stmt->close();
} else {
    echo "Error preparing query: " . $conn->error;
}

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

    redirect("admin/students/{$student_id}/courses");
    exit();
}

$student_id = $_GET['student_id'] ?? '';

if (!$student_id) {
    $_SESSION['error'] = "No student ID provided.";
    redirect('admin/students');
    exit();
}

// Get student name for display
$student_sql = "SELECT fullname FROM students WHERE id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_name = '';
if ($student_row = $student_result->fetch_assoc()) {
    $student_name = $student_row['fullname'];
}
$student_stmt->close();

$courses_sql = "SELECT * FROM it_course WHERE id NOT IN (SELECT course_id FROM student_course WHERE student_id = ?)";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("i", $student_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
?>
<!doctype html>
<html lang="en">

<head>
    <title>Add More Courses - Student Registration Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
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
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
        
        .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            color: #333;
            font-size: 1.25rem;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .user-info span {
            font-weight: 500;
        }
        
        /* Content Card Styling */
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .section-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .student-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(37, 117, 252, 0.05);
            border-radius: 10px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .student-info i {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-right: 15px;
        }
        
        .student-info h4 {
            margin: 0;
            font-weight: 600;
        }
        
        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
            outline: none;
        }
        
        /* Button Styling */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
        }
        
        .btn-secondary {
            background: var(--dark-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(52, 58, 64, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 58, 64, 0.4);
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: var(--secondary-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        /* Alert Styling */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        /* Footer */
        footer {
            background: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .menu-toggle {
                display: block;
            }
            
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 99;
                display: none;
            }
            
            .overlay.active {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .content-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
                <?php include '../sidebar.php'?>


        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <div class="overlay" id="overlay"></div>
            
            <!-- Top Bar -->
            <div class="topbar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4>Add More Courses</h4>
                <div class="user-info">
                    <img src="<?= asset_url('picture/profile.jpg') ?>" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="content-card">
                <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <?php if($student_name): ?>
                <div class="student-info">
                    <i class="fas fa-user-graduate"></i>
                    <h4>Student: <?php echo htmlspecialchars($student_name); ?></h4>
                </div>
                <?php endif; ?>
                
                <h2 class="section-title">Add Course Enrollment</h2>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                    
                    <div class="form-group">
                        <label for="course_id"><i class="fas fa-book mr-2"></i>Select Course:</label>
                        <select name="course_id" id="course_id" class="form-control" required>
                            <option value="">-- Select a course --</option>
                            <?php 
                            if ($courses_result->num_rows > 0) {
                                while ($course = $courses_result->fetch_assoc()): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title'] . ' - ' . $course['duration'] . ' - $' . $course['price']); ?></option>
                                <?php endwhile;
                            } else {
                                echo '<option value="" disabled>No available courses found</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Course
                        </button>
                        <a href="<?= student_url($student_id) ?>" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
                
                <div class="mt-4">
                    <a href="<?= student_course_url($student_id) ?>" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Student Courses
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System</p>
    </footer>

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
<?php $courses_stmt->close(); ?>
