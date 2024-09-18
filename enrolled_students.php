  <?php
  include 'config.php';
  include 'navbar.php';

  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  $scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <title>Enrolled Students</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

      <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>

      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

      <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
      <section class="ftco-section">
          <div class="container">
              <div class="row justify-content-center">
                  <div class="col-md-6 text-center mb-5">
                      <h2 class="heading-section">Enrolled Students</h2>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-12">
                      <div class="table-wrap">
                          <table class="table table-striped">
                              <thead>
                                  <tr>
                                      <th>Student ID</th>
                                      <th>Name</th>
                                      <th>Email</th>
                                      <th>Payment Date</th>
                                      <th>Course</th>
                                      <th>Remaining Balance</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  $sql = "SELECT sc.id AS student_course_id, s.id AS student_id, s.firstname, s.lastname, s.email, 
                                                 p.payment_date, c.title, c.price, 
                                                 IFNULL(SUM(p.amount), 0) as total_paid
                                          FROM students s
                                          JOIN student_course sc ON s.id = sc.student_id
                                          JOIN it_course c ON c.id = sc.course_id
                                          LEFT JOIN payments p ON sc.id = p.student_course_id
                                          GROUP BY sc.id";
                                  $stmt = $conn->prepare($sql);
                                  $stmt->execute();
                                  $result = $stmt->get_result();
                                  if ($result->num_rows > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                          $remaining_balance = $row['price'] - $row['total_paid'];
                                  ?>
                                          <tr>
                                              <td><?= htmlspecialchars($row['student_id']) ?></td>
                                              <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                              <td><?= htmlspecialchars($row['email']) ?></td>
                                              <td><?= htmlspecialchars(date('F j, Y', strtotime($row['payment_date']))) ?></td>
                                              <td><?= htmlspecialchars($row['title']) ?></td>
                                              <td><?= number_format($remaining_balance, 2) ?></td>
                                          </tr>
                                  <?php
                                      }
                                  } else {
                                      echo "<tr><td colspan='6'>No students are currently enrolled in any IT courses.</td></tr>";
                                  }
                                  ?>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
      </section>
  </body>
  </html>
  <?php
  $stmt->close();
  $conn->close();
  ?>