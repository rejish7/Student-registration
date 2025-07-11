
<?php
include '../../config/config.php';
include '../../config/url_helpers.php';

if (!isset($_GET['request_id']) || !isset($_GET['student_id'])) {
    die("Required parameters missing");
}

$request_id = $_GET['request_id'];
$student_id = $_GET['student_id'];

$sql = "SELECT file FROM requests WHERE id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $request = $result->fetch_assoc();
    if ($request['file'] && file_exists($request['file'])) {
        unlink($request['file']);
    }
}

$sql = "DELETE FROM requests WHERE id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $student_id);

if ($stmt->execute()) {
    redirect('admin/students/' . $student_id . '/requests');
} else {
    die("Error deleting request: " . $conn->error);
}
