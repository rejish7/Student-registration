<style>
.error {color: #FF0000;}
</style>
<?php
include '../../config/config.php';

$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $wanted_course = isset($_POST['wanted_course']) ? $_POST['wanted_course'] : '';
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($fullname)) {
        $errors['fullname'] = "Full name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $fullname)) {
        $errors['fullname'] = "<span class='text-danger'>Only letters and white space allowed</span>";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors['phone'] = "Phone number is required";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Invalid phone number format";
    }

    if (empty($address)) {
        $errors['address'] = "Address is required";
    }

    if (empty($gender)) {
        $errors['gender'] = "Gender is required";
    }

    if (empty($wanted_course)) {
        $errors['wanted_course'] = "Please select a course";
    }

    if (empty($username)) {
        $errors['username'] = "Username is required";
    } elseif (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
        $errors['username'] = "Only letters and numbers allowed";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long";
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors['image'] = "Profile picture is required";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; 

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors['image'] = "Invalid file type. Please upload a JPG, PNG, or GIF file";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors['image'] = "File size exceeds 5MB limit";
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
                            $stmt1->bind_param("ii", $last_id, $wanted_course);
                            $result1 = $stmt1->execute();

                            if ($result1) {
                                $file_name = $_FILES['image']['name'];
                                $fileTmpName = $_FILES['image']['tmp_name'];
                                $folder = '../../public/images/' . basename($file_name);

                                $sql2 = "INSERT INTO images (Student_id, images) VALUES (?, ?)";
                                if ($stmt2 = $conn->prepare($sql2)) {
                                    $stmt2->bind_param("is", $last_id, $file_name);
                                    $result2 = $stmt2->execute();

                                    if ($result2) {
                                        if (move_uploaded_file($fileTmpName, $folder)) {
                                            echo "<script>alert('Thank you for registering!'); window.location.href='../../admin/dashboard.php';</script>";
                                        } else {
                                            echo "<script>alert('Failed to upload the file.');</script>";
                                        }
                                    } else {
                                        echo "<script>alert('Failed to insert file name into the database.');</script>";
                                    }
                                } else {
                                    echo "<script>alert('Error preparing image insert statement: " . $conn->error . "');</script>";
                                }
                            } else {
                                echo "<script>alert('Error inserting course: " . $conn->error . "');</script>";
                            }
                        } else {
                            echo "<script>alert('Error preparing course insert statement: " . $conn->error . "');</script>";
                        }
                    } else {
                        echo "<script>alert('Error inserting student: " . $conn->error . "');</script>";
                    }
                } else {
                    echo "<script>alert('Error preparing student insert statement: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Error inserting user: " . $conn->error . "');</script>";
            }

            $stmt_user->close();
        } else {
            echo "<script>alert('Error preparing user insert statement: " . $conn->error . "');</script>";
        }

        mysqli_close($conn);
    }
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
</head>

<body>
    <div class="content">
        <div class="container-fluid p-0">
            <div class="row no-gutters vh-100">
                <div class="col-md-6 p-0">
                    <img src="../../public/images/undraw_remotely_2j6y.svg" alt="Registration Image" class="img-fluid h-100 w-100 object-fit-cover">
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center p-0">
                    <div class="bg-white p-4 rounded w-100 h-100">
                        <div class="row justify-content-center h-100">
                            <div class="col-md-10">
                                <div class="mb-4">
                                    <h2>Student Registration</h2>
                                    <form action="addstudent.php" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                            <span class="error"><?= isset($errors['username']) ? $errors['username'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="fullname">Full Name</label>
                                            <input type="text" class="form-control" name="fullname" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
                                            <span class="error"><?= isset($errors['fullname']) ? $errors['fullname'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                            <span class="error"><?= isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" name="password" required>
                                            <span class="error"><?= isset($errors['password']) ? $errors['password'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="tel" class="form-control" name="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                            <span class="error"><?= isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <input type="text" class="form-control" name="address" value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                                            <span class="error"><?= isset($errors['address']) ? $errors['address'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label>Gender</label>
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
                                            <label>Wanted Course</label>
                                            <?php
                                            $sql = "SELECT * FROM it_course";
                                            $result = $conn->query($sql);

                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<div class='form-check'>";
                                                    echo "<input type='radio' id='course_" . htmlspecialchars($row["id"]) . "' name='wanted_course' value='" . htmlspecialchars($row["id"]) . "' " . ((isset($_POST['wanted_course']) && $_POST['wanted_course'] == $row["id"]) ? 'checked' : '') . " required>";
                                                    echo "<label for='course_" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["title"]) . "</label>";
                                                    echo "</div>";
                                                }
                                            }
                                            ?>
                                            <span class="error"><?= isset($errors['wanted_course']) ? $errors['wanted_course'] : ''; ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="profile_picture">Profile Picture</label>
                                            <br>
                                            <input type="file" name="image" id="image" accept="image/*" required>
                                            <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                                            <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
                                            <span class="error"><?= isset($errors['image']) ? $errors['image'] : ''; ?></span>
                                        </div>

                                        <br>
                                        <button type="submit" class="btn btn-primary">Register</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/popper.min.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>