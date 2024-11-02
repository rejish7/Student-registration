<?php
include '../../config/config.php';

$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $currentProfilePicture = $_POST['current_profile_picture'];

    // Validation
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

    // File upload validation
    if (!empty($_FILES['profile_picture']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['profile_picture'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
        if ($file_size > 5000000 || $file_size < 1000) {
            $errors['profile_picture'] = "File size must be between 1KB and 5MB.";
        }
    }

    if (empty($errors)) {
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "../../public/picture/";
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = basename($_FILES["profile_picture"]["name"]);
            } else {
                $errors['file_upload'] = "Failed to upload the file.";
            }
        } else {
            $profile_picture = $currentProfilePicture;
        }

        if (empty($errors)) {
            $sql = "UPDATE students SET fullname=?, email=?, phone=?, address=?, gender=? WHERE id=?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssssi", $fullname, $email, $phone, $address, $gender, $id);
                $result = $stmt->execute();

                if ($result) {
                    $sql_image = "UPDATE images SET images=? WHERE student_id=?";
                    if ($stmt_image = $conn->prepare($sql_image)) {
                        $stmt_image->bind_param("si", $profile_picture, $id);
                        $result_image = $stmt_image->execute();

                        if ($result_image) {
                            echo "<script>alert('Record updated successfully!'); window.location.href='crud_display.php';</script>";
                            exit();
                        } else {
                            $errors['image_update'] = "Error updating image: " . $conn->error;
                        }
                    } else {
                        $errors['image_statement'] = "Error preparing image update statement: " . $conn->error;
                    }
                } else {
                    $errors['student_update'] = "Error updating student: " . $conn->error;
                }
            } else {
                $errors['student_statement'] = "Error preparing student update statement: " . $conn->error;
            }
        }
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $sql1 = "SELECT * FROM images WHERE student_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $row1 = $result1->fetch_assoc();
    
    if (!$row) {
        $errors['not_found'] = "Error: Student data not found.";
    }
} else {
    $errors['no_id'] = "Error: No student ID provided.";
}

// Display errors at the top of the form
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
    <title>Edit Student</title>
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
                        <h2>Edit Student</h2>
                        <?php if (isset($row)): ?>
                        <form action="edit.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <div class="form-group">
                                <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                                <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($row['fullname']); ?>" required>
                                <span class="error"><?= isset($errors['fullname']) ? $errors['fullname'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($row['email']); ?>" required>
                                <span class="error"><?= isset($errors['email']) ? $errors['email'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($row['phone']); ?>" required>
                                <span class="error"><?= isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="address"><i class="fas fa-home"></i> Address</label>
                                <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($row['address']); ?>" required>
                                <span class="error"><?= isset($errors['address']) ? $errors['address'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-venus-mars"></i> Gender</label>
                                <div class="gender-options">
                                    <div class="form-check">
                                        <input type="radio" name="gender" value="male" id="male" <?= ($row['gender'] == 'male') ? 'checked' : ''; ?> required>
                                        <label for="male">Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" id="female" name="gender" value="female" <?= ($row['gender'] == 'female') ? 'checked' : ''; ?>>
                                        <label for="female">Female</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" id="other" name="gender" value="other" <?= ($row['gender'] == 'other') ? 'checked' : ''; ?>>
                                        <label for="other">Other</label>
                                    </div>
                                </div>
                                <span class="error"><?= isset($errors['gender']) ? $errors['gender'] : ''; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="profile_picture"><i class="fas fa-image"></i> Profile Picture</label>
                                <br>
                                <?php if ($row1 && isset($row1['images'])): ?>
                                    <img src="../../public/picture/<?= htmlspecialchars($row1['images']); ?>" alt="Current Profile Picture" width="100" class="mb-2">
                                <?php endif; ?>
                                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                                <input type="hidden" name="current_profile_picture" value="<?= isset($row1['images']) ? htmlspecialchars($row1['images']) : ''; ?>">
                                <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                                <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
                                <span class="error"><?= isset($errors['profile_picture']) ? $errors['profile_picture'] : ''; ?></span>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update</button>
                            </div>
                            <div class="text-center mt-2">
                                                            <a href="crud_display.php" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> Cancel</a>
                                                        </div>
                            
                        </form>
                        <?php endif; ?>
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
