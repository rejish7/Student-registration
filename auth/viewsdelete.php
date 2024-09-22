<?php
 include '../auth/config.php'; 
 $id=$_GET['id'];
echo $id;
$scid=$_GET['scid'];

$sql="Delete from student_course where id=$scid ";
$conn->query($sql);
header("location:views.php?id=$id");
?>