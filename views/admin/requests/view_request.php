<!DOCTYPE html>
<html lang="en">
<head>
    <title>Request Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>

<?php
include '../../config/config.php';
include '../../config/url_helpers.php';

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

$sql = "SELECT r.*, s.fullname as student_name 
        FROM requests r 
        JOIN students s ON r.student_id = s.id 
        WHERE r.student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo'<div class="container mt-5">
           <div class="row justify-content-center">
               <div class="col-md-12 text-center">
                   <div class="alert alert-warning" style="font-size: 4em;">
                       <i class="fa fa-exclamation-triangle"></i>
                       You Have Not Requests Anything Yet!
                   </div>
               </div>
           </div>
       </div>';
       exit();
}
?>


<body class="d-flex flex-column min-vh-100">
    <section class="ftco-section flex-grow-1">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-4">
                    <h2 class="heading-section"> Your Request Details</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><i class="fa fa-file-text"></i> Request Type</th>
                                    <th><i class="fa fa-header"></i> Subject</th>
                                    <th><i class="fa fa-align-left"></i> Description</th>
                                    <th><i class="fa fa-money"></i> Amount</th>
                                    <th><i class="fa fa-check-circle"></i> Status</th>
                                    <th><i class="fa fa-calendar"></i> Submission Date</th>
                                    <th><i class="fa fa-paperclip"></i> Attachment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($request = $result->fetch_assoc()): ?>
                                <tr>
                                    <td> <?= htmlspecialchars(ucfirst($request['request_type'])) ?></td>
                                    <td> <?= htmlspecialchars($request['subject']) ?></td>
                                    <td><?= htmlspecialchars($request['description']) ?></td>
                                    <td>
                                        <?php if ($request['request_type'] === 'payment'): ?>
                                             Rs. <?= htmlspecialchars($request['amount']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'approved' ? 'success' : 'danger') ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('F j, Y H:i:s', strtotime($request['created_at'])) ?></td>
                                    <td>
                                        <?php if ($request['file']): ?>
                                            <a href="<?= htmlspecialchars($request['file']) ?>" class="btn btn-sm btn-info" download>
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-4">
                        <a href="<?php echo student_url($student_id); ?>" class="btn btn-primary">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>