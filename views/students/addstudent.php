<?php
include '../../config/config.php';

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
                                            echo "<script>alert('Thank you for registering!'); window.location.href='../../admin/dashboard.php';</script>";
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
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
    <title>Student Registration</title>
    <style>
    .error {color: #FF0000;}
    </style>
</head>

<body>
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="form-container">
                        <h2>Student Registration</h2>
                        <form action="addstudent.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> Username</label>
                                <input type="text" class="form-control" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['username']) ? $errors['username'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                                <input type="text" class="form-control" name="fullname" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['fullname']) ? $errors['fullname'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['email']) ? $errors['email'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" class="form-control" name="password" required>
                                <span class="error"><?= isset($errors['password']) ? $errors['password'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                                <input type="tel" class="form-control" name="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                <span class="error"><?= isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="address"><i class="fas fa-home"></i> Address</label>
                                <input type="text" class="form-control" name="address" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
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
                            <div class="form-group">
                                <label><i class="fas fa-graduation-cap"></i> Wanted Courses</label>
                                <div class="course-options">
                                    <?php
                                    $sql = "SELECT * FROM it_course";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<div class='form-check custom-checkbox'>";
                                            echo "<input type='checkbox' class='custom-control-input' id='course_" . htmlspecialchars($row["id"]) . "' name='wanted_courses[]' value='" . htmlspecialchars($row["id"]) . "' " . ((isset($_POST['wanted_courses']) && in_array($row["id"], $_POST['wanted_courses'])) ? 'checked' : '') . ">";
                                            echo "<label class='custom-control-label' for='course_" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["title"]) . "</label>";
                                            echo "</div>";
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="error"><?= isset($errors['wanted_courses']) ? $errors['wanted_courses'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="profile_picture"><i class="fas fa-image"></i> Profile Picture</label>
                                <br>
                                <input type="file" name="image" id="image" accept="image/*" required>
                                <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                                <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
                                <span class="error"><?= isset($errors['image']) ? $errors['image'] : ''; ?></span>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/popper.min.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>
</body>
</html>