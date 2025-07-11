<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users - Student Registration Management</title>
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
        
        /* Page Content */
        .page-title {
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 30px;
            color: #333;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }
        
        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
            color: white;
        }
        
        /* Table Styles */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px;
            font-size: 0.85rem;
        }
        
        .table thead th:first-child {
            border-radius: 10px 0 0 0;
        }
        
        .table thead th:last-child {
            border-radius: 0 10px 0 0;
        }
        
        .table tbody tr:last-child td:first-child {
            border-radius: 0 0 0 10px;
        }
        
        .table tbody tr:last-child td:last-child {
            border-radius: 0 0 10px 0;
        }
        
        .table tbody tr:hover {
            background-color: rgba(37, 117, 252, 0.05);
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
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
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-buttons .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
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
                    <img src="<?php echo asset_url('picture/profile.jpg'); ?>" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <!-- Page Content -->
            <h2 class="page-title">User Management</h2>
            
            <div class="content-card">
                <div class="action-buttons">
                    <a href="<?php echo admin_url(); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                    <a href="<?php echo user_url('','add'); ?>" class="btn btn-gradient">
                        <i class="fas fa-plus mr-2"></i> Add New User
                    </a>
                </div>
                
                <?php
                $sql = "SELECT * FROM users ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END, username";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-hover'>";
                    echo "<thead>";
                    echo "<tr><th>Username</th><th>Role</th><th>Actions</th></tr>";
                    echo "</thead>";
                    echo "<tbody>";

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td><span class='badge " . ($row['role'] == 'admin' ? 'badge-primary' : 'badge-secondary') . "'>" . htmlspecialchars($row['role']) . "</span></td>";
                        echo "<td>";
                        echo "<a href='" . user_url($row['id'], 'edit') . "' class='btn btn-sm btn-primary mr-2'><i class='fas fa-edit'></i> Edit</a>";
                        echo "<a href='" . user_url($row['id'], 'delete') . "'  class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\");'><i class='fas fa-trash'></i> Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-info'><i class='fas fa-info-circle mr-2'></i>No users found.</div>";
                }

                mysqli_close($conn);
                ?>
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
