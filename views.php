<?php
include 'config.php';
$id = ($_GET['id']) ? ($_GET['id']) : 0;
$sql1 = "SELECT * FROM students s WHERE s.id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $id);
$stmt1->execute();
$result1 = $stmt1->get_result();
$row = $result1->fetch_assoc();

if (!$row) {
    die("Student not found");
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Enrolled Course</title>
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
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Name:<?= htmlspecialchars($row['firstname']) ?></h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">S.N</th>
                                    <th scope="col">Course name</th>
                                    <th scope="col">Duration</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Payment Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT *,sc.id as scid FROM student_course AS sc
         JOIN students AS s ON s.id = sc.student_id 
         JOIN it_course AS c ON c.id = sc.course_id 
         WHERE s.id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $i = 1;
                                if ($result->num_rows > 0) {
                                    while ($row1 = $result->fetch_assoc()) {
                                ?>
                                        <tr>
                                            <td scope="row"><?= $i++ ?></td>
                                            <td scope="row"><?= htmlspecialchars($row1['title']) ?></td>
                                            <td scope="row"><?= htmlspecialchars($row1['duration']) ?></td>
                                            <td scope="row"><?= htmlspecialchars($row1['price']) ?></td>
                                            <td>
                                                <a href="viewsedit.php?id=<?php echo urlencode($row['id']); ?>&scid=<?php echo urlencode($row1['scid']); ?>">
                                                    <button class="btn btn-primary">Edit</button>
                                                </a>
                                                <a href="viewsdelete.php?id=<?php echo urlencode($row['id']); ?>&scid=<?php echo urlencode($row1['scid']); ?>" onclick="return confirm('Are you sure you want to delete this record?')">
                                                    <button class="btn btn-danger">Delete</button>
                                                </a>
                                            </td>

                                            <td>
                                                <a href="addpayment.php?id=<?php echo urlencode($row['id']); ?>&scid=<?php echo urlencode($row1['scid']); ?>"><button class="btn btn-primary ">Add payment</button></a>
                                                <a href="paymentviews.php?id=<?php echo urlencode($row['id']); ?>&scid=<?php echo urlencode($row1['scid']); ?>"><button class="btn btn-success">Views</button></a>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6">No course enrolled</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <a href="add_course.php?student_id=<?= urlencode($row['id']) ?>"><button class=" btn btn-warning">ADD MORE COURSE</button></a>