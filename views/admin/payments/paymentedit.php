<?php
session_start();
include '../../../config/config.php';
include '../../../config/url_helpers.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('admin/login');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    
    // Get payment and course info for validation
    $sql_check = "SELECT p.student_course_id, c.price, c.id as course_id, sc.student_id
                  FROM payments p
                  JOIN student_course sc ON p.student_course_id = sc.id
                  JOIN it_course c ON sc.course_id = c.id
                  WHERE p.id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $check_row = $result_check->fetch_assoc();
    
    // Calculate total paid for this course (excluding current payment)
    $sql_total_check = "SELECT COALESCE(SUM(amount), 0) as total_paid 
                        FROM payments 
                        WHERE student_course_id = ? AND id != ?";
    $stmt_total_check = $conn->prepare($sql_total_check);
    $stmt_total_check->bind_param("ii", $check_row['student_course_id'], $id);
    $stmt_total_check->execute();
    $result_total_check = $stmt_total_check->get_result();
    $total_paid_excluding_current_check = $result_total_check->fetch_assoc()['total_paid'];
    
    // Calculate remaining balance
    $course_price_check = $check_row['price'];
    $remaining_balance_check = $course_price_check - $total_paid_excluding_current_check;
    $max_allowed_payment_check = min($remaining_balance_check, $course_price_check);
    
    // Validation
    if ($amount <= 0) {
        $error = "Payment amount must be greater than 0.";
    } elseif ($amount > $max_allowed_payment_check) {
        $error = "Payment amount cannot exceed Rs " . number_format($max_allowed_payment_check, 2) . " (remaining balance).";
    } else {
        $sql = "UPDATE payments SET amount = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Payment updated successfully!";
            redirect(payment_url($check_row['student_id'], $check_row['course_id'], ''));
        } else {
            $error = "Error updating record: " . $conn->error;
        }
        $stmt->close();
    }
    
    $stmt_check->close();
    $stmt_total_check->close();
}

$sql = "SELECT p.amount, p.student_course_id, c.title, c.price, c.id as course_id, s.fullname, sc.student_id
        FROM payments p
        JOIN student_course sc ON p.student_course_id = sc.id
        JOIN it_course c ON sc.course_id = c.id
        JOIN students s ON sc.student_id = s.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Payment not found");
}

$row = $result->fetch_assoc();
$student_id = $row['student_id'];
$course_id = $row['course_id'];

