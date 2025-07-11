<?php
include '../../../config/config.php';
include '../../../config/url_helpers.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$scid = isset($_GET['scid']) ? intval($_GET['scid']) : 0;

if ($scid <= 0) {
    die("Invalid course ID");
}

$sql = "SELECT sc.*, s.fullname, c.title, c.duration, c.price 
        FROM student_course AS sc 
        JOIN students AS s ON s.id = sc.student_id 
        JOIN it_course AS c ON c.id = sc.course_id 
        WHERE sc.id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error in prepare statement: " . $conn->error);
}
$stmt->bind_param("i", $scid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Course not found");
}

$row = $result->fetch_assoc();
$student_name = htmlspecialchars($row['fullname']);
$course_price = $row['price'];
$course_title = $row['title'];
$student_id = $row['student_id'];
?>

    <!DOCTYPE html>
<html lang="en">

<head>
    <title>Payment Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
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
        
        /* Payment Table Styles */
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
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-top: 0;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            padding: 15px;
            color: var(--dark-color);
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 15px;
            border-color: #e9ecef;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            margin: 0 2px;
        }
        
        .table .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            box-shadow: 0 2px 6px rgba(37, 117, 252, 0.3);
        }
        
        .table .btn-danger {
            background: var(--danger-color);
            border: none;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
        }
        
        .table-info {
            background-color: rgba(23, 162, 184, 0.05) !important;
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
            .table-responsive {
                border: 0;
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
                    <img src="../../public/picture/profile.jpg" alt="Admin">
                    <span>Administrator</span>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <h2 class="dashboard-title"><i class="fas fa-file-invoice-dollar"></i> Payment Details</h2>
            
            <!-- Student Info Card -->
            <div class="student-info">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4><i class="fas fa-user"></i> Student: <?= $student_name ?></h4>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <h4><i class="fas fa-book"></i> Course: <?= htmlspecialchars($course_title) ?></h4>
                    </div>
                </div>
            </div>
            
            <!-- Payment Table Card -->
            <div class="payment-card">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col"><i class="fas fa-book"></i> COURSE NAME</th>
                                <th scope="col"><i class="fas fa-clock"></i> DURATION</th>
                                <th scope="col"><i class="fas fa-tag"></i> PRICE</th>
                                <th scope="col"><i class="fas fa-money-bill-alt"></i> PAID AMOUNT</th>
                                <th scope="col"><i class="fas fa-balance-scale"></i> REMAINING</th>
                                <th scope="col"><i class="fas fa-calendar-alt"></i> PAYMENT DATE</th>
                                <th scope="col"><i class="fas fa-cogs"></i> ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.id, p.amount, p.payment_date, c.title, c.duration 
                                    FROM payments p
                                    JOIN student_course sc ON p.student_course_id = sc.id
                                    JOIN it_course c ON sc.course_id = c.id
                                    WHERE sc.id = ?
                                    ORDER BY p.payment_date ASC";
                            $stmt = $conn->prepare($sql);
                            if (!$stmt) {
                                echo "<tr><td colspan='7' class='text-center text-danger'>Error preparing statement: " . $conn->error . "</td></tr>";
                            } else {
                                $stmt->bind_param("i", $scid);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                $total_paid = 0;
                                
                                if ($result->num_rows > 0) {
                                    while ($payment = $result->fetch_assoc()) {
                                        $total_paid += $payment['amount'];
                                        $remaining_amount = $course_price - $total_paid;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($payment['title']) ?></td>
                                            <td><?= htmlspecialchars($payment['duration']) ?></td>
                                            <td>Rs<?= htmlspecialchars($course_price) ?></td>
                                            <td>Rs<?= htmlspecialchars($payment['amount']) ?></td>
                                            <td>Rs<?= htmlspecialchars($remaining_amount) ?></td>
                                            <td><?= htmlspecialchars(date('F j, Y', strtotime($payment['payment_date']))) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo payment_url
                                                    ($payment['id'],$scid,'edit') ?>" class="btn btn-primary btn-sm btn-action">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href=" <?php echo payment_url
                                                    ($payment['id'],$scid,'delete') ?>" onclick="return confirm('Are you sure you want to delete this payment record?')" class="btn btn-danger btn-sm btn-action">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    // Show total row
                                    echo "<tr class='table-info'>";
                                    echo "<td colspan='3' class='text-right'><strong>Totals:</strong></td>";
                                    echo "<td><strong>Rs" . htmlspecialchars($total_paid) . "</strong></td>";
                                    echo "<td><strong>Rs" . htmlspecialchars($course_price - $total_paid) . "</strong></td>";
                                    echo "<td colspan='2'></td>";
                                    echo "</tr>";
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No payment records found</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../../views/payments/addpayment.php?id=<?= urlencode($id) ?>&scid=<?= urlencode($scid) ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Add New Payment
                    </a>
                    <a href="../../views/courses/views.php?id=<?= urlencode($student_id) ?>" class="btn btn-info">
                        <i class="fas fa-arrow-left"></i> Back to Student Details
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Student Registration Management System</p>
    </footer>

    <script src="../../public/js/jquery-3.3.1.min.js"></script>
    <script src="../../public/js/popper.min.js"></script>
    <script src="../../public/js/bootstrap.min.js"></script>
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
$conn->close();
?>
