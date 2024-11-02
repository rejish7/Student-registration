<?php
include '../config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4 text-center">User Management</h2>
        
                <div class="text-right mb-3">
                    <a href="add_user.php" class="btn btn-success"><i class="fa fa-plus"></i> Add New User</a>
                </div>
                <div class="text-left mb-3">
                                    <a href="dashboard.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                                </div>
                
        
        <?php
        $sql = "SELECT * FROM users ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END, username";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-hover table-striped'>";
            echo "<thead class='thead-dark'>";
            echo "<tr><th>Username</th><th>Role</th><th>Actions</th></tr>";
            echo "</thead>";
            echo "<tbody>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>";
                echo "<a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary mr-2'><i class='fa fa-edit'></i> Edit</a>";
                echo "<a href='delete_user.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\");'><i class='fa fa-trash'></i> Delete</a>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-info'>No users found.</div>";
        }

        mysqli_close($conn);
        ?>
    </div>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
