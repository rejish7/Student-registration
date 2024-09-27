  <?php
  session_start();
  include '../../config/config.php';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $username = trim($_POST['username']);
      $password = trim($_POST['password']);

      $errors = [];

      if (empty($username) || empty($password)) {
          $errors[] = "Username and password are required.";
      } else {
          $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("s", $username);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows === 1) {
              $user = $result->fetch_assoc();
              if (password_verify($password, $user['password'])) {
                  $_SESSION['user_id'] = $user['id'];
                  $_SESSION['username'] = $user['username'];
                  $_SESSION['role'] = $user['role'];
                                if ($user['role'] === 'admin') {
                                    header("Location:../../admin/dashboard.php");
                                } else {
                                    header("Location:../students/student_dashboard.php");
                                }
                  exit();
              } else {
                  $errors[] = "Invalid username or password.";
              }
          } else {
              $errors[] = "Invalid username or password.";
          }
          $stmt->close();
      }
  }
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Login</title>
      <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
  </head>
  <body>
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-md-6">
                  <div class="form-wrap">
                      <h2 class="text-center mb-4">Login</h2>
                      <?php if (!empty($errors)): ?>
                          <div class="alert alert-danger">
                              <?php foreach ($errors as $error): ?>
                                  <p><?php echo htmlspecialchars($error); ?></p>
                              <?php endforeach; ?>
                          </div>
                      <?php endif; ?>
                    
                      <form action="" method="post">
                          <div class="form-group">
                              <label for="username">Username</label>
                              <input type="text" class="form-control" id="username" name="username" required>
                          </div>
                          <div class="form-group">
                              <label for="password">Password</label>
                              <input type="password" class="form-control" id="password" name="password" required>
                          </div>
                          <button type="submit" class="btn btn-primary btn-block">Login</button>
                      </form>
                      <p class="text-center mt-3">Don't have an account? <a href="../../views/students/registration.php">Sign up</a></p>
                  </div>
              </div>
          </div>
      </div>
    
      <script src="/js/bootstrap.min.js"></script>
  </body>
  </html>
