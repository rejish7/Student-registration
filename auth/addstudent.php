<?php
 include '../auth/config.php'; 
 if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $wanted_course = $_POST['wanted_course'];

    $sql = "INSERT INTO students (firstname, lastname, email, phone, address, gender) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $firstname, $lastname, $email, $phone, $address, $gender);
    $result = $stmt->execute();

    if ($result) {
        $last_id = $conn->insert_id;
        $sql1 = "INSERT INTO student_course (student_id, course_id) VALUES (?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ii", $last_id, $wanted_course);
        $result1 = $stmt1->execute();

        if ($result1) {
            if (isset($_FILES['image'])) {
                $file_name = $_FILES['image']['name'];
                $fileTmpName = $_FILES['image']['tmp_name'];
                $folder = '../picture/' . $file_name;

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
                    $sql2 = "INSERT INTO images (Student_id, images) VALUES (?, ?)";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("is", $last_id, $file_name);
                    $result2 = $stmt2->execute();

                    if ($result2) {
                        if (move_uploaded_file($fileTmpName, $folder)) {
                            echo "<script>alert('Thank you for registering!'); window.location.href='crud_display.php';</script>";
                        } else {
                            echo "<script>alert('Failed to upload the file.');</script>";
                        }
                    } else {
                        echo "<script>alert('Failed to insert file name into the database.');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid file. Please upload a JPG, JPEG, PNG, or GIF file under 5MB.');</script>";
                }
            } else {
                echo "<script>alert('Profile picture is required.');</script>";
            }
        } else {
            echo "<script>alert('Error inserting course: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error inserting student: " . $conn->error . "');</script>";
    }

    // Close the prepared statements
    $stmt->close();
    $stmt1->close();
    $stmt2->close();
    mysqli_close($conn);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Student Registration</title>
</head>

<body>
    <div class="content">
        <div class="container-fluid p-0">
            <div class="row no-gutters vh-100">
                <div class="col-md-6 p-0">
                    <img src="../images/undraw_remotely_2j6y.svg" alt="Registration Image" class="img-fluid h-100 w-100 object-fit-cover">
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center p-0">
                    <div class="bg-white p-4 rounded w-100 h-100">
                        <div class="row justify-content-center h-100">
                            <div class="col-md-10">
                                <div class="mb-4">
                                    <h2>Student Registration</h2>
                                    <form action="addstudent.php" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="firstname">First Name</label>
                                            <input type="text" class="form-control" name="firstname" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="lastname">Last Name</label>
                                            <input type="text" class="form-control" name="lastname" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="tel" class="form-control" name="phone" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <input type="text" class="form-control" name="address" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Gender</label>
                                            <div class="gender-options">
                                                <div class="form-check">
                                                    <input type="radio" name="gender" value="male" id="male" required>
                                                    <label for="male">Male</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" id="female" name="gender" value="female">
                                                    <label for="female">Female</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="radio" id="other" name="gender" value="other">
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
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<div class='form-check'>";
                                                    echo "<input type='radio' id='course_" . htmlspecialchars($row["id"]) . "' name='wanted_course' value='" . htmlspecialchars($row["id"]) . "' required>";
                                                    echo "<label for='course_" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["title"]) . "</label>";
                                                    echo "</div>";
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="profile_picture">Profile Picture</label>
                                            <br>
                                            <input type="file" name="image" id="image" accept="image/*" required>
                                        </div>
                                        <br>
                                        <button type="submit" class="btn btn-primary">Register</button>
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
