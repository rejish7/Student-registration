
<?php
include '../../config/url_helpers.php';
session_start();
session_destroy();
redirect('admin/login');
?>
