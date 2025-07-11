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

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$course_title = "";

if ($course_id) {
    $sql_course = "SELECT title FROM it_course WHERE id = ?";
    if ($stmt_course = $conn->prepare($sql_course)) {
        $stmt_course->bind_param("i", $course_id);
        $stmt_course->execute();
        $result_course = $stmt_course->get_result();
        if ($course_data = $result_course->fetch_assoc()) {
            $course_title = $course_data['title'];
        }
        $stmt_course->close();
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Enrolled Students - Student Registration Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
    <style>
        /* Your existing styles remain the same */
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
        
        /* Course Info Styles */
        .course-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(37, 117, 252, 0.05);
            border-radius: 10px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .course-info i {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-right: 15px;
        }
        
        .course-info h4 {
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
        
        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: rgba(37, 117, 252, 0.2);
            margin-bottom: 15px;
            display: block;
        }
        
        .empty-state p {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        /* Button Styling */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
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
                <h4>Enrolled Students</h4>
                <div class="user-info">
                    <img src="<?php echo asset_url('picture/profile.jpg'); ?>" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="content-card">
                <?php if($course_title): ?>
                <div class="course-info">
                    <i class="fas fa-book-open"></i>
                    <h4>Course: <?php echo htmlspecialchars($course_title); ?></h4>
                </div>
                <?php endif; ?>
                
                <h2 class="section-title">Students Enrolled</h2>
                
                <?php if ($course_id): ?>
                    <?php 
                    $sql = "SELECT s.id, s.fullname, s.email, s.phone
                            FROM students s 
                            INNER JOIN student_course sc ON s.id = sc.student_id 
                            WHERE sc.course_id = ?";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $course_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col"><i class="fas fa-id-badge mr-1"></i> ID</th>
                                        <th scope="col"><i class="fas fa-user mr-1"></i> Name</th>
                                        <th scope="col"><i class="fas fa-envelope mr-1"></i> Email</th>
                                        <th scope="col"><i class="fas fa-phone mr-1"></i> Phone</th>
                                        <th scope="col"><i class="fas fa-cogs mr-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                                        <td>
                                            <a href="<?php echo student_url($row['id'], 'edit'); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>No students are currently enrolled in this course.</p>
                        </div>
                        <?php endif;
                        $stmt->close();
                    } else {
                        echo "<div class='alert alert-danger'>Error preparing statement: " . htmlspecialchars($conn->error) . "</div>";
                    }
                    ?>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Please provide a valid course ID.
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="<?php echo course_url(); ?>" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Courses
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
<?php $conn->close(); ?>
