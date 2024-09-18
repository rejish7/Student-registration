<?php
include 'config.php';
include 'navbar.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['duration']) && isset($_POST['price'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $duration = $_POST['duration'];
        $price = $_POST['price'];
        $sql = "UPDATE it_course SET title = ?, duration = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $title, $duration, $price, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Course updated successfully!'); window.location.href='course.php';</script>";
        } else {
            echo "<script>alert('Error updating course: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$course = null;

if ($id) {
    $sql = "SELECT * FROM it_course WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
}

if (!$course) {
    echo "<script>alert('Course not found!'); window.location.href='course.php';</script>";
    exit;
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Update Course</title>
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
                    <h2 class="heading-section">Update the Course</h2>
                </div>
            </div>
            <div>
                <form method="post" action="">
                    <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                    <label for="title">Course Title:</label><br>
                    <input type="text" id="title" name="title" value="<?php echo $course['title']; ?>" required><br><br>
                    <label for="duration">Duration:</label><br>
                    <input type="text" id="duration" name="duration" value="<?php echo $course['duration']; ?>" required><br><br>
                    <label for="price">Price:</label><br>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo $course['price']; ?>" required><br><br>
                    <input type="submit" value="Update Course" class="btn btn-warning">
                </form>
            </div>
        </div>
    </section>
</body>
</html>