<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';

$id = intval($_GET['id']);
echo $id;
$scid = intval($_GET['scid']);

$sql = "DELETE FROM student_course WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $scid);
$stmt->execute();
$stmt->close();

redirect(student_course_url($id));
?>