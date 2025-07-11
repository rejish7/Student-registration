<?php
include '../../config/config.php';
include '../../config/url_helpers.php';

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
                                            echo "<script>alert('Thank you for registering!'); window.location.href='" . auth_url() . "';</script>";
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
    <!-- <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>"> -->
    <title>Student Registration</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f8fa;
            color: #333;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 30px;
            margin-bottom: 30px;
            transform: translateY(0);
            transition: all 0.3s ease;
            position: relative;
        }
        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .form-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 25px;
            text-align: center;
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .form-body {
            padding: 30px;
        }
        .form-section {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        .form-section.active {
            display: block;
        }
        .progress-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .progress-container::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .progress-step {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 25%;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #888;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .progress-step.active .step-icon, 
        .progress-step.completed .step-icon {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        .step-text {
            font-size: 12px;
            color: #888;
            font-weight: 500;
        }
        .progress-step.active .step-text,
        .progress-step.completed .step-text {
            color: #6a11cb;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        .form-group label {
            font-weight: 500;
            color: #444;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #2575fc;
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
        }
        .input-group {
            position: relative;
        }
        .input-group-append {
            position: absolute;
            right: 10px;
            top: 13px;
            z-index: 10;
        }
        .input-group-text {
            background: none;
            border: none;
            cursor: pointer;
            color: #888;
        }
        .input-group-text:hover {
            color: #2575fc;
        }
        .gender-options, .course-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .form-check {
            background-color: #f5f8fa;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-check:hover {
            border-color: #2575fc;
            background-color: rgba(37, 117, 252, 0.05);
        }
        .form-check input[type="radio"],
        .form-check input[type="checkbox"] {
            margin-right: 8px;
        }
        .custom-file-upload {
            display: block;
            padding: 20px;
            background-color: #f5f8fa;
            border: 2px dashed #2575fc;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .custom-file-upload:hover {
            background-color: rgba(37, 117, 252, 0.05);
        }
        .file-preview {
            margin-top: 15px;
            display: none;
            text-align: center;
        }
        .file-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        .btn {
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.5);
            background: linear-gradient(135deg, #5b0bb2 0%, #1e68e6 100%);
        }
        .btn-secondary {
            background-color: #e0e0e0;
            color: #444;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #d5d5d5;
            color: #333;
        }
        .buttons-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .form-body {
                padding: 20px;
            }
            .gender-options, .course-options {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            .step-text {
                display: none;
            }
            .progress-container {
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="form-container">
                        <div class="form-header">
                            <h2><i class="fas fa-user-graduate mr-2"></i> Student Registration</h2>
                            <p>Create your student account and join our courses</p>
                        </div>
                        <div class="form-body">
                            <!-- Progress Indicator -->
                            <div class="progress-container">
                                <div class="progress-step active" data-step="1">
                                    <div class="step-icon">1</div>
                                    <div class="step-text">Account</div>
                                </div>
                                <div class="progress-step" data-step="2">
                                    <div class="step-icon">2</div>
                                    <div class="step-text">Personal Info</div>
                                </div>
                                <div class="progress-step" data-step="3">
                                    <div class="step-icon">3</div>
                                    <div class="step-text">Preferences</div>
                                </div>
                                <div class="progress-step" data-step="4">
                                    <div class="step-icon">4</div>
                                    <div class="step-text">Upload</div>
                                </div>
                            </div>

                            <form action="<?php echo frontend_student_url('register'); ?>" method="post" enctype="multipart/form-data" id="registrationForm">
                                <!-- Section 1: Account Details -->
                                <div class="form-section active" id="section1">
                                    <h4 class="mb-4">Account Details</h4>
                                    <div class="form-group">
                                        <label for="username"><i class="fas fa-user"></i> Username</label>
                                        <input type="text" class="form-control" name="username" id="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                        <span class="error"><?= isset($errors['username']) ? $errors['username'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" class="form-control" name="email" id="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                        <span class="error"><?= isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="password" id="password" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text" onclick="togglePassword()">
                                                    <i class="fas fa-eye" id="togglePassword"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <span class="error"><?= isset($errors['password']) ? $errors['password'] : ''; ?></span>
                                        <small class="form-text text-muted">Password must be at least 8 characters with uppercase, lowercase, number and special character</small>
                                    </div>
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary" disabled>Previous</button>
                                        <button type="button" class="btn btn-primary next-btn">Next <i class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                                
                                <!-- Section 2: Personal Information -->
                                <div class="form-section" id="section2">
                                    <h4 class="mb-4">Personal Information</h4>
                                    <div class="form-group">
                                        <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                                        <input type="text" class="form-control" name="fullname" id="fullname" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
                                        <span class="error"><?= isset($errors['fullname']) ? $errors['fullname'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                                        <input type="tel" class="form-control" name="phone" id="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                        <span class="error"><?= isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="address"><i class="fas fa-home"></i> Address</label>
                                        <input type="text" class="form-control" name="address" id="address" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                                        <span class="error"><?= isset($errors['address']) ? $errors['address'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                                        <div class="gender-options">
                                            <div class="form-check">
                                                <input type="radio" name="gender" value="male" id="male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'checked' : ''; ?> required>
                                                <label for="male">Male</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="female" name="gender" value="female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'checked' : ''; ?>>
                                                <label for="female">Female</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="other" name="gender" value="other" <?= (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'checked' : ''; ?>>
                                                <label for="other">Other</label>
                                            </div>
                                        </div>
                                        <span class="error"><?= isset($errors['gender']) ? $errors['gender'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary prev-btn"><i class="fas fa-arrow-left"></i> Previous</button>
                                        <button type="button" class="btn btn-primary next-btn">Next <i class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                                
                                <!-- Section 3: Course Preferences -->
                                <div class="form-section" id="section3">
                                    <h4 class="mb-4">Course Preferences</h4>
                                    <div class="form-group">
                                        <label><i class="fas fa-graduation-cap"></i> Select Your Courses</label>
                                        <div class="course-options">
                                            <?php
                                            $sql = "SELECT * FROM it_course";
                                            $result = $conn->query($sql);

                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<div class='form-check custom-checkbox'>";
                                                    echo "<input type='checkbox' id='course_" . htmlspecialchars($row["id"]) . "' name='wanted_courses[]' value='" . htmlspecialchars($row["id"]) . "' " . ((isset($_POST['wanted_courses']) && in_array($row["id"], $_POST['wanted_courses'])) ? 'checked' : '') . ">";
                                                    echo "<label for='course_" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["title"]) . "</label>";
                                                    echo "</div>";
                                                }
                                            }
                                            ?>
                                        </div>
                                        <span class="error"><?= isset($errors['wanted_courses']) ? $errors['wanted_courses'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary prev-btn"><i class="fas fa-arrow-left"></i> Previous</button>
                                        <button type="button" class="btn btn-primary next-btn">Next <i class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                                
                                <!-- Section 4: Profile Picture -->
                                <div class="form-section" id="section4">
                                    <h4 class="mb-4">Profile Picture</h4>
                                    <div class="form-group">
                                        <label for="profile_picture"><i class="fas fa-image"></i> Upload Your Photo</label>
                                        <label for="image" class="custom-file-upload" id="dropArea">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                            <p>Drag & drop your photo here or click to browse</p>
                                            <p class="text-muted small">Max file size: 5MB. Formats: JPG, PNG, GIF</p>
                                        </label>
                                        <input type="file" name="image" id="image" accept="image/*" style="display:none;" required>
                                        <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                                        <div class="file-preview" id="imagePreview">
                                            <img id="preview" src="#">
                                            <p id="fileName"></p>
                                        </div>
                                        <span class="error"><?= isset($errors['image']) ? $errors['image'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary prev-btn"><i class="fas fa-arrow-left"></i> Previous</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Complete Registration</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo asset_url('js/jquery-3.3.1.min.js'); ?>"></script>
    <script src="<?php echo asset_url('js/popper.min.js'); ?>"></script>
    <script src="<?php echo asset_url('js/bootstrap.min.js'); ?>"></script>
    <script>
        $(document).ready(function() {
            // Navigation between form sections
            $('.next-btn').click(function() {
                let currentSection = $(this).closest('.form-section');
                let nextSection = currentSection.next('.form-section');
                let currentStepNum = parseInt(currentSection.attr('id').replace('section', ''));
                let nextStepNum = currentStepNum + 1;
                
                // Simple validation before proceeding
                let canProceed = true;
                currentSection.find('input[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('is-invalid');
                        canProceed = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (canProceed) {
                    currentSection.removeClass('active');
                    nextSection.addClass('active');
                    
                    // Update progress indicator
                    $('.progress-step').removeClass('active');
                    $('.progress-step[data-step="' + nextStepNum + '"]').addClass('active');
                    for (let i = 1; i < nextStepNum; i++) {
                        $('.progress-step[data-step="' + i + '"]').addClass('completed');
                    }
                }
            });
            
            $('.prev-btn').click(function() {
                let currentSection = $(this).closest('.form-section');
                let prevSection = currentSection.prev('.form-section');
                let currentStepNum = parseInt(currentSection.attr('id').replace('section', ''));
                let prevStepNum = currentStepNum - 1;
                
                currentSection.removeClass('active');
                prevSection.addClass('active');
                
                // Update progress indicator
                $('.progress-step').removeClass('active');
                $('.progress-step[data-step="' + prevStepNum + '"]').addClass('active');
                $('.progress-step[data-step="' + currentStepNum + '"]').removeClass('completed');
            });
            
            // Toggle password visibility
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('togglePassword');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
            
            // Make the togglePassword function globally available
            window.togglePassword = togglePassword;
            
            // Image preview
            $('#image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').css('display', 'block');
                        $('#preview').attr('src', e.target.result);
                        $('#fileName').text(file.name);
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Drag and drop functionality
            const dropArea = document.getElementById('dropArea');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropArea.style.backgroundColor = 'rgba(37, 117, 252, 0.1)';
                dropArea.style.borderColor = '#2575fc';
            }
            
            function unhighlight() {
                dropArea.style.backgroundColor = '#f5f8fa';
                dropArea.style.borderColor = '#2575fc';
            }
            
            dropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length) {
                    const fileInput = document.getElementById('image');
                    fileInput.files = files;
                    $(fileInput).trigger('change'); // Trigger the change event for preview
                }
            }
        });
    </script>
</body>
</html>
