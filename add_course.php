<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['title']) && isset($_POST['duration']) && isset($_POST['price'])) {
        $title = $_POST['title'];
        $duration = $_POST['duration'];
        $price = $_POST['price'];
        $sql = "INSERT INTO it_course (title, duration, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssd", $title, $duration, $price);
        if ($stmt->execute()) {
            echo "<script>alert('Course added successfully!'); window.location.href='courses.php';</script>";
        } else {
            echo "<script>alert('Error adding course: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

?>


<!doctype html>
<html lang="en">

<head>
    <title>Add Course</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>



<body>
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Add New Course</h2>
                </div>
            </div>
            <div>
                <form method="post" action="">
                    <label for="title">Course Title:</label><br>
                    <input type="text" id="title" name="title" required><br><br>
                    <label for="duration">Duration:</label><br>
                    <input type="text" id="duration" name="duration" required><br><br>
                    <label for="price">Price:</label><br>
                    <input type="number" id="price" name="price" step="0.01" required><br><br>
                    <input type="submit" value="Add Course" class="btn btn-warning">
                </form>
            </div>
        </div>
    </section>
</body>

</html>