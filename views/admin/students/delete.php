<?php
session_start();
include '../../../config/config.php';
include '../../../config/url_helpers.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First, delete related records to maintain referential integrity
    $conn->begin_transaction();
    
    try {
        // Delete student images
        $sql_images = "DELETE FROM images WHERE student_id = ?";
        $stmt_images = $conn->prepare($sql_images);
        $stmt_images->bind_param("i", $id);
        $stmt_images->execute();
        
        // Delete student courses
        $sql_courses = "DELETE FROM student_courses WHERE student_id = ?";
        $stmt_courses = $conn->prepare($sql_courses);
        $stmt_courses->bind_param("i", $id);
        $stmt_courses->execute();
        
        // Delete student payments (if exists)
        $sql_payments = "DELETE FROM payments WHERE student_id = ?";
        $stmt_payments = $conn->prepare($sql_payments);
        $stmt_payments->bind_param("i", $id);
        $stmt_payments->execute();
        
        // Delete student requests (if exists)
        $sql_requests = "DELETE FROM requests WHERE student_id = ?";
        $stmt_requests = $conn->prepare($sql_requests);
        $stmt_requests->bind_param("i", $id);
        $stmt_requests->execute();
        
        // Finally, delete the student record
        $sql = "DELETE FROM students WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        
        if ($result) {
            $conn->commit();
            $_SESSION['success_message'] = "Student record deleted successfully!";
        } else {
            throw new Exception("Error deleting student record: " . $stmt->error);
        }
        
        $stmt->close();
        $stmt_images->close();
        $stmt_courses->close();
        $stmt_payments->close();
        $stmt_requests->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    $conn->close();
    redirect_admin('students');
    exit();
} else {
    $_SESSION['error_message'] = "No student ID provided for deletion.";
    redirect_admin('students');
    exit();
}
