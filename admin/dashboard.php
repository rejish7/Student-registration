<?php
    include '../config/config.php';

    session_start();
        $query = "SELECT username FROM users WHERE id = ? AND role = 'admin'";
        $stmt = $conn->prepare($query);
    
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($row = $result->fetch_assoc()) {
                $admin_username = $row['username'];
            }
    
            $stmt->close();
        } else {
            echo "Error preparing query: " . $conn->error;
        }
    ?>
    <!doctype html>
    <html lang="en">

    <head>
        <title>Admin Dashboard - Student Registration Management</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="../public/css/style.css">
        
    </head>

    <body>
        <header>
            <div class="container">
                <div id="branding">
                    <h1><span class="highlight">Admin Dashboard</span></h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="logout.php" style="color: red;">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <div class="container">
                <h2 class="heading-section">Student Registration Management</h2>
                <div class="student-info">
                    <h3>Welcome, Admin <u><?php echo htmlspecialchars($admin_username);?></u></h3>
                </div>
                <div class="form-wrap">
                    <div class="btn-group">
                        <a href="../views/students/addstudent.php" class="btn"><i class="fa fa-user-plus"></i> Add Student</a>
                        <a href="../views/courses/courses.php" class="btn"><i class="fa fa-book"></i> View Course</a>
                        <a href="../views/students/crud_display.php" class="btn"><i class="fa fa-users"></i> View/Edit Students</a>
                        <a href="../admin/manage_users.php" class="btn"><i class="fa fa-cogs"></i> Manage Users</a>
                        <!-- <a href="generate_reports.php" class="btn"><i class="fa fa-file-text"></i> Generate Reports</a>
                        <a href="system_settings.php" class="btn"><i class="fa fa-wrench"></i> Manage Settings</a> -->
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>© <?php echo date('Y'); ?> Student Registration Management System by Rejish</p>
        </footer>
    </body>
    </html>
