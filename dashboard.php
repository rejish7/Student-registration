  <?php
  session_start();
  include 'config.php';
  include 'navbar.php';
  ?>
  <!doctype html>
  <html lang="en">
  <head>
      <title>Student Registration Management Dashboard</title>
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
                      <h2 class="heading-section">Student Registration Management Dashboard</h2>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-12">
                      <div class="form-wrap">
                          <div class="row mb-4">
                              <div class="col-md-6">
                                  <h3>Welcome,User</h3>
                              </div>
                              <div class="col-md-6 text-right">
                                  <a href="#logout.php" class="btn btn-primary"><i class="fa fa-sign-out"></i> Logout</a>
                              </div>
                          </div>
                          <div class="row">
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <h4>Add New Student</h4>
                                      <a href="index.php" class="btn btn-success btn-block"><i class="fa fa-user-plus"></i> Add Student</a>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="form-group">
                                      <h4>Registered Students</h4>
                                      <a href="crud_display.php" class="btn btn-info btn-block"><i class="fa fa-list"></i> View Registered Students</a>
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
