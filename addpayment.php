<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_course_id = intval($_POST['student_course_id']);
    $amount = floatval($_POST['paid_amount']);
    $payment_date = $_POST['payment_date'];

    $sql = "INSERT INTO payments (student_course_id, amount, payment_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $student_course_id, $amount, $payment_date);
    
    if ($stmt->execute()) {
        header("Location: paymentviews.php?id=" . $id . "&scid=" . $scid);
        exit();
    } else {
        echo "Error adding payment: " . $stmt->error;
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
    <title>Add Payment</title>
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
                    <h2 class="heading-section">Add New Payment</h2>
                </div>
            </div>
            <div>
                <h2>Student: <?= $student_name ?></h2>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-wrap">
                        <form action="addpayment.php?id=<?= $id ?>&scid=<?= $scid ?>" method="post">
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
                                <label for="paid_amount">Amount:</label>
                                <input type="number" id="paid_amount" name="paid_amount" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="payment_date">Payment Date:</label>
                                <input type="date" id="payment_date" name="payment_date" required>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Add Payment" class="btn btn-success">
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
$conn->close(); 
?>
