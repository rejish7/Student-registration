<?php
 include '../auth/config.php'; 
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']); 
    $scid = intval($_POST['scid']);

    $sql = "UPDATE student_course SET course_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("ii", $course_id, $scid);
    
    if ($stmt->execute()) {
        header("Location: views.php?id=" . $student_id);
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$sql = "SELECT sc.*, s.firstname, s.lastname, c.title, c.duration, c.price 
        FROM student_course AS sc 
        JOIN students AS s ON s.id = sc.student_id 
        JOIN it_course AS c ON c.id = sc.course_id 
        WHERE sc.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $scid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found");
}

$row = $result->fetch_assoc();
$student_name = htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
?>

<!doctype html>
<html lang="en">

<head>
    <title>Edit Student Course</title>
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
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Edit Student Course</h2>
                </div>
            </div>
            <div>
                <h2>Student: <?= $student_name ?></h2>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                <th scope="col">Course name</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Price</th>
                                <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <form action="viewsedit.php?id=<?= $id ?>&scid=<?= $scid ?>" method="post">
                                        <input type="hidden" name="student_id" value="<?= $id ?>">
                                        <input type="hidden" name="scid" value="<?= $scid ?>">
                                        <td scope="col">
                                            <?php
                                            $courses = $conn->query("SELECT id, title FROM it_course");
                                            echo "<select name='course_id' required>";
                                            while ($course = $courses->fetch_assoc()) {
                                                $selected = ($course['id'] == $row['course_id']) ? 'selected' : '';
                                                echo "<option value='" . intval($course['id']) . "' {$selected}>" . htmlspecialchars($course['title']) . "</option>";
                                            }
                                            echo "</select>";
                                            ?>
                                        </td>
                                        <td scope="col"><?= htmlspecialchars($row['duration']) ?></td>
                                        <td scope="col"><?= htmlspecialchars($row['price']) ?></td>
                                        <td>
                                            <input type="submit" value="Save" class="btn btn-success">
                                        </td>
                                    </form>
                                </tr>
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