<?php
// Include required configuration files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/url_helpers.php';

session_start();
// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}

$admin_username = 'Admin'; // Default fallback value

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
    error_log("Error preparing query: " . $conn->error);
    // Don't echo errors to user in production
}

// Get counts for dashboard stats
$stats = [
    'students' => 0,
    'courses' => 0,
    'users' => 0,
    'payments' => 0
];
// Count students
$query = "SELECT COUNT(*) as count FROM students";
if ($result = $conn->query($query)) {
    $row = $result->fetch_assoc();
    $stats['students'] = $row ? $row['count'] : 0;
} else {
    error_log("Error counting students: " . $conn->error);
}

// Count courses
$query = "SELECT COUNT(*) as count FROM it_course";
if ($result = $conn->query($query)) {
    $row = $result->fetch_assoc();
    $stats['courses'] = $row ? $row['count'] : 0;
} else {
    error_log("Error counting courses: " . $conn->error);
}

// Count users
$query = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
if ($result = $conn->query($query)) {
    $row = $result->fetch_assoc();
    $stats['users'] = $row ? $row['count'] : 0;
} else {
    error_log("Error counting users: " . $conn->error);
}

// Count payments
$query = "SELECT COUNT(*) as count FROM payments";
if ($result = $conn->query($query)) {
    $row = $result->fetch_assoc();
    $stats['payments'] = $row ? $row['count'] : 0;
} else {
    error_log("Error counting payments: " . $conn->error);
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Admin Dashboard - Student Registration Management</title>
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
        
        /* Topbar and Main Content Styles */
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
        
        .actions button {
            background: transparent;
            border: none;
            color: #777;
            margin-left: 15px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .actions button:hover {
            color: var(--primary-color);
        }
        
        /* Dashboard Content */
        .dashboard-title {
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 30px;
            color: #333;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .stat-card.students .icon {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
        }
        
        .stat-card.courses .icon {
            background: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
        }
        
        .stat-card.users .icon {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .stat-card.payments .icon {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-card p {
            color: #777;
            margin: 5px 0 0;
        }
        
        /* Action Cards */
        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .action-card .icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 20px rgba(37, 117, 252, 0.2);
        }
        
        .action-card h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 15px;
        }
        
        .action-card p {
            color: #777;
            margin: 0 0 25px;
            font-size: 0.95rem;
        }
        
        .action-card .btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }
        
        .action-card .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
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
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

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
                    <span><?php echo htmlspecialchars($admin_username); ?></span>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <h2 class="dashboard-title">Dashboard Overview</h2>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card students">
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3><?php echo $stats['students']; ?></h3>
                    <p>Total Students</p>
                </div>
                
                <div class="stat-card courses">
                    <div class="icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3><?php echo $stats['courses']; ?></h3>
                    <p>Active Courses</p>
                </div>
                
                <div class="stat-card users">
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Users</p>
                </div>
                
                <div class="stat-card payments">
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3><?php echo $stats['payments']; ?></h3>
                    <p>Payments</p>
                </div>
            </div>
            
            <!-- Action Cards -->
            <div class="action-cards">
                <div class="action-card">
                    <div class="icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4>Add New Student</h4>
                    <p>Register a new student to the system with personal details and course selection</p>
                    <a href="<?php echo student_url('','add')?>" class="btn">Add Student</a>
                </div>
                
                <div class="action-card">
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h4>Manage Courses</h4>
                    <p>Add, edit or remove courses from the system and manage course details</p>
                    <a href="<?php echo course_url()?>" class="btn">View Courses</a>
                </div>
                
                <div class="action-card">
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h4>Manage Users</h4>
                    <p>Manage system users, roles and permissions for administrators and staff</p>
                    <a href="<?= admin_url('users') ?>" class="btn">Manage Users</a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System by Rejish</p>
    </footer>

    <script src="<?= asset_url('js/jquery-3.3.1.min.js') ?>"></script>
    <script src="<?= asset_url('js/popper.min.js') ?>"></script>
    <script src="<?= asset_url('js/bootstrap.min.js') ?>"></script>
    <script>
        // Menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const overlay = document.getElementById('overlay');
            
            if (menuToggle && overlay) {
                menuToggle.addEventListener('click', function() {
                    overlay.classList.toggle('active');
                });
                
                overlay.addEventListener('click', function() {
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
