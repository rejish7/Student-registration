<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username=?, role=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $role, $password, $id);
    } else {
        $sql = "UPDATE users SET username=?, role=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $role, $id);
    }

    if ($stmt->execute()) {
        redirect('admin/users');
    } else {
        $error = "Error updating user: " . $conn->error;
    }
    $stmt->close();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: manage_users.php");
    exit();
}

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
    <title>Add User - Student Registration Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
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
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
    }

    .btn-secondary {
        background: var(--dark-color);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(52, 58, 64, 0.3);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(52, 58, 64, 0.4);
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

    /* User Icon */
    .user-icon {
        width: 70px;
        height: 70px;
        background: rgba(37, 117, 252, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 2rem;
        color: var(--secondary-color);
    }

    .user-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
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

        .user-header {
            flex-direction: column;
            text-align: center;
        }

        .user-icon {
            margin-right: 0;
            margin-bottom: 15px;
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
                <h4>Edit User</h4>
                <div class="user-info">
                    <img src="<?php echo asset_url('picture/profile.jpg'); ?>" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="content-card">
                <?php if(isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>

                <div class="user-header">
                    <div class="user-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2 class="section-title">Edit User Account</h2>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row">
                        <div class="col-md-6">

                            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <div class="form-group">
                                    <label for="username">Username:</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="role">Role:</label>
                                    <select class="form-control" id="role" name="role">
                                        <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User
                                        </option>
                                        <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>
                                            Admin</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="password">New Password (leave blank to keep current):</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <button type="submit" class="btn btn-primary">Update User</button>
                                <a href="<?php echo user_url()?>" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                        <script src="<?php echo asset_url('js/jquery.min.js'); ?>"></script>
                        <script src="<?php echo asset_url('js/popper.min.js'); ?>"></script>
                        <script src="<?php echo asset_url('js/bootstrap.min.js'); ?>"></script>
</body>

</html>