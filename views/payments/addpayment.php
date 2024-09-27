<?php
    include '../../config/config.php';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_course_id = intval($_POST['student_course_id']);
    $amount = floatval($_POST['paid_amount']);
    $payment_date = $_POST['payment_date'];

    $sql_check = "SELECT c.price, IFNULL(SUM(p.amount), 0) as total_paid 
                  FROM student_course sc
                  JOIN it_course c ON c.id = sc.course_id
                  LEFT JOIN payments p ON p.student_course_id = sc.id
                  WHERE sc.id = ?
                  GROUP BY sc.id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $student_course_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    
    $course_price = $row_check['price'];
    $total_paid = $row_check['total_paid'];
    $remaining_amount = $course_price - $total_paid;

    if ($amount <= $remaining_amount) {
        $sql = "INSERT INTO payments (student_course_id, amount, payment_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ids", $student_course_id, $amount, $payment_date);
        
        if ($stmt->execute()) {
            header("Location: ../../views/payments/paymentviews.php?id=" . $id . "&scid=" . $scid);
            exit();
        } else {
            echo "Error adding payment: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Payment amount exceeds the remaining balance.";
    }

    $stmt_check->close();
}

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


$sql_balance = "SELECT IFNULL(SUM(amount), 0) as total_paid FROM payments WHERE student_course_id = ?";
$stmt_balance = $conn->prepare($sql_balance);
$stmt_balance->bind_param("i", $scid);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$row_balance = $result_balance->fetch_assoc();
$total_paid = $row_balance['total_paid'];
$remaining_balance = $row['price'] - $total_paid;
?>

<!doctype html>
<html lang="en">

<head>
    <title>Add Payment</title>
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
                    <h2 class="heading-section">Add New Payment</h2>
                </div>
            </div>
            <div>
                <h2>Student: <?= $student_name ?></h2>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-wrap">
                        <form action="../../views/payments/addpayment.php?id=<?= $id ?>&scid=<?= $scid ?>" method="post">
                            <input type="hidden" name="student_course_id" value="<?= intval($row['id']) ?>">
                            <div class="form-group">
                                <label>Course name: <?= htmlspecialchars($row['title']) ?></label>
                            </div>
                            <div class="form-group">
                                <label>Duration: <?= htmlspecialchars($row['duration']) ?></label>
                            </div>
                            <div class="form-group">
                                <label>Price: <?= htmlspecialchars($row['price']) ?></label>
                            </div>
                            <div class="form-group">
                                <label>Remaining To Pay Balance: <?= number_format($remaining_balance, 2) ?></label>
                            </div>
                            <div class="form-group">
                                <label for="paid_amount">Amount:</label>
                                <input type="number" id="paid_amount" name="paid_amount" step="0.01" min="0" max="<?= $remaining_balance ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="payment_date">Payment Date:</label>
                                <input type="date" id="payment_date" name="payment_date" required class="form-control">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn-submit" <?= $remaining_balance <= 0 ? 'disabled' : '' ?>>
                                    <i class="fa fa-plus-circle"></i> Add Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>

<?php
$stmt->close();
$stmt_balance->close();
$conn->close(); 
?>
