<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

session_start(); // Start session
// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']); 
    $scid = intval($_POST['scid']);

    $sql = "UPDATE student_course SET course_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("ii", $course_id, $scid);
    
    if ($stmt->execute()) {
        redirect(student_course_url($student_id));
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$sql = "SELECT sc.*, s.fullname, c.title, c.duration, c.price 
        FROM student_course AS sc 
        JOIN students AS s ON s.id = sc.student_id 
        JOIN it_course AS c ON c.id = sc.course_id 
        WHERE sc.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $scid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found");
}

$row = $result->fetch_assoc();
$student_name = htmlspecialchars($row['fullname'] );
?>
<!doctype html>
<html lang="en">

<head>
    <title>Edit Student Course - Student Registration Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
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
        
        /* Table Styling */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
            background-color: white;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 15px;
            font-weight: 500;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .table tbody tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #f5f5f5;
        }
        
        /* Form Controls */
        select.form-control {
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            padding: 10px 15px;
            height: auto;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        select.form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
        }
        
        /* Button Styling */
        .btn-primary, .btn-danger, .btn-success, .btn-warning {
            border-radius: 50px;
            font-weight: 500;
            padding: 8px 20px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.2);
        }
        
        .btn-danger {
            background: var(--danger-color);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
        }
        
        .btn-success {
            background: var(--success-color);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        .btn-warning {
            background: var(--warning-color);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
            color: #212529;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
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
                <h4>Edit Student Course</h4>
                <div class="user-info">
                    <img src="<?php echo asset_url('picture/profile.jpg'); ?>" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="content-card">
                <div class="student-info">
                    <i class="fas fa-user-graduate"></i>
                    <h4>Student: <?php echo htmlspecialchars($student_name); ?></h4>
                </div>
                
                <h2 class="section-title">Edit Course Enrollment</h2>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"><i class="fas fa-book"></i> Course Name</th>
                                <th scope="col"><i class="fas fa-clock"></i> Duration</th>
                                <th scope="col"><i class="fas fa-tag"></i> Price</th>
                                <th scope="col"><i class="fas fa-cogs"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <form action="<?php echo student_course_url($id); ?>" method="post">
                                    <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                    <input type="hidden" name="scid" value="<?= $scid ?>">
                                    <td>
                                        <div class="form-group mb-0">
                                            <?php
                                            $courses = $conn->query("SELECT id, title FROM it_course");
                                            echo "<select name='course_id' class='form-control' required>";
                                            while ($course = $courses->fetch_assoc()) {
                                                $selected = ($course['id'] == $row['course_id']) ? 'selected' : '';
                                                echo "<option value='" . intval($course['id']) . "' {$selected}>" . htmlspecialchars($course['title']) . "</option>";
                                            }
                                            echo "</select>";
                                            ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['duration']) ?></td>
                                    <td><?= htmlspecialchars($row['price']) ?></td>
                                    <td>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo student_course_url($id); ?>" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Student Courses
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System</p>
    </footer>

    <script src="<?php echo asset_url('js/jquery-3.3.1.min.js'); ?>"></script>
    <script src="<?php echo asset_url('js/popper.min.js'); ?>"></script>
    <script src="<?php echo asset_url('js/bootstrap.min.js'); ?>"></script>
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

<?php
$stmt->close();
$conn->close(); 
?>