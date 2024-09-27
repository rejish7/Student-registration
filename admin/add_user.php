  <?php
include '../config/config.php';
session_start();

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $username = $_POST['username'];
      $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $role = $_POST['role'];

      $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sss", $username, $password, $role);

      if ($stmt->execute()) {
          $success_message = "User added successfully.";
      } else {
          $error_message = "Error: " . $stmt->error;
      }

      $stmt->close();
  }

  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
      <title>Add User</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

      <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
      <link rel="stylesheet" href="../public/css/bootstrap.min.css">
      <link rel="stylesheet" href="../public/css/style.css">
  </head>
  <body class="bg-light">
      <div class="container mt-5">
          <h2 class="mb-4 text-center">Add New User</h2>
          <?php
          if (isset($success_message)) {

              echo "<div class='alert alert-success'>" . htmlspecialchars($success_message) . "</div>";
          }
          if (isset($error_message)) {

              echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message) . "</div>";
          }
          ?>
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group">
                  <label for="username">Username:</label>
                  <input type="text" class="form-control" id="username" name="username" required>
              </div>
              <div class="form-group">
                  <label for="password">Password:</label>
                  <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="form-group">
                  <label for="role">Role:</label>
                  <select class="form-control" id="role" name="role" required>
                      <?php
                      $sql = "SELECT DISTINCT role FROM users ORDER BY role";
                      $result = $conn->query($sql);
                      if ($result->num_rows > 0) {
                          while($row = $result->fetch_assoc()) {
                              echo "<option value='" . htmlspecialchars($row['role']) . "'>" . htmlspecialchars($row['role']) . "</option>";
                          }
                      }
                      ?>
                  </select>
              </div>
              <button type="submit" class="btn btn-primary">Add User</button>
              <a href="manage_users.php" class="btn btn-secondary">Back to User Management</a>
          </form>
      </div>
      <script src="../js/jquery.min.js"></script>
      <script src="../js/popper.min.js"></script>
      <script src="../js/bootstrap.min.js"></script>
  </body>
  </html>
