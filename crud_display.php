<?php
include 'config.php';
include 'navbar.php';

?>
<!doctype html>
<html lang="en">

<head>
    <title>Registered Students</title>
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
                <div class="col-md-6 text-center mb-3">
                    <h2 class="heading-section">Registered Students</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">S.N</th>
                                    <th scope="col">FirstName</th>
                                    <th scope="col">LastName</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Gender</th>
                                    <th scope="col">Enrolled Course</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM students";
                                $result = $conn->query($sql);
                                $i = 1;
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) { 
                                        ?>
						                <tr>
						                    <th scope="row"><?= $i++ ?></th>
                                        <th scope="row"><?= htmlspecialchars($row['firstname']) ?></th>
                                        <th scope="row"><?= htmlspecialchars($row['lastname']) ?></th>
                                        <th scope="row"><?= htmlspecialchars($row['email']) ?></th>
                                        <th scope="row"><?= htmlspecialchars($row['phone']) ?></th>
                                        <th scope="row"><?= htmlspecialchars($row['address']) ?></th>
                                        <th scope="row"><?= htmlspecialchars($row['gender']) ?></th>
                                            <th>
                                                <a href="views.php?id=<?= $row['id'] ?>"><i class="fa fa-eye btn btn-success"></i></a>
                                                <a href="addmorecourse.php?student_id=<?= urlencode($row['id']) ?>"><i class="fa fa-plus-circle btn btn-warning"></i></a>
                                            </th>
                                            <th>
                                                <a href="edit.php?id=<?= $row['id'] ?>"><i class="fa fa-edit btn btn-primary"></i></a>
                                                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fa fa-trash btn btn-danger"></i></a>
                                            </th>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='9'>No records found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>