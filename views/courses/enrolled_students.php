<?php
 include '../../config/config.php'; 
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Students</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Enrolled Students</h2>

        <?php
        $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($course_id) {
            $sql = "SELECT s.id, s.fullname, s.email 
                    FROM students s 
                    INNER JOIN student_course sc ON s.id = sc.student_id 
                    WHERE sc.course_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table class='table'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['fullname']) . "</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                          </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No students enrolled in this course.</p>";
            }
            $stmt->close();
        } else {
            echo "<p>Please provide a valid course ID in the URL.</p>";
        }
        ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
