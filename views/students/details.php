  <?php
  session_start();
  include '../../config/config.php';

  if (isset($_GET['id'])) {
      $student_id = $_GET['id'];
  } elseif (isset($_SESSION['user_id'])) {
      $student_id = $_SESSION['user_id'];
  } else {
      header("Location: login.php");
      exit();
  }

  $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
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

  $stmt = $conn->prepare("SELECT c.* FROM it_course c JOIN student_course sc ON c.id = sc.course_id WHERE sc.student_id = ?");
  if (!$stmt) {
      die("Database query failed: " . $conn->error);
  }
  $stmt->bind_param("i", $student_id);
  $stmt->execute();
  $enrolled_courses = $stmt->get_result();

  $sql1 = "SELECT * FROM images WHERE student_id = ?";
  $stmt1 = $conn->prepare($sql1);
  if (!$stmt1) {
      die("Database query failed: " . $conn->error);
  }
  $stmt1->bind_param("i", $student_id);
  $stmt1->execute();
  $result1 = $stmt1->get_result();
  $row1 = $result1->fetch_assoc();

  ?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Student Details</title>
      <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
      <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
      <link rel="stylesheet" href="../../public/css/style.css">
  </head>

  <body>
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
          <a class="navbar-brand" href="#">Student Details</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="navbar-nav ml-auto">
                  <li class="nav-item">
                      <a class="nav-link" href="../../views/auth/logout.php">Logout</a>
                  </li>
              </ul>
          </div>
      </nav>

      <div class="container mt-4">
          <div class="row">
              <div class="col-md-3">
                  <div class="card">
                      <?php if ($row1 && isset($row1['images'])): ?>
                          <img src="../../public/picture/<?= htmlspecialchars($row1['images']); ?>" alt="Profile Picture" width="150" height="150" class="img-thumbnail">
                      <?php else: ?>
                          <img src="../../public/picture/default.jpg" alt="Default Profile Picture" width="150" height="150" class="img-thumbnail">
                      <?php endif; ?>
                      <div class="card-body">
                          <h5 class="card-title"><?= htmlspecialchars($student['fullname']); ?></h5>
                          <a href="views_profile.php?id=<?= $student_id; ?>" class="btn btn-primary btn-block">View Profile</a>
                      </div>
                  </div>
              </div>
              <div class="col-md-9">
                  <div class="card">
                      <div class="card-body">
                          <h5 class="card-title">Student Information</h5>
                          <p>Email: <?= htmlspecialchars($student['email']) ?></p>
                          <p>Phone: <?= htmlspecialchars($student['phone']) ?></p>
                      </div>
                  </div>

                  <div class="card mt-4">
                      <div class="card-body">
                          <h5 class="card-title">Enrolled Courses</h5>
                          <ul class="list-group">
                              <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                                  <li class="list-group-item d-flex justify-content-between align-items-center">
                                      <?= htmlspecialchars($course['title']); ?>
                                      <a href="../../views/payments/paymentviews.php?id=<?= urlencode($student_id); ?>&scid=<?= urlencode($course['id']); ?>" class="btn btn-sm btn-outline-primary">View Payments</a>
                                  </li>
                              <?php endwhile; ?>
                          </ul>
                      </div>
                  </div>
              </div>
          </div>
          <div class="mt-4">
              <a href="../../views/students/index.php" class="btn btn-secondary">Back to Students List</a>
          </div>
      </div>

      <script src="../../public/js/jquery-3.3.1.min.js"></script>
      <script src="../../public/js/popper.min.js"></script>
      <script src="../../public/js/bootstrap.min.js"></script>
  </body>

  </html>

  <?php
  $stmt->close();
  $stmt1->close();
  $conn->close();
  ?>
