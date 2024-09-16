  <?php
  include 'config.php';

  if (isset($_GET['id'])) {
      $id = $_GET['id'];
      $sql = "SELECT s.*, sc.course_id FROM students s LEFT JOIN student_course sc ON s.id = sc.student_id WHERE s.id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
      } else {
          echo "No record found";
          exit();
      }
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $id = $_POST['id'];
      $firstname = $_POST['firstname'];
      $lastname = $_POST['lastname'];
      $email = $_POST['email'];
      $phone = $_POST['phone'];
      $address = $_POST['address'];
      $gender = $_POST['gender'];
      $wanted_course = $_POST['wanted_course'];
    
      $sql = "UPDATE students SET firstname=?, lastname=?, email=?, phone=?, address=?, gender=? WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssi", $firstname, $lastname, $email, $phone, $address, $gender, $id);
    
      if ($stmt->execute()) {
          $sql_course = "UPDATE student_course SET course_id=? WHERE student_id=?";
          $stmt_course = $conn->prepare($sql_course);
          $stmt_course->bind_param("ii", $wanted_course, $id);
        
          if ($stmt_course->execute()) {
              $_SESSION['success_message'] = "Record updated successfully";
              header("Location: crud_display.php");
              exit();
          } else {
              echo "Error updating course: " . $conn->error;
          }
      } else {
          echo "Error updating record: " . $conn->error;
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
      <link rel="stylesheet" href="css/bootstrap.min.css">
      <link rel="stylesheet" href="css/style.css">
      <title>Edit Student Registration</title>
  </head>

  <body>
      <div class="content">
          <div class="container-fluid p-0">
              <div class="row no-gutters vh-100">
                  <div class="col-md-6 p-0">
                      <img src="images/undraw_remotely_2j6y.svg" alt="Registration Image" class="img-fluid h-100 w-100 object-fit-cover">
                  </div>
                  <div class="col-md-6 d-flex align-items-center justify-content-center p-0">
                      <div class="bg-white p-4 rounded w-100 h-100">
                          <div class="row justify-content-center h-100">
                              <div class="col-md-10">
                                  <div class="mb-4">
                                      <h2>Edit Student Registration</h2>
                                      <?php
                                      if (isset($_SESSION['success_message'])) {
                                          echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
                                          unset($_SESSION['success_message']);
                                      }
                                      if (isset($error_message)) {
                                          echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                      }
                                      ?>
                                      <form action="edit.php" method="post">
                                          <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                          <div class="form-group">
                                              <label for="firstname">First Name</label>
                                              <input type="text" class="form-control" name="firstname" value="<?php echo $row['firstname']; ?>" required>
                                          </div>
                                          <div class="form-group">
                                              <label for="lastname">Last Name</label>
                                              <input type="text" class="form-control" name="lastname" value="<?php echo $row['lastname']; ?>" required>
                                          </div>
                                          <div class="form-group">
                                              <label for="email">Email</label>
                                              <input type="email" class="form-control" name="email" value="<?php echo $row['email']; ?>" required>
                                          </div>
                                          <div class="form-group">
                                              <label for="phone">Phone</label>
                                              <input type="tel" class="form-control" name="phone" value="<?php echo $row['phone']; ?>" required>
                                          </div>
                                          <div class="form-group">
                                              <label for="address">Address</label>
                                              <input type="text" class="form-control" name="address" value="<?php echo $row['address']; ?>" required>
                                          </div>
                                          <div class="form-group">
                                              <label>Gender</label>
                                              <div class="gender-options">
                                                  <div class="form-check">
                                                      <input type="radio"   id="male"  name="gender" value="male" <?php if($row['gender'] == 'male') echo 'checked'; ?> required>
                                                      <label for="male">Male</label>
                                                  </div>
                                                  <div class="form-check">
                                                      <input type="radio" id="female" name="gender" value="female" <?php if($row['gender'] == 'female') echo 'checked'; ?> required>
                                                      <label for="female">Female</label>
                                                  </div>
                                                  <div class="form-check">
                                                      <input type="radio" id="other" name="gender" value="other" <?php if($row['gender'] == 'other') echo 'checked'; ?> required>
                                                      <label for="other">Other</label>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="form-group">
                                              <label>Wanted Course</label>
                                              <?php
                                              $sql = "SELECT * FROM it_course";
                                              $result = $conn->query($sql);

                                              if ($result->num_rows > 0) {
                                                  while ($course = $result->fetch_assoc()) {
                                                      echo "<div class='form-check'>";
                                                      echo "<input type='radio' id='course_" . htmlspecialchars($course["id"]) . "' name='wanted_course' value='" . htmlspecialchars($course["id"]) . "' " . ($row['course_id'] == $course['id'] ? 'checked' : '') . " required>";
                                                      echo "<label for='course_" . htmlspecialchars($course["id"]) . "'>" . htmlspecialchars($course["title"]) . "</label>";
                                                      echo "</div>";
                                                  }
                                              }
                                              ?>
                                          </div>
                                          <button type="submit" class="btn btn-primary">Update</button>
                                      </form>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <script src="js/jquery-3.3.1.min.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/main.js"></script>
  </body>
  </html>