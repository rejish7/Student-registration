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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql1 = "SELECT * FROM students s WHERE s.id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $id);

$stmt1->execute();
$result1 = $stmt1->get_result();
$row = $result1->fetch_assoc();

if (!$row) {
    die("Student not found");
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Enrolled Courses - Student Registration Management</title>
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
        
        /* Button Styling */
        .btn-primary, .btn-danger, .btn-success, .btn-warning {
            border-radius: 50px;
            font-weight: 500;
            padding: 8px 15px;
            border: none;
        }
        
       
        
       
        .btn i {
            margin-right: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
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
            
            .main-content {
                margin-left: 0;
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
            .action-buttons {
                flex-direction: column;
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
                <h4>Student's Enrolled Courses</h4>
                <div class="user-info">
                    <img src="../../public/picture/profile.jpg" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="content-card">
                <div class="student-info">
                    <i class="fas fa-user-graduate"></i>
                    <h4>Student: <?php echo htmlspecialchars($row['fullname']); ?></h4>
                </div>
                
                <h2 class="section-title">Enrolled Courses</h2>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">S.N</th>
                                <th scope="col"><i class="fas fa-book"></i> Course Name</th>
                                <th scope="col"><i class="fas fa-clock"></i> Duration</th>
                                <th scope="col"><i class="fas fa-tag"></i> Price</th>
                                <th scope="col"><i class="fas fa-cogs"></i> Actions</th>
                                <th scope="col"><i class="fas fa-money-bill-wave"></i> Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT *,sc.id as scid FROM student_course AS sc
                            JOIN students AS s ON s.id = sc.student_id 
                            JOIN it_course AS c ON c.id = sc.course_id 
                            WHERE s.id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $i = 1;
                            if ($result->num_rows > 0) {
                                while ($row1 = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row1['title']) ?></td>
                                <td><?= htmlspecialchars($row1['duration']) ?></td>
                                <td><?= htmlspecialchars($row1['price']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= student_course_url($row['id'], 'edit', $row1['scid']) ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= student_course_url($row['id'], 'delete', $row1['scid']) ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash"></i> 
                                        </a>
                                    </div>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= payment_url($row['id'], $row1['scid'], 'add') ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <a href="<?= payment_url($row['id'], $row1['scid'],'') ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-eye"></i> 
                                        </a>
                                    </div>
                                </td>
                            </tr>       
                            <?php
                                }
                            } else {
                                ?>
                            <tr>
                                <td colspan="6" class="text-center">No courses enrolled</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="<?= student_url() ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to student
                    </a>
                    <a href="<?= student_course_url($row['id'], 'add') ?>" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Add More Courses
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System</p>
    </footer>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/popper.min.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>
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