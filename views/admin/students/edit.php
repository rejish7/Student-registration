<?php
session_start();
include '../../../config/config.php';
include '../../../config/url_helpers.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}

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
                            $_SESSION['success_message'] = "Student record updated successfully!";
                            redirect_admin('students');
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/bootstrap.min.css') ?>">
    <title>Edit Student</title>
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
            width: 33.33%;
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
        .gender-options {
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
        .form-check input[type="radio"] {
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
            .gender-options {
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
                            <h2><i class="fas fa-user-edit mr-2"></i> Edit Student</h2>
                            <p>Update student information</p>
                        </div>
                        
                        <div class="form-body">
                            <?php if (isset($row)): ?>
                            <!-- Progress Indicator -->
                            <div class="progress-container">
                                <div class="progress-step active" data-step="1">
                                    <div class="step-icon">1</div>
                                    <div class="step-text">Basic Info</div>
                                </div>
                                <div class="progress-step" data-step="2">
                                    <div class="step-icon">2</div>
                                    <div class="step-text">Contact Details</div>
                                </div>
                                <div class="progress-step" data-step="3">
                                    <div class="step-icon">3</div>
                                    <div class="step-text">Profile Picture</div>
                                </div>
                            </div>

                            <form action="<?= student_url($row['id'], 'edit') ?>" method="post" enctype="multipart/form-data" id="editForm">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                
                                <!-- Section 1: Basic Information -->
                                <div class="form-section active" id="section1">
                                    <h4 class="mb-4">Basic Information</h4>
                                    <div class="form-group">
                                        <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
                                        <input type="text" class="form-control" name="fullname" id="fullname" value="<?= htmlspecialchars($row['fullname']); ?>" required>
                                        <span class="error"><?= isset($errors['fullname']) ? $errors['fullname'] : ''; ?></span>
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
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= student_url() ?>'">Cancel</button>
                                        <button type="button" class="btn btn-primary next-btn">Next <i class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                                
                                <!-- Section 2: Contact Information -->
                                <div class="form-section" id="section2">
                                    <h4 class="mb-4">Contact Information</h4>
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($row['email']); ?>" required>
                                        <span class="error"><?= isset($errors['email']) ? $errors['email'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                                        <input type="tel" class="form-control" name="phone" id="phone" value="<?= htmlspecialchars($row['phone']); ?>" required>
                                        <span class="error"><?= isset($errors['phone']) ? $errors['phone'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="address"><i class="fas fa-home"></i> Address</label>
                                        <input type="text" class="form-control" name="address" id="address" value="<?= htmlspecialchars($row['address']); ?>" required>
                                        <span class="error"><?= isset($errors['address']) ? $errors['address'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary prev-btn"><i class="fas fa-arrow-left"></i> Previous</button>
                                        <button type="button" class="btn btn-primary next-btn">Next <i class="fas fa-arrow-right"></i></button>
                                    </div>
                                </div>
                                
                                <!-- Section 3: Profile Picture -->
                                <div class="form-section" id="section3">
                                    <h4 class="mb-4">Profile Picture</h4>
                                    <div class="form-group">
                                        <label><i class="fas fa-image"></i> Current Profile Picture</label>
                                        <div class="file-preview current-image">
                                            <?php if ($row1 && isset($row1['images'])): ?>
                                                <img src="../../public/picture/<?= htmlspecialchars($row1['images']); ?>" alt="Current Profile Picture" class="mb-2">
                                                <p><?= htmlspecialchars($row1['images']); ?></p>
                                            <?php else: ?>
                                                <p>No profile picture available</p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <label for="profile_picture" class="mt-4"><i class="fas fa-upload"></i> Upload New Picture</label>
                                        <label for="profile_picture" class="custom-file-upload" id="dropArea">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                            <p>Drag & drop a new photo here or click to browse</p>
                                            <p class="text-muted small">Max file size: 5MB. Formats: JPG, PNG, GIF</p>
                                        </label>
                                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display:none;">
                                        <input type="hidden" name="current_profile_picture" value="<?= isset($row1['images']) ? htmlspecialchars($row1['images']) : ''; ?>">
                                        <input type="hidden" name="MAX_FILE_SIZE" value="5000000">
                                        
                                        <div class="file-preview" id="imagePreview" style="display:none;">
                                            <img id="preview" src="#">
                                            <p id="fileName"></p>
                                        </div>
                                        <span class="error"><?= isset($errors['profile_picture']) ? $errors['profile_picture'] : ''; ?></span>
                                    </div>
                                    
                                    <div class="buttons-container">
                                        <button type="button" class="btn btn-secondary prev-btn"><i class="fas fa-arrow-left"></i> Previous</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Student</button>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset_url('js/jquery-3.3.1.min.js') ?>"></script>
    <script src="<?= asset_url('js/popper.min.js') ?>"></script>
    <script src="<?= asset_url('js/bootstrap.min.js') ?>"></script>
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
            
            // Image preview
            $('#profile_picture').change(function() {
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
                    const fileInput = document.getElementById('profile_picture');
                    fileInput.files = files;
                    $(fileInput).trigger('change'); // Trigger the change event for preview
                }
            }
        });
    </script>
</body>
</html>
