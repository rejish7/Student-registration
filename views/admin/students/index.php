<?php
session_start();
include '../../../config/config.php';
include '../../../config/url_helpers.php';
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Student Management - Student Registration System</title>
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
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .table-actions {
            display: flex;
            gap: 10px;
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 2px solid #e9ecef;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(37, 117, 252, 0.05);
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 50px;
        }
        
        .badge-primary {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
        }
        
        .badge-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin: 0 2px;
            border: none;
        }
        
        .btn-view {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }
        
        .btn-view:hover {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-add {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .btn-add:hover {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-requests {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .btn-requests:hover {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-edit {
            background-color: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
        }
        
        .btn-edit:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-delete {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        .action-group {
            white-space: nowrap;
        }
        
        /* Back Button */
        .back-btn {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
            margin: 0 auto 30px;
            width: fit-content;
        }
        
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .add-student-btn {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .add-student-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
            color: white;
            text-decoration: none;
        }
        
        /* Search Box */
        .search-box {
            position: relative;
        }
        
        .search-input {
            width: 250px;
            border-radius: 50px;
            border: 1px solid #e0e0e0;
            padding: 10px 15px 10px 40px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            width: 300px;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
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
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .search-input:focus {
                width: 250px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../sidebar.php'; ?>


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
            
            <!-- Table Container -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title"><i class="fas fa-users mr-2"></i> Student Management</h3>
                    <div class="table-actions">
                        <div class="search-box mr-3">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" class="search-input" placeholder="Search students...">
                        </div>
                        <a href="/students/add" class="add-student-btn">
                            <i class="fas fa-user-plus"></i> Add New Student
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="studentsTable" class="table">
                        <thead>
                            <tr>
                                <th>S.N</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Profile Picture</th>
                                <th>Courses</th>
                                <th>Requests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT s.*, u.username 
                            FROM users u 
                            JOIN students s 
                            ON s.user_id = u.id 
                            WHERE u.role != 'admin'";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $i = 1;
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?= htmlspecialchars($row['username']) ?></td>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <?php
                                            $sql1 = "SELECT * FROM images WHERE student_id = ?";
                                            $stmt1 = $conn->prepare($sql1);
                                            $stmt1->bind_param("i", $row['id']);
                                            $stmt1->execute();
                                            $result1 = $stmt1->get_result();

                                            if ($result1->num_rows > 0) {
                                                $row1 = $result1->fetch_assoc();
                                                $imagePath = "../../public/picture/" . htmlspecialchars($row1['images']);
                                                if (file_exists($imagePath)) {
                                            ?>
                                                    <img src="<?= $imagePath ?>" alt="Profile Picture" class="profile-img">
                                            <?php
                                                } else {
                                            ?>
                                                    <img src="../../public/picture/default.jpg" alt="Default Profile Picture" class="profile-img">
                                            <?php
                                                }
                                            } else {
                                            ?>
                                                <img src="../../public/picture/default.jpg" alt="Default Profile Picture" class="profile-img">
                                            <?php
                                            }
                                            ?>
                                        </td>
                                        <td class="action-group">
                                            <a href="<?= student_course_url($row['id']) ?>" class="btn-action btn-view" title="View Courses">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= student_course_url($row['id'], 'add') ?>" class="btn-action btn-add" title="Add Course">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= request_url($row['id']) ?>" class="btn-action btn-requests" title="View Requests">
                                                <i class="fas fa-bell"></i>
                                            </a>
                                        </td>
                                        <td class="action-group">
                                            <a href="<?= student_url($row['id'], 'view') ?>" class="btn-action btn-view" title="View Student">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= student_url($row['id'], 'edit') ?>" class="btn-action btn-edit" title="Edit Student">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= student_url($row['id'], 'delete') ?>" onclick="return confirm('Are you sure you want to delete this student?')" class="btn-action btn-delete" title="Delete Student">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='11' class='text-center'>No students found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
            
            // Search functionality
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#studentsTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>