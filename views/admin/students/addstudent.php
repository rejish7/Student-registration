<?php
session_start();
include '../../../config/config.php';
include '../../../config/url_helpers.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $wanted_courses = isset($_POST['wanted_courses']) ? $_POST['wanted_courses'] : [];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($fullname) || strlen($fullname) < 2 || strlen($fullname) > 100) {
        $errors['fullname'] = "Full name is required and must be between 2 and 100 characters";
    }
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    if (empty($phone) || !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Phone number is required and must be 10 digits";
    }
    if (empty($address) || strlen($address) < 5 || strlen($address) > 200) {
        $errors['address'] = "Address is required and must be between 5 and 200 characters";
    }
    if (empty($gender) || !in_array($gender, ['male', 'female', 'other'])) {
        $errors['gender'] = "Valid gender selection is required";
    }
    if (empty($wanted_courses) || !is_array($wanted_courses) || count($wanted_courses) < 1) {
        $errors['wanted_courses'] = "Please select at least one course";
    }
    if (empty($username) || strlen($username) < 4 || strlen($username) > 20 || !preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors['username'] = "Username is required and must be 4-20 characters long, containing only letters, numbers, and underscores";
    }
    if (empty($password) || strlen($password) < 8 || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $errors['password'] = "Password is required and must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number and one special character";
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors['image'] = "Profile picture is required";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['image'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
        if ($file_size > 5000000 || $file_size < 1000) {
            $errors['image'] = "File size must be between 1KB and 5MB.";
        }
    }

    if (empty($errors)) {
        $check_username = "SELECT id FROM users WHERE username = ?";
        if ($stmt_check = $conn->prepare($check_username)) {
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors['username'] = "Username already exists. Please choose a different one.";
            }
            $stmt_check->close();
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql_user = "INSERT INTO users (username, password) VALUES (?, ?)";

        if ($stmt_user = $conn->prepare($sql_user)) {
            $stmt_user->bind_param("ss", $username, $password_hash);
            $result_user = $stmt_user->execute();

            if ($result_user) {
                $user_id = $conn->insert_id;
                $sql_student = "INSERT INTO students (user_id, fullname, email, phone, address, gender) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_student = $conn->prepare($sql_student)) {
                    $stmt_student->bind_param("isssss", $user_id, $fullname, $email, $phone, $address, $gender);
                    $result_student = $stmt_student->execute();

                    if ($result_student) {
                        $last_id = $conn->insert_id;
                        $sql1 = "INSERT INTO student_course (student_id, course_id) VALUES (?, ?)";
                        if ($stmt1 = $conn->prepare($sql1)) {
                            foreach ($wanted_courses as $course_id) {
                                $stmt1->bind_param("ii", $last_id, $course_id);
                                $result1 = $stmt1->execute();
                                if (!$result1) {
                                    $errors['course_insert'] = "Error inserting course: " . $conn->error;
                                    break;
                                }
                            }

                            if (!isset($errors['course_insert'])) {
                                $file_name = $_FILES['image']['name'];
                                $fileTmpName = $_FILES['image']['tmp_name'];
                                $folder = '../../public/picture/' . basename($file_name);

                                $sql2 = "INSERT INTO images (Student_id, images) VALUES (?, ?)";
                                if ($stmt2 = $conn->prepare($sql2)) {
                                    $stmt2->bind_param("is", $last_id, $file_name);
                                    $result2 = $stmt2->execute();

                                    if ($result2) {
                                        if (move_uploaded_file($fileTmpName, $folder)) {
                                            $_SESSION['success_message'] = "Student registered successfully!";
                                            redirect_admin('students');
                                            exit();
                                        } else {
                                            $errors['file_upload'] = "Failed to upload the file.";
                                        }
                                    } else {
                                        $errors['image_insert'] = "Failed to insert file name into the database.";
                                    }
                                } else {
                                    $errors['image_statement'] = "Error preparing image insert statement: " . $conn->error;
                                }
                            }
                        } else {
                            $errors['course_statement'] = "Error preparing course insert statement: " . $conn->error;
                        }
                    } else {
                        $errors['student_insert'] = "Error inserting student: " . $conn->error;
                    }
                } else {
                    $errors['student_statement'] = "Error preparing student insert statement: " . $conn->error;
                }
            } else {
                $errors['user_insert'] = "Error inserting user: " . $conn->error;
            }

            $stmt_user->close();
        } else {
            $errors['user_statement'] = "Error preparing user insert statement: " . $conn->error;
        }

        mysqli_close($conn);
    }
}

if (!empty($errors)) {
    echo "<div class='alert alert-danger'>";
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    echo "</div>";
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Add Student - Student Registration Management</title>
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
        
        /* Form Styling */
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .section-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 25px;
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

        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            padding: 12px 15px;
            height: auto;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
        }

        .gender-options, .course-options {
            margin-top: 10px;
        }
        
        .form-check {
            margin-bottom: 8px;
        }
        
        .error {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
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
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include '../sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <div class="overlay" id="overlay"></div>
            
            <!-- Top Bar -->
            <div class="topbar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4>Add New Student</h4>
                <div class="user-info">
                    <img src="<?= asset_url('picture/profile.jpg') ?>" alt="Admin">
                    <span><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="content-card">
                <h2 class="section-title">Student Registration Form</h2>
                
                <form action="<?= student_url('', 'add') ?>" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> Username</label>
                                <input type="text" class="form-control" name="username" id="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['username']) ? $errors['username'] : ''; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <span class="error"><?= isset($errors['password']) ? $errors['password'] : ''; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                                <input type="text" class="form-control" name="fullname" id="fullname" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['fullname']) ? $errors['fullname'] : ''; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['email']) ? $errors['email'] : ''; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                                <input type="tel" class="form-control" name="phone" id="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address"><i class="fas fa-home"></i> Address</label>
                                <input type="text" class="form-control" name="address" id="address" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['address']) ? $errors['address'] : ''; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <div class="gender-options">
                            <div class="form-check form-check-inline">
                                <input type="radio" name="gender" value="male" id="male" class="form-check-input" <?= (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'checked' : ''; ?> required>
                                <label for="male" class="form-check-label">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="female" name="gender" value="female" class="form-check-input" <?= (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'checked' : ''; ?>>
                                <label for="female" class="form-check-label">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" id="other" name="gender" value="other" class="form-check-input" <?= (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'checked' : ''; ?>>
                                <label for="other" class="form-check-label">Other</label>
                            </div>
                        </div>
                        <span class="error"><?= isset($errors['gender']) ? $errors['gender'] : ''; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-graduation-cap"></i> Wanted Courses</label>
                        <div class="course-options row">
                            <?php
                            $sql = "SELECT * FROM it_course";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<div class='col-md-4'>";
                                    echo "<div class='form-check'>";
                                    echo "<input type='checkbox' class='form-check-input' id='course_" . htmlspecialchars($row["id"]) . "' name='wanted_courses[]' value='" . htmlspecialchars($row["id"]) . "' " . ((isset($_POST['wanted_courses']) && in_array($row["id"], $_POST['wanted_courses'])) ? 'checked' : '') . ">";
                                    echo "<label class='form-check-label' for='course_" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["title"]) . "</label>";
                                    echo "</div>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                        <span class="error"><?= isset($errors['wanted_courses']) ? $errors['wanted_courses'] : ''; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="image"><i class="fas fa-image"></i> Profile Picture</label>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input" accept="image/*" required>
                            <label class="custom-file-label" for="image">Choose file</label>
                            <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                        </div>
                        <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
                        <span class="error"><?= isset($errors['image']) ? $errors['image'] : ''; ?></span>
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-user-plus"></i> Register Student</button>
                        <a href="<?= student_url() ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Students</a>
                        <a href="../../admin/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                    </div>
                </form>
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
            
            // Custom file input
            $('input[type="file"]').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Choose file');
            });
        });
    </script>
</body>
</html>