<?php
// Include URL helpers if not already included
if (!function_exists('admin_url')) {
    require_once __DIR__ . '/../../config/url_helpers.php';
}

// Function to check if current page is active
function is_active_page($page) {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    $current_path = $_SERVER['REQUEST_URI'];
    
    switch($page) {
        case 'dashboard':
            return ($current_page == 'dashboard' || strpos($current_path, '/admin') !== false && strpos($current_path, '/admin/users') === false);
        case 'students':
            return (strpos($current_path, '/students') !== false || $current_page == 'crud_display');
        case 'courses':
            return (strpos($current_path, '/courses') !== false || $current_page == 'courses');
        case 'users':
            return (strpos($current_path, '/admin/users') !== false || $current_page == 'manage_users');
        default:
            return false;
    }
}
?>

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
</style>

<!-- Sidebar HTML -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-graduation-cap mr-2"></i>Admin Panel</h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= admin_url() ?>" class="<?= is_active_page('dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="<?= student_url() ?>" class="<?= is_active_page('students') ? 'active' : '' ?>">
                <i class="fas fa-users"></i>Students
            </a>
        </li>
        <li>
            <a href="<?= course_url() ?>" class="<?= is_active_page('courses') ? 'active' : '' ?>">
                <i class="fas fa-book"></i>Courses
            </a>
        </li>
        <li>
            <a href="<?= admin_url('users') ?>" class="<?= is_active_page('users') ? 'active' : '' ?>">
                <i class="fas fa-user-cog"></i>Users
            </a>
        </li>
        <li>
            <a href="<?= admin_url('logout') ?>">
                <i class="fas fa-sign-out-alt"></i>Logout
            </a>
        </li>
    </ul>
</div>

<script>
    // Sidebar toggle functionality
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