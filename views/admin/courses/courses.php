<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect_admin('login');
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Course Management - Student Registration System</title>
    <meta charset="utf-8">
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
        
        /* Content Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .content-title {
            font-weight: 600;
            font-size: 1.75rem;
            color: #333;
            margin: 0;
        }
        
        .content-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Course Cards */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .course-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .course-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
        }
        
        .course-body {
            padding: 20px;
            flex: 1;
        }
        
        .course-id {
            display: inline-block;
            background: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
            padding: 5px 15px;
            border-radius: 20px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .course-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            border: none;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d5d5d5;
        }
        
        .btn-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: none;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
            border: none;
        }
        
        .btn-warning:hover {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: none;
        }
        
        .btn-success:hover {
            background-color: var(--success-color);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #888;
            margin-bottom: 20px;
        }
        
        /* Footer */
        footer {
            background: white;
            text-align: center;
            padding: 15px;
            margin-top: auto;
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
            
            .course-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
        
        @media (max-width: 576px) {
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .content-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .course-actions {
                flex-direction: column;
            }
            
            .course-actions .btn {
                width: 100%;
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
                <h4>Student Registration System</h4>
                <div class="user-info">
                    <img src="../../public/picture/profile.jpg" alt="Admin">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
            
            <!-- Content Header -->
            <div class="content-header">
                <h2 class="content-title"><i class="fas fa-book mr-2"></i> Course Management</h2>
                <div class="content-actions">
                    <a href="<?= admin_url() ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="<?= course_url('', 'add') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Course
                    </a>
                </div>
            </div>
            
            <?php
            $sql = "SELECT * FROM it_course";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $courses = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $courses[] = [
                        'id' => $row['id'],
                        'course_name' => $row['title']
                    ];
                }
            }
            ?>
            
            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Courses Found</h3>
                    <p>Start by adding a new course to the system.</p>
                    <a href="add_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Course
                    </a>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h3 class="course-title"><?= htmlspecialchars($course['course_name']) ?></h3>
                            </div>
                            <div class="course-body">
                                <div class="course-id">
                                    <i class="fas fa-hashtag"></i> Course ID: <?= htmlspecialchars($course['id']) ?>
                                </div>
                                <div class="course-actions">
                                    <a href="<?= course_url($course['id'], 'enrolled') ?>" class="btn btn-success">
                                        <i class="fas fa-users"></i> Enrolled Students
                                    </a>
                                    <a href="<?= course_url($course['id'], 'edit') ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Update
                                    </a>
                                    <a href="<?= course_url($course['id'], 'delete') ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System</p>
    </footer>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/popper.min.js"></script>
    <script src="../public/js/bootstrap.min.js"></script>
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