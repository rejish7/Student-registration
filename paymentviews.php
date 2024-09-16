<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

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
    <title>Payment Details</title>
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
                    <h2 class="heading-section">Payment Details</h2>
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
                                    <th scope="col">Amount</th>
                                    <th scope="col">Remaining Amount</th>
                                    <th scope="col">Payment Date</th>
                                    <th scope="col">Action</th>
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
                                        <td scope="col"><?= htmlspecialchars($payment['price']) ?></td>
                                        <td scope="col"><?= htmlspecialchars($payment['amount']) ?></td>
                                        <td scope="col"><?= htmlspecialchars($remaining_amount) ?></td>
                                        <td scope="col"><?= htmlspecialchars(date('F j, Y', strtotime($payment['payment_date']))) ?></td>
                                        <td>
                                            <a href="paymentedit.php?id=<?= urlencode($payment['id']) ?>&scid=<?= urlencode($scid) ?>">
                                                <button class="btn btn-primary">Edit</button>
                                            </a>
                                            <a href="paymentdelete.php?id=<?= urlencode($payment['id']) ?>&scid=<?= urlencode($scid) ?>" onclick="return confirm('Are you sure you want to delete this record?')">
                                                <button class="btn btn-danger">Delete</button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7">No payment records found</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <a href="addpayment.php?id=<?= urlencode($id) ?>&scid=<?= urlencode($scid) ?>"><button class="btn btn-success">Add New Payment</button></a>
                        <a href="views.php?id=<?= urlencode($id) ?>"><button class="btn btn-info">Back to Student Details</button></a>
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
