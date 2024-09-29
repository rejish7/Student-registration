<?php
    include '../../config/config.php';
    ?>
<!doctype html>
<html lang="en">

<head>
    <title>Student Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-md-12 text-left mb-3">
                <a href="../../admin/dashboard.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>
    </div>

    <section class="ftco-section">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-4">
                    <h2 class="heading-section">Student Details</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="studentsTable" class="table table-striped table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>S.N</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Gender</th>
                                    <th>Profile Picture</th>
                                    <th>Enrolled Course</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT s.*, u.username 
                                FROM users u 
                                JOIN students s 
                                ON s.user_id = u.id 
                                WHERE u.role = 'user'";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $i = 1;
                                ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['phone']) ?></td>
                                            <td><?= htmlspecialchars($row['address']) ?></td>
                                            <td><?= htmlspecialchars($row['gender']) ?></td>
                                            <td>
                                                <?php
                                                $sql1 = "SELECT * FROM images WHERE student_id = ?";
                                                $stmt1 = $conn->prepare($sql1);
                                                $stmt1->bind_param("i", $row['id']);
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();

                                                if ($result1->num_rows > 0) {
                                                    $row1 = $result1->fetch_assoc();
                                                ?>
                                                    <img src="../../public/picture/<?= htmlspecialchars($row1['images']) ?>" alt="Profile Picture" width="50" height="50" class="img-thumbnail">
                                                <?php
                                                } else {
                                                ?>
                                                    <img src="../../public/picture/default.jpg" alt="Default Profile Picture" width="50" height="50" class="img-thumbnail">
                                                <?php
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="../courses/views.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm btn-action"><i class="fa fa-eye"></i></a>
                                                <a href="../courses/addmorecourses.php?student_id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm btn-action"><i class="fa fa-plus-circle"></i></a>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm btn-action"><i class="fa fa-edit"></i></a>
                                                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this record?')" class="btn btn-danger btn-sm btn-action"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='text-center'>No record found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

</body>

</html>