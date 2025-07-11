  <?php
  session_start();
  include '../../config/config.php';
  include '../../config/url_helpers.php';

  if (isset($_GET['id'])) {
      $student_id = $_GET['id'];
  } elseif (isset($_SESSION['user_id'])) {
      $student_id = $_SESSION['user_id'];
  } else {
      redirect('auth/login');
  }

  $stmt = $conn->prepare("SELECT s.*, i.images FROM students s LEFT JOIN images i ON s.id = i.student_id WHERE s.id = ?");
  if (!$stmt) {
      die("Database query failed: " . $conn->error);
  }
  $stmt->bind_param("i", $student_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $student = $result->fetch_assoc();

  if (!$student) {
      die("Student not found.");
  }

  ?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Student Profile</title>
      <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
      <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
      <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
  </head>

  <body>
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
          <a class="navbar-brand" href="#">Student Profile</a>
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
          <div class="row">
              <div class="col-md-3">
                  <div class="card">
                      <?php if ($student['images']): ?>
                          <img src="../../public/picture/<?= htmlspecialchars($student['images']); ?>" alt="Profile Picture" width="150" height="150" class="img-thumbnail">
                      <?php else: ?>
                          <img src="../../public/picture/default.jpg" alt="Default Profile Picture" width="150" height="150" class="img-thumbnail">
                      <?php endif; ?>
                      <div class="card-body">
                          <h5 class="card-title"><?= htmlspecialchars($student['fullname']); ?></h5>
                      </div>
                  </div>
              </div>
              <div class="col-md-9">
                  <div class="card">
                      <div class="card-body">
                          <h4 class="card-title">Student Information</h4>
                          <p class="lead">Email: <?= htmlspecialchars($student['email']); ?></p>
                          <p class="lead">Phone: <?= htmlspecialchars($student['phone']); ?></p>
                          <p class="lead">Address: <?= htmlspecialchars($student['address']); ?></p>
                          <p class="lead">Gender: <?= htmlspecialchars($student['gender']); ?></p>
                      </div>
                  </div>
              </div>
          </div>
          <a href="<?php echo base_url('students/dashboard'); ?>" class="btn btn-primary mt-3">Back to Dashboard</a>
      </div>

      <script src="<?php echo asset_url('js/jquery-3.3.1.min.js'); ?>"></script>
      <script src="<?php echo asset_url('js/popper.min.js'); ?>"></script>
      <script src="<?php echo asset_url('js/bootstrap.min.js'); ?>"></script>
  </body>

  </html>
