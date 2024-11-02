  <?php
  include '../../config/config.php';

  $errors = array();
  $request_id = isset($_GET['request_id']) ? $_GET['request_id'] : null;
  $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

  if (!$request_id || !$student_id) {
      $errors['no_id'] = "Request ID or Student ID not provided";
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $request_type = $_POST['request_type'];
      $subject = trim($_POST['subject']);
      $description = trim($_POST['description']);
      $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
      $status = $_POST['status'];
      $currentFile = $_POST['current_file'];
    
      // Validation
      if (empty($subject) || strlen($subject) < 2 || strlen($subject) > 100) {
          $errors['subject'] = "Subject is required and must be between 2 and 100 characters";
      }
      if (empty($description) || strlen($description) < 5) {
          $errors['description'] = "Description is required and must be at least 5 characters";
      }
      if ($request_type === 'payment' && $amount <= 0) {
          $errors['amount'] = "Amount must be greater than 0 for payment requests";
      }
      if (empty($status) || !in_array($status, ['pending', 'approved', 'rejected'])) {
          $errors['status'] = "Valid status selection is required";
      }

      // File upload validation
      if (!empty($_FILES['file']['name'])) {
          $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword'];
          $file_type = $_FILES['file']['type'];
          $file_size = $_FILES['file']['size'];
          
          if (!in_array($file_type, $allowed_types)) {
              $errors['file'] = "Invalid file type. Only PDF, JPG, PNG, and DOC are allowed.";
          }
          if ($file_size > 5000000) {
              $errors['file'] = "File size must be less than 5MB.";
          }
      }

      if (empty($errors)) {
          if (!empty($_FILES['file']['name'])) {
              $target_dir = "../../uploads/";
              if (!file_exists($target_dir)) {
                  mkdir($target_dir, 0777, true);
              }
              $target_file = $target_dir . basename($_FILES["file"]["name"]);
              if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                  $file = basename($_FILES["file"]["name"]);
              } else {
                  $errors['file_upload'] = "Failed to upload the file.";
              }
          } else {
              $file = $currentFile;
          }

          if (empty($errors)) {
              $sql = "UPDATE requests SET request_type=?, subject=?, description=?, amount=?, status=?, file=? WHERE id=? AND student_id=?";
              if ($stmt = $conn->prepare($sql)) {
                  $stmt->bind_param("sssdssis", $request_type, $subject, $description, $amount, $status, $file, $request_id, $student_id);
                  $result = $stmt->execute();

                  if ($result) {
                      echo "<script>alert('Request updated successfully!'); window.location.href='requestview.php?student_id=" . $student_id . "';</script>";
                      exit();
                  } else {
                      $errors['update'] = "Error updating request: " . $conn->error;
                  }
              } else {
                  $errors['statement'] = "Error preparing update statement: " . $conn->error;
              }
          }
      }
  }

  $sql = "SELECT r.*, s.fullname as student_name 
          FROM requests r 
          JOIN students s ON r.student_id = s.id 
          WHERE r.id = ? AND r.student_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $request_id, $student_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
      $errors['not_found'] = "Request not found";
  }

  $request = $result->fetch_assoc();

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
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
      <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
      <link rel="stylesheet" href="../../public/css/style.css">
      <title>Edit Request</title>
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
                          <h2>Edit Request</h2>
                          <?php if (isset($request)): ?>
                          <form action="" method="POST" enctype="multipart/form-data">
                              <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                              <div class="form-group">
                                  <label for="request_type"><i class="fas fa-file-alt"></i> Request Type</label>
                                  <select class="form-control" name="request_type" id="request_type" required>
                                      <option value="general" <?= $request['request_type'] === 'general' ? 'selected' : '' ?>>General</option>
                                      <option value="payment" <?= $request['request_type'] === 'payment' ? 'selected' : '' ?>>Payment</option>
                                      <option value="academic" <?= $request['request_type'] === 'academic' ? 'selected' : '' ?>>Academic</option>
                                  </select>
                                  <span class="error"><?= isset($errors['request_type']) ? $errors['request_type'] : ''; ?></span>
                              </div>
                              <div class="form-group">
                                  <label for="subject"><i class="fas fa-heading"></i> Subject</label>
                                  <input type="text" class="form-control" name="subject" value="<?= htmlspecialchars($request['subject']) ?>" required>
                                  <span class="error"><?= isset($errors['subject']) ? $errors['subject'] : ''; ?></span>
                              </div>
                              <div class="form-group">
                                  <label for="description"><i class="fas fa-align-left"></i> Description</label>
                                  <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($request['description']) ?></textarea>
                                  <span class="error"><?= isset($errors['description']) ? $errors['description'] : ''; ?></span>
                              </div>
                              <div class="form-group payment-field" <?= $request['request_type'] !== 'payment' ? 'style="display:none;"' : '' ?>>
                                  <label for="amount"><i class="fas fa-money-bill"></i> Amount</label>
                                  <input type="number" step="0.01" min="0" class="form-control" name="amount" value="<?= htmlspecialchars($request['amount']) ?>">
                                  <span class="error"><?= isset($errors['amount']) ? $errors['amount'] : ''; ?></span>
                              </div>
                              <div class="form-group">
                                  <label for="file"><i class="fas fa-paperclip"></i> Attachment</label>
                                  <?php if ($request['file']): ?>
                                      <div class="mb-2">
                                          <small>Current file: <?= htmlspecialchars(basename($request['file'])) ?></small>
                                      </div>
                                  <?php endif; ?>
                                  <input type="file" class="form-control" name="file">
                                  <input type="hidden" name="current_file" value="<?= htmlspecialchars($request['file']) ?>">
                                  <small class="form-text text-muted">Max file size: 5MB. Allowed formats: PDF, JPG, PNG, DOC</small>
                                  <span class="error"><?= isset($errors['file']) ? $errors['file'] : ''; ?></span>
                              </div>
                              <div class="form-group">
                                  <label for="status"><i class="fas fa-info-circle"></i> Status</label>
                                  <select class="form-control" name="status" required>
                                      <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                      <option value="approved" <?= $request['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                      <option value="rejected" <?= $request['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                  </select>
                                  <span class="error"><?= isset($errors['status']) ? $errors['status'] : ''; ?></span>
                              </div>
                              <div class="text-center">
                                  <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update</button>
                              </div>
                              <div class="text-center mt-2">
                                  <a href="requestview.php?student_id=<?= htmlspecialchars($student_id) ?>" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> Cancel</a>
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
      <script>
          $(document).ready(function() {
              $('select[name="request_type"]').change(function() {
                  if ($(this).val() === 'payment') {
                      $('.payment-field').show();
                  } else {
                      $('.payment-field').hide();
                      $('input[name="amount"]').val('0');
                  }
              });
          });
      </script>
  </body>
  </html>

  <?php
  $stmt->close();
  $conn->close();
  ?>