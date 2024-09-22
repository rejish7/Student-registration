  <?php
  session_start();
  include '../auth/config.php'; 

  ?>
  <!doctype html>
  <html lang="en">
  <head>
      <title>Admin Dashboard - Student Registration Management</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
      <link rel="stylesheet" href="../css/style.css">
      <link rel="stylesheet" href="../css/bootstrap.min.css">
  </head>
  <body>
      <section class="ftco-section">
          <div class="container">
              <div class="row justify-content-center">
                  <div class="col-md-12 text-center mb-5 dashboard-header">
                      <h2 class="heading-section">Admin Dashboard - Student Registration Management</h2>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-12">
                      <div class="dashboard-card">
                          <div class="row mb-4">
                              <div class="col-md-6">
                                  <h3>Welcome, Admin <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : $_SESSION['username']; ?></h3>
                              </div>
                              <div class="col-md-6 text-right">
                                  <a href="logout.php" class="btn btn-danger btn-custom"><i class="fa fa-sign-out"></i> Logout</a>
                              </div>
                          </div>
                          <div class="row">
                              <div class="col-md-4">
                                  <div class="dashboard-card">
                                      <h4><i class="fa fa-user-plus"></i> Add New Student</h4>
                                      <a href="../auth/addstudent.php" class="btn btn-success btn-block btn-custom">Add Student</a>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="dashboard-card">
                                      <h4><i class="fa fa-eye"></i>View course Listed</h4>
                                      <a href="../auth/courses.php" class="btn btn-warning btn-block btn-custom">View Course</a>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="dashboard-card">
                                      <h4><i class="fa fa-list"></i> Manage Students</h4>
                                      <a href="../auth/crud_display.php" class="btn btn-info btn-block btn-custom">View/Edit Students</a>
                                  </div>
                              </div>
                              <div class="col-md-4">
                                  <div class="dashboard-card">
                                      <h4><i class="fa fa-users"></i> User Management</h4>
                                      <a href="manage_users.php" class="btn btn-warning btn-block btn-custom">Manage Users</a>
                                  </div>
                              </div>
                          </div>
                          <div class="row mt-4">
                              <div class="col-md-6">
                                  <div class="dashboard-card">
                                      <h4><i class="fa fa-bar-chart"></i> Reports</h4>
                                      <a href="generate_reports.php" class="btn btn-primary btn-block btn-custom">Generate Reports</a>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="dashboard-card">
                                      <h4><i class="fa fa-cog"></i> System Settings</h4>
                                      <a href="system_settings.php" class="btn btn-secondary btn-block btn-custom">Manage Settings</a>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </section>
      <footer class="text-center py-3">
          <p>© <?php echo date('Y'); ?> Student Registration Management System by Rejish</p>
      </footer>
  </body>
  </html>
