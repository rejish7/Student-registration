<!DOCTYPE html>
<html lang="en">
<head>
    <title>Request Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<?php
include '../../config/config.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if (!$student_id) {
    die("Student ID not provided");
}


$sql = "SELECT r.*, s.fullname as student_name 
        FROM requests r 
        JOIN students s ON r.student_id = s.id 
        WHERE r.student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
   echo '<div class="container mt-5">
           <div class="row justify-content-center">
               <div class="col-md-12 text-center">
                   <div class="alert alert-warning" style="font-size: 4em;">
                       <i class="fa fa-exclamation-triangle"></i>
                       No requests found for this student
                   </div>
               </div>
           </div>
       </div>';
   
     exit();
}
$first_request = $result->fetch_assoc();
$student_name = $first_request['student_name'];
?>


<body class="d-flex flex-column min-vh-100">
    <section class="ftco-section flex-grow-1">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-4">
                    <h2 class="heading-section">Requests for <?= htmlspecialchars($student_name) ?></h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $result->data_seek(0);
                    while ($request = $result->fetch_assoc()) {
                    ?>
                    <div class="table-wrap mb-4">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th width="200"><i class="fa fa-file-text"></i> Request Type</th>
                                    <td><?= htmlspecialchars(ucfirst($request['request_type'])) ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa fa-header"></i> Subject</th>
                                    <td><?= htmlspecialchars($request['subject']) ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa fa-align-left"></i> Description</th>
                                    <td><?= htmlspecialchars($request['description']) ?></td>
                                </tr>
                                <?php if ($request['request_type'] === 'payment'): ?>
                                <tr>
                                    <th><i class="fa fa-money"></i> Amount</th>
                                    <td>Rs. <?= htmlspecialchars($request['amount']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($request['file']): ?>
                                <tr>
                                    <th><i class="fa fa-paperclip"></i> Attachment</th>
                                    <td>
                                        <a href="<?= htmlspecialchars($request['file']) ?>" class="btn btn-sm btn-info" download>
                                            <i class="fa fa-download"></i> Download Attachment
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th><i class="fa fa-calendar"></i> Submission Date</th>
                                    <td><?= date('F j, Y H:i:s', strtotime($request['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa fa-info-circle"></i> Status</th>
                                    <td>
                                        <span class="badge bg-<?= $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'approved' ? 'success' : 'danger') ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-end mt-2">
                            <a href="requestedit.php?request_id=<?= $request['id'] ?>&student_id=<?= $request['student_id'] ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                            <a href="requestdelete.php?request_id=<?= $request['id'] ?>&student_id=<?= $request['student_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this request?')">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                    <div class="text-center mt-4">
                        <a href="../students/crud_display.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</body>
</html>