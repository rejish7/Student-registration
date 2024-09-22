<?php
 include '../auth/config.php'; ?>
 <header>
 <link rel="stylesheet" href="../css/style.css">
 <link rel="stylesheet" href="../css/bootstrap.min.css">
 </header>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4">Course Management</h2>
                <a href="add_course.php" class="btn btn-primary mb-3">Add New Course</a>
                <?php
                $sql = "SELECT * FROM it_course";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                $courses = [];
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $courses[] = [
                            'id' => $row['id'],
                            'course_name' => $row['title']
                        ];
                    }
                }

                if (empty($courses)) {
                    echo "<p>No courses found.</p>";
                } else {
                    foreach ($courses as $course) {
                        echo "<div class='card mb-4'>";
                        echo "<div class='card-header'>";
                        echo "<h3>" . htmlspecialchars($course['course_name']) . "</h3>";
                        echo "</div>";
                        echo "<div class='card-body'>";
                        echo "<p class='card-text'>Course ID: " . htmlspecialchars($course['id']) . "</p>";

                        echo "<a href='delete_course.php?id=" . urlencode($course['id']) . "' class='btn btn-danger mt-3'>Delete Course</a>";

                        echo "<a href='update_course.php?id=" . urlencode($course['id']) . "' class='btn btn-warning mt-3 ml-2'>Update Course</a>";
                        echo "<a href='enrolled_students.php?id=" . urlencode($course['id']) . "' class='btn btn-success mt-3 ml-2'>Enrolled Students</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script src="css/bootstrap.min.js"></script>