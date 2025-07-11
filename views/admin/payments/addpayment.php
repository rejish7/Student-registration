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

$error_message = '';
$success_message = '';

if ($scid <= 0) {
    die("Invalid course ID");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_course_id = intval($_POST['student_course_id']);
    $amount = floatval($_POST['paid_amount']);
    $payment_date = $_POST['payment_date'];

    // Validate payment date
    if (empty($payment_date)) {
        $error_message = "Payment date is required";
    } else {
        $sql_check = "SELECT c.price, IFNULL(SUM(p.amount), 0) as total_paid 
                    FROM student_course sc
                    JOIN it_course c ON c.id = sc.course_id
                    LEFT JOIN payments p ON p.student_course_id = sc.id
                    WHERE sc.id = ?
                    GROUP BY sc.id";
        
        $stmt_check = $conn->prepare($sql_check);
        if (!$stmt_check) {
            $error_message = "Error preparing statement: " . $conn->error;
        } else {
            $stmt_check->bind_param("i", $student_course_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows == 0) {
                $error_message = "Course information not found";
            } else {
                $row_check = $result_check->fetch_assoc();
                $course_price = $row_check['price'];
                $total_paid = $row_check['total_paid'];
                $remaining_amount = $course_price - $total_paid;

                if ($amount <= 0) {
                    $error_message = "Payment amount must be greater than zero";
                } elseif ($amount > $remaining_amount) {
                    $error_message = "Error: Payment amount (Rs" . number_format($amount, 2) . ") exceeds the remaining balance (Rs" . number_format($remaining_amount, 2) . ")";
                } else {
                    // All validation passed, insert the payment
                    $sql = "INSERT INTO payments (student_course_id, amount, payment_date) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        $error_message = "Error preparing statement: " . $conn->error;
                    } else {
                        $stmt->bind_param("ids", $student_course_id, $amount, $payment_date);
                        
                        if ($stmt->execute()) {
                            // Redirect on success using clean URL
                            $_SESSION['success_message'] = "Payment added successfully!";
                            redirect(payment_url($id, $scid));
                            exit();
                        } else {
                            $error_message = "Error adding payment: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
            $stmt_check->close();
        }
    }
}

// Get course and student information
$sql = "SELECT sc.*, s.fullname, c.title, c.duration, c.price 
        FROM student_course AS sc 
        JOIN students AS s ON s.id = sc.student_id 
        JOIN it_course AS c ON c.id = sc.course_id 
        WHERE sc.id = ?";
        
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $scid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found");
}

$row = $result->fetch_assoc();
$student_name = htmlspecialchars($row['fullname']);
$course_title = htmlspecialchars($row['title']);
$course_duration = htmlspecialchars($row['duration']);
$course_price = $row['price'];
$student_id = $row['student_id'];

// Get payment balance information
$sql_balance = "SELECT IFNULL(SUM(amount), 0) as total_paid FROM payments WHERE student_course_id = ?";
$stmt_balance = $conn->prepare($sql_balance);
if (!$stmt_balance) {
    die("Error preparing statement: " . $conn->error);
}

$stmt_balance->bind_param("i", $scid);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$row_balance = $result_balance->fetch_assoc();
$total_paid = $row_balance['total_paid'];
$remaining_balance = $course_price - $total_paid;

// Set default payment date to today
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Add Payment</title>
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
        
        .actions button {
            background: transparent;
            border: none;
            color: #777;
            margin-left: 15px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .actions button:hover {
            color: var(--primary-color);
        }
        
        /* Dashboard Content */
        .dashboard-title {
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 30px;
            color: #333;
        }
        
        /* Payment Form Styles */
        .payment-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .student-info {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .info-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .badge-primary {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
        }
        
        .badge-info {
            background: rgba(37, 117, 252, 0.1);
            color: var(--secondary-color);
        }
        
        .badge-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .badge-danger {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
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
        
        .btn-success {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 117, 252, 0.4);
        }
        
        .btn-info {
            background: var(--info-color);
            border: none;
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-info:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(23, 162, 184, 0.4);
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
        
        @media (max-width: 768px) {
            .payment-info-row {
                flex-direction: column;
            }
            
            .payment-info-col {
                width: 100%;
                margin-bottom: 15px;
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
            <h2 class="dashboard-title"><i class="fas fa-plus-circle"></i> Add New Payment</h2>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Student Info Card -->
            <div class="student-info">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4><i class="fas fa-user"></i> Student: <?= $student_name ?></h4>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <h4><i class="fas fa-book"></i> Course: <?= $course_title ?></h4>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form Card -->
            <div class="payment-card">
                <form action="<?= payment_url($id, $scid, '') ?>" method="post">
                    <input type="hidden" name="student_course_id" value="<?= intval($row['id']) ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-book"></i> Course Name:</label>
                                <p class="text-info font-weight-bold"><?= $course_title ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Duration:</label>
                                <p class="text-info font-weight-bold"><?= $course_duration ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="info-badge badge-primary">
                                    <i class="fas fa-tag"></i> Total Price: Rs<?= number_format($course_price, 2) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="info-badge badge-success">
                                    <i class="fas fa-money-bill-wave"></i> Already Paid: Rs<?= number_format($total_paid, 2) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="info-badge badge-danger">
                                    <i class="fas fa-balance-scale"></i> Remaining: Rs<?= number_format($remaining_balance, 2) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="paid_amount"><i class="fas fa-money-bill-alt"></i> Payment Amount:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rs</span>
                                    </div>
                                    <input type="number" id="paid_amount" name="paid_amount" step="0.01" min="1" max="<?= $remaining_balance ?>" required class="form-control" placeholder="Enter amount">
                                </div>
                                <small class="form-text text-muted">Maximum amount: Rs<?= number_format($remaining_balance, 2) ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date"><i class="fas fa-calendar-alt"></i> Payment Date:</label>
                                <input type="date" id="payment_date" name="payment_date" required class="form-control" value="<?= $today ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success" <?= $remaining_balance <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-plus-circle"></i> Add Payment
                        </button>
                        <a href="<?= payment_url($student_id, $scid) ?>" class="btn btn-secondary ml-2">
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
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$stmt_balance->close();
$conn->close(); 
?>
