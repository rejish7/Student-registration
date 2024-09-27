
<?php
    include '../../config/config.php';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    $sql = "UPDATE payments SET amount = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $id);
    
    if ($stmt->execute()) {
        header("Location: ../../views/payments/paymentviews.php?id=$scid&scid=$scid");
        exit();
    } else {
        $error = "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

$sql = "SELECT p.amount, c.title, c.price 
        FROM payments p
        JOIN student_course sc ON p.student_course_id = sc.id
        JOIN it_course c ON sc.course_id = c.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Payment not found");
}

$row = $result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
    <title>Edit Payment</title>
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
                    <h2 class="heading-section">Edit Payment</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="course">Course:</label>
                            <input type="text" class="form-control" id="course" value="<?= htmlspecialchars($row['title']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="price">Course Price:</label>
                            <input type="text" class="form-control" id="price" value="<?= htmlspecialchars($row['price']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="amount">Payment Amount:</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?= htmlspecialchars($row['amount']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Payment</button>
                        <a href="../../views/payments/paymentviews.php?id=<?= urlencode($scid) ?>&scid=<?= urlencode($scid) ?>" class="btn btn-secondary">Cancel</a>
                    </form>
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