// Calculate total paid for this course (excluding current payment)
$sql_total = "SELECT COALESCE(SUM(amount), 0) as total_paid 
              FROM payments 
              WHERE student_course_id = ? AND id != ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("ii", $row['student_course_id'], $id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_paid_excluding_current = $result_total->fetch_assoc()['total_paid'];

// Calculate remaining balance
$course_price = $row['price'];
$current_payment = $row['amount'];
$remaining_balance = $course_price - $total_paid_excluding_current;
$max_allowed_payment = min($remaining_balance, $course_price);

$stmt_total->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Payment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/bootstrap.min.css') ?>">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f8fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-container {
            display: flex;
            flex: 1;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            transition: all 0.3s ease;
            padding: 20px;
        }
        
        .topbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .menu-toggle {
            display: none;
            background: transparent;
            border: none;
            color: #333;
            font-size: 1.25rem;
            cursor: pointer;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .user-info span {
            font-weight: 500;
        }
        
        /* Dashboard Content */
        .dashboard-title {
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 30px;
            color: #333;
        }
        
        /* Form Card Styles */
        .edit-payment-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .edit-payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
        }
        
        .btn {
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
        }
        
        .btn-secondary {
            background: var(--dark-color);
            border: none;
            box-shadow: 0 5px 15px rgba(52, 58, 64, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 58, 64, 0.4);
        }
        
        /* Footer */
        footer {
            background: white;
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .menu-toggle {
                display: block;
            }
            
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 99;
                display: none;
            }
            
            .overlay.active {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
       <?php include '../sidebar.php'?>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <div class="overlay" id="overlay"></div>
            
            <!-- Top Bar -->
            <div class="topbar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4>Student Registration System</h4>
                <div class="user-info">
                    <img src="<?= asset_url('picture/profile.jpg') ?>" alt="Admin">
                    <span>Administrator</span>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <h2 class="dashboard-title"><i class="fas fa-edit"></i> Edit Payment</h2>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Edit Payment Form Card -->
            <div class="edit-payment-card">
                <!-- Payment Information -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Payment Information</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Student:</strong><br>
                                    <?= htmlspecialchars($row['fullname']) ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Course Price:</strong><br>
                                    Rs <?= number_format($course_price, 2) ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Already Paid:</strong><br>
                                    Rs <?= number_format($total_paid_excluding_current, 2) ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Max Allowed:</strong><br>
                                    <span class="text-success">Rs <?= number_format($max_allowed_payment, 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form action="<?= payment_url($student_id, $course_id, 'edit') . '?id=' . $id ?>" method="post" id="paymentForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="course"><i class="fas fa-book"></i> Course:</label>
                                <input type="text" class="form-control" id="course" value="<?= htmlspecialchars($row['title']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price"><i class="fas fa-tag"></i> Course Price:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rs</span>
                                    </div>
                                    <input type="text" class="form-control" id="price" value="<?= htmlspecialchars($row['price']) ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount"><i class="fas fa-money-bill-alt"></i> Payment Amount:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rs</span>
                            </div>
                            <input type="number" 
                                   step="0.01" 
                                   class="form-control" 
                                   id="amount" 
                                   name="amount" 
                                   value="<?= htmlspecialchars($row['amount']) ?>" 
                                   min="0.01"
                                   max="<?= $max_allowed_payment ?>"
                                   required>
                        </div>
                        <small class="form-text text-muted">
                            Maximum allowed: Rs <?= number_format($max_allowed_payment, 2) ?>
                        </small>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Payment
                        </button>
                        <a href="<?= payment_url($row['student_id'], $scid, '') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System</p>
    </footer>

    <script src="<?= asset_url('js/jquery-3.3.1.min.js') ?>"></script>
    <script src="<?= asset_url('js/popper.min.js') ?>"></script>
    <script src="<?= asset_url('js/bootstrap.min.js') ?>"></script>
    <script>
        $(document).ready(function() {
            const maxAllowed = <?= $max_allowed_payment ?>;
            const coursePrice = <?= $course_price ?>;
            
            // Toggle sidebar on mobile
            $('#menu-toggle').click(function() {
                $('#sidebar').toggleClass('active');
                $('#overlay').toggleClass('active');
            });
            
            // Close sidebar when clicking overlay
            $('#overlay').click(function() {
                $('#sidebar').removeClass('active');
                $('#overlay').removeClass('active');
            });
            
            // Handle window resize
            $(window).resize(function() {
                if ($(window).width() > 992) {
                    $('#sidebar').removeClass('active');
                    $('#overlay').removeClass('active');
                }
            });
            
            // Payment amount validation
            $('#amount').on('input', function() {
                const amount = parseFloat($(this).val());
                const $input = $(this);
                
                // Remove any existing validation classes
                $input.removeClass('is-valid is-invalid');
                
                if (amount > maxAllowed) {
                    $input.addClass('is-invalid');
                    if (!$input.next('.invalid-feedback').length) {
                        $input.after('<div class="invalid-feedback">Amount cannot exceed Rs ' + maxAllowed.toFixed(2) + '</div>');
                    }
                } else if (amount > 0) {
                    $input.addClass('is-valid');
                    $input.next('.invalid-feedback').remove();
                } else {
                    $input.next('.invalid-feedback').remove();
                }
            });
            
            // Form submission validation
            $('#paymentForm').on('submit', function(e) {
                const amount = parseFloat($('#amount').val());
                
                if (amount <= 0) {
                    e.preventDefault();
                    alert('Payment amount must be greater than 0.');
                    return false;
                }
                
                if (amount > maxAllowed) {
                    e.preventDefault();
                    alert('Payment amount cannot exceed Rs ' + maxAllowed.toFixed(2) + ' (remaining balance).');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
