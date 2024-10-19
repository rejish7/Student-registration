<?php
    include '../../config/config.php';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

$sql = "SELECT sc.*, s.fullname, c.title, c.duration, c.price 
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
$student_name = htmlspecialchars($row['fullname']);
?>

<!doctype html>
<html lang="en">

<head>
    <title>Payment Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
   
</head>

<body>
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section">Payment Details</h2>
                </div>
            </div>
            <div class="student-name">
                <i class="fa fa-user"></i> Student: <?= $student_name ?>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col"><i class="fa fa-book"></i> Course name</th>
                                    <th scope="col"><i class="fa fa-clock-o"></i> Duration</th>
                                    <th scope="col"><i class="fa fa-tag"></i> Price</th>
                                    <th scope="col"><i class="fa fa-money"></i> Paid Amount</th>
                                    <th scope="col"><i class="fa fa-balance-scale"></i> Remaining Amount</th>
                                    <th scope="col"><i class="fa fa-calendar"></i> Payment Date</th>
                                    <th scope="col"><i class="fa fa-cogs"></i> Action</th>
                                    <th scope="col"><i class="fa fa-plus-circle"></i> Add Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT p.id, p.amount, p.payment_date, c.title, c.duration, c.price 
                                        FROM payments p
                                        JOIN student_course sc ON p.student_course_id = sc.id
                                        JOIN it_course c ON sc.course_id = c.id
                                        WHERE sc.id = ?
                                        ORDER BY p.payment_date ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $scid);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                $total_paid = 0;
                                $course_price = 0;
                                
                                if ($result->num_rows > 0) {
                                    while ($payment = $result->fetch_assoc()) {
                                        $course_price = $payment['price'];
                                        $total_paid += $payment['amount'];
                                        $remaining_amount = $course_price - $total_paid;
                                ?>
                                    <tr>
                                        <td scope="col"><?= htmlspecialchars($payment['title']) ?></td>
                                        <td scope="col"><?= htmlspecialchars($payment['duration']) ?></td>
                                        <td scope="col">Rs<?= htmlspecialchars($payment['price']) ?></td>
                                        <td scope="col">Rs<?= htmlspecialchars($payment['amount']) ?></td>
                                        <td scope="col">Rs<?= htmlspecialchars($remaining_amount) ?></td>
                                        <td scope="col"><?= htmlspecialchars(date('F j, Y', strtotime($payment['payment_date']))) ?></td>
                                        <td>
                                            <a href="../../views/payments/paymentedit.php?id=<?= urlencode($payment['id']) ?>&scid=<?= urlencode($scid) ?>" class="btn btn-primary btn-sm">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <a href="../../views/payments/paymentdelete.php?id=<?= urlencode($payment['id']) ?>&scid=<?= urlencode($scid) ?>" onclick="return confirm('Are you sure you want to delete this record?')" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                        <td>
                                            <a href="../../views/payments/addpayment.php?id=<?= urlencode($id) ?>&scid=<?= urlencode($scid) ?>" class="btn btn-success btn-sm">
                                                <i class="fa fa-plus"></i> Add New Payment
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No payment records found</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="text-center mt-4">
                            <a href="../../views/courses/views.php?id=<?= urlencode($id) ?>" class="btn btn-info">
                                <i class="fa fa-arrow-left"></i> Back to Student Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../../public/js/jquery.min.js"></script>
    <script src="../../public/js/popper.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>
    <script src="../../public/js/main.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
