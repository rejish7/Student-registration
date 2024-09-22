<?php
 include '../auth/config.php'; 
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

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body>
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Name:<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                    </h2>

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
                                        <a href="viewsedit.php?id=<?=urlencode($row['id']); ?>&scid=<?= urlencode($row1['scid']); ?>"
                                            class="btn btn-primary btn-sm mr-2">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="viewsdelete.php?id=<?= urlencode($row['id']); ?>&scid=<?= urlencode($row1['scid']); ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </td>

                                    <td>
                                        <a href="addpayment.php?id=<?=urlencode($row['id']); ?>&scid=<?= urlencode($row1['scid']); ?>"
                                            class="btn btn-primary btn-sm">
                                            <i class="fa fa-plus"></i> Add payment
                                        </a>
                                        <a href="paymentviews.php?id=<?= urlencode($row['id']); ?>&scid=<?= urlencode($row1['scid']); ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="fa fa-eye"></i> View
                                        </a>
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
                        <a href="crud_display.php"><i class="fa fa-arrow-left"></i> Back to students Enrolled</a>
                        <a href="addmorecourse.php?student_id=<?= urlencode($row['id']) ?>" class="btn btn-warning"><i
                                class="fa fa-plus"></i> ADD MORE COURSE</a>