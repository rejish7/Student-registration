  <?php
  include '../../config/config.php';

  $errors = array();
  $success_message = '';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $request_type = isset($_POST['request_type']) ? trim($_POST['request_type']) : '';
      $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
      $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
      $description = isset($_POST['description']) ? trim($_POST['description']) : '';
      $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';

    
      if (empty($request_type) || !in_array($request_type, ['payment', 'document', 'other'])) {
          $errors[] = "Please select a valid request type";
      }

      if (empty($student_id)) {
          $errors[] = "Student ID is required";
      }

      if (empty($subject) || strlen($subject) < 5 || strlen($subject) > 100) {
          $errors[] = "Subject is required and must be between 5 and 100 characters";
      }

      if (empty($description) || strlen($description) < 10 || strlen($description) > 500) {
          $errors[] = "Description is required and must be between 10 and 500 characters";
      }

      if ($request_type === 'payment' && (empty($amount) || !is_numeric($amount) || $amount <= 0)) {
          $errors[] = "Valid amount is required for payment requests";
      }

      if ($request_type === 'payment' && (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] == UPLOAD_ERR_NO_FILE)) {
          $errors[] = "File attachment is required for payment requests";
      }

      if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] != UPLOAD_ERR_NO_FILE) {
          $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
          $file_type = mime_content_type($_FILES['attachment']['tmp_name']);
          $file_size = $_FILES['attachment']['size'];
        
          if (!in_array($file_type, $allowed_types)) {
              $errors[] = "Invalid file type. Only PDF, JPG, PNG, GIF and DOC/DOCX are allowed.";
          }
          if ($file_size > 5000000) { 
              $errors[] = "File size must not exceed 5MB.";
          }
      }

      if (empty($errors)) {
          $attachment_path = null;
          if ($request_type === 'payment') {
              if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] != 0) {
                  $errors[] = "File attachment is required for payment requests";
              } else {
                  $upload_dir = '../../uploads/';
                  
                  if (!file_exists($upload_dir)) {
                      mkdir($upload_dir, 0777, true);
                  }
                  
                  $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
                  $file_name = uniqid() . '_' . time() . '.' . $file_extension;
                  $attachment_path = $upload_dir . $file_name;
                  
                  $file_type = mime_content_type($_FILES['attachment']['tmp_name']);
                  $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                  
                  if (!in_array($file_type, $allowed_types)) {
                      $errors[] = "Invalid file type detected.";
                  } else {
                      if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path)) {
                          $errors[] = "Failed to upload file. Please check directory permissions.";
                      } else {
                          chmod($attachment_path, 0644);
                      }
                  }
              }
          } else if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
              $upload_dir = '../../uploads/';
              
              if (!file_exists($upload_dir)) {
                  mkdir($upload_dir, 0777, true);
              }
              
              $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
              $file_name = uniqid() . '_' . time() . '.' . $file_extension;
              $attachment_path = $upload_dir . $file_name;
              
              $file_type = mime_content_type($_FILES['attachment']['tmp_name']);
              $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
              
              if (!in_array($file_type, $allowed_types)) {
                  $errors[] = "Invalid file type detected.";
              } else {
                  if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path)) {
                      $errors[] = "Failed to upload file. Please check directory permissions.";
                  } else {
                      chmod($attachment_path, 0644);
                  }
              }
          }

          if (empty($errors)) {
              $sql = "INSERT INTO requests (student_id, request_type, subject, description, amount, file, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
              
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("ssssss", 
                  $student_id,
                  $request_type,
                  $subject,
                  $description,
                  $amount,
                  $attachment_path
              );

              if ($stmt->execute()) {
                  $success_message = "Request submitted successfully!";
                  echo "<script>
                          setTimeout(function() {
                              alert('" . $success_message . "');
                              window.location.href = '" . base_url('students/dashboard?student_id=' . urlencode($student_id)) . "';
                          }, 100);
                      </script>";
                  exit();
              } else {
                  $errors[] = "Error saving request: " . $conn->error;
                  if ($attachment_path && file_exists($attachment_path)) {
                      unlink($attachment_path);
                  }
              }
              $stmt->close();
          }
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
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Student Request Form</title>
      <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
      <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
      <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
      <style>
      .error {color: #FF0000;}
      </style>
  </head>
  <body>
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
          <a class="navbar-brand" href="#">Student Request Form</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="navbar-nav ml-auto">
                  <li class="nav-item">
                      <a class="nav-link" href="<?php echo auth_url('logout'); ?>">Logout</a>
                  </li>
              </ul>
          </div>
      </nav>

      <div class="container mt-4">
          <div class="card">
              <div class="card-header">
                  <h5 class="card-title">Student Request Form</h5>
              </div>
              <div class="card-body">
                  <form action="<?php echo frontend_request_url($student_id, 'add'); ?>" method="POST" enctype="multipart/form-data">
                      <div class="form-group mb-3">
                          <label>Request Type</label>
                          <select name="request_type" class="form-control" required>
                              <option value="">Select Request Type</option>
                              <option value="general" <?= (isset($_POST['request_type']) && $_POST['request_type'] == 'general') ? 'selected' : ''; ?>>General Request</option>
                              <option value="payment" <?= (isset($_POST['request_type']) && $_POST['request_type'] == 'payment') ? 'selected' : ''; ?>>Payment Request</option>
                              <option value="academic" <?= (isset($_POST['request_type']) && $_POST['request_type'] == 'academic') ? 'selected' : ''; ?>>Academic Request</option>
                          </select>
                          <?php if (isset($errors[0]) && strpos($errors[0], 'request type') !== false): ?>
                              <span class="error"><?= $errors[0] ?></span>
                          <?php endif; ?>
                      </div>

                      <div class="form-group mb-3">
                          <label>Student ID</label>
                          <input type="text" name="student_id" class="form-control" value="<?= isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : ''; ?>" readonly required>
                          <?php if (isset($errors[1]) && strpos($errors[1], 'Student ID') !== false): ?>
                              <span class="error"><?= $errors[1] ?></span>
                          <?php endif; ?>
                      </div>

                      <div class="form-group mb-3">
                          <label>Subject</label>
                          <input type="text" name="subject" class="form-control" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                          <?php if (isset($errors[2]) && strpos($errors[2], 'Subject') !== false): ?>
                              <span class="error"><?= $errors[2] ?></span>
                          <?php endif; ?>
                      </div>

                      <div class="form-group mb-3">
                          <label>Description</label>
                          <textarea name="description" class="form-control" rows="4" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                          <?php if (isset($errors[3]) && strpos($errors[3], 'Description') !== false): ?>
                              <span class="error"><?= $errors[3] ?></span>
                          <?php endif; ?>
                      </div>

                      <div class="form-group mb-3" id="amountField" style="display: none;">
                          <label>Amount (if payment request)</label>
                          <input type="number" name="amount" class="form-control" value="<?= isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                          <?php if (isset($errors[4]) && strpos($errors[4], 'amount') !== false): ?>
                              <span class="error"><?= $errors[4] ?></span>
                          <?php endif; ?>
                      </div>

                      <div class="form-group mb-3" id="attachmentField" style="display: none;">
                          <label>Attachment (if payment request)</label>
                          <input type="file" name="attachment" class="form-control">
                          <small class="form-text text-muted">Max file size: 5MB. Allowed formats: PDF, JPG, PNG, GIF, DOC, DOCX</small>
                          <?php if (isset($errors[5]) && strpos($errors[5], 'attachment') !== false): ?>
                              <span class="error"><?= $errors[5] ?></span>
                          <?php endif; ?>
                      </div>

                      <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Submit Request</button>
                      <a href="<?php echo base_url('students/dashboard'); ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                  </form>
              </div>
          </div>
      </div>

      <script src="<?php echo asset_url('js/jquery-3.3.1.min.js'); ?>"></script>
      <script src="<?php echo asset_url('js/popper.min.js'); ?>"></script>
      <script src="<?php echo asset_url('js/bootstrap.min.js'); ?>"></script>
      <script>
      $(document).ready(function() {
          $('select[name="request_type"]').change(function() {
              if ($(this).val() === 'payment') {
                  $('#amountField, #attachmentField').show();
              } else {
                  $('#amountField, #attachmentField').hide();
              }
          });
          
          // Trigger change event on page load if payment is selected
          if ($('select[name="request_type"]').val() === 'payment') {
              $('#amountField, #attachmentField').show();
          }
      });
      </script>
  </body>
  </html>
