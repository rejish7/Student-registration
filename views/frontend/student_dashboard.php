<?php
session_start();
include '../../config/config.php';
include '../../config/url_helpers.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    redirect('login');
}

$stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
if (!$stmt) {
    die("Database query failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

$student_id = $student['id'];

$stmt = $conn->prepare("SELECT c.*, COALESCE(SUM(p.amount), 0) as total_paid, c.price as course_price 
        FROM it_course c 
        JOIN student_course sc ON c.id = sc.course_id 
        LEFT JOIN payments p ON p.student_course_id = sc.id 
        WHERE sc.student_id = ? 
        GROUP BY c.id, sc.id");
if (!$stmt) {
    die("Database query failed: " . $conn->error);
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

$sql1 = "SELECT * FROM images WHERE student_id = ?";
$stmt1 = $conn->prepare($sql1);
if (!$stmt1) {
    die("Database query failed: " . $conn->error);
}
$stmt1->bind_param("i", $student_id);
$stmt1->execute();
$result1 = $stmt1->get_result();
$row1 = $result1->fetch_assoc();

$total_course_price = 0;
$total_amount_paid = 0;
$courses_data = [];
while ($course = $enrolled_courses->fetch_assoc()) {
    $total_course_price += $course['course_price'];
    $total_amount_paid += $course['total_paid'];
    $courses_data[] = $course;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset_url('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-bg: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .container {
            max-width: 1200px;
        }

        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow-lg);
            overflow: hidden;
            position: relative;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        .profile-img-container {
            position: relative;
            padding: 2rem;
            text-align: center;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1rem 0 0.5rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .modern-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-header-modern {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            border: none;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .card-body-modern {
            padding: 2rem;
        }

        .btn-modern {
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn:hover{
            color:white;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-success-modern {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .btn-info-modern {
            background: linear-gradient(135deg, var(--info-color), #2563eb);
            color: white;
        }

        .btn-warning-modern {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .course-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .course-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .course-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .payment-summary {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .payment-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .profile-img {
                width: 120px;
                height: 120px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-link {
            font-weight: 500;
            color: var(--primary-color) !important;
            transition: color 0.3s ease;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light ">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Student Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-content-end">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo auth_url('logout'); ?>">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 fade-in">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="profile-card">
                    <div class="profile-img-container">
                        <?php if ($row1 && !empty($row1['images'])): ?>
                            <img src="<?php echo asset_url('picture/' . htmlspecialchars($row1['images'])); ?>" 
                                 alt="Profile Picture" class="profile-img">
                        <?php else: ?>
                            <img src="<?php echo asset_url('picture/default.jpg'); ?>" 
                                 alt="Default Profile Picture" class="profile-img">
                        <?php endif; ?>
                        <div class="profile-name"><?= htmlspecialchars($student['fullname']); ?></div>
                        <div class="mb-3">
                            <small class="text-white-50">
                                <i class="fas fa-user-graduate me-1"></i>
                                Student ID: <?= htmlspecialchars($student['id']); ?>
                            </small>
                        </div>
                        <a href="<?php echo frontend_student_url('profile', $student_id); ?>" 
                           class="btn btn-light btn-modern">
                            <i class="fas fa-eye me-2"></i>
                            View Full Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8 col-md-7">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-value text-primary"><?= count($courses_data); ?></div>
                        <div class="stat-label">Enrolled Courses</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon text-success">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-value text-success"><?= number_format($total_amount_paid, 0); ?></div>
                        <div class="stat-label">Amount Paid</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon text-warning">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-value text-warning"><?= number_format($total_course_price - $total_amount_paid, 0); ?></div>
                        <div class="stat-label">Remaining</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="modern-card mb-4">
                    <div class="card-header-modern">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </div>
                    <div class="card-body-modern">
                        <div class="action-buttons">
                            <a href="<?php echo frontend_request_url($student_id, 'add'); ?>" 
                               class="btn btn-info-modern btn-modern">
                                <i class="fas fa-paper-plane me-2"></i>
                                Make a Request
                            </a>
                            <a href="<?php echo frontend_request_url($student_id, 'view'); ?>" 
                               class="btn btn-success-modern btn-modern">
                                <i class="fas fa-eye me-2"></i>
                                View Requests
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Enrolled Courses -->
                <div class="modern-card">
                    <div class="card-header-modern">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Your Enrolled Courses
                    </div>
                    <div class="card-body-modern">
                        <?php if (count($courses_data) > 0): ?>
                            <?php foreach ($courses_data as $course): ?>
                                <div class="course-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="course-title">
                                                <i class="fas fa-book me-2"></i>
                                                <?= htmlspecialchars($course['title']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?= htmlspecialchars($course['description'] ?? 'No description available'); ?>
                                            </small>
                                        </div>
                                        <div class="course-price">
                                            <i class="fas fa-rupee-sign"></i><?= number_format($course['course_price'], 0); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-graduation-cap text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No courses enrolled yet</p>
                                <a href="<?php echo student_course_url($student_id, 'add'); ?>" 
                                   class="btn btn-primary-modern btn-modern">
                                    <i class="fas fa-plus me-2"></i>
                                    Enroll in a Course
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Payment Summary -->
                        <?php if (count($courses_data) > 0): ?>
                            <div class="payment-summary">
                                <h6 class="mb-3">
                                    <i class="fas fa-calculator me-2"></i>
                                    Payment Summary
                                </h6>
                                <div class="payment-item">
                                    <span>Total Course Fees:</span>
                                    <span class="fw-bold">
                                        <i class="fas fa-rupee-sign"></i><?= number_format($total_course_price, 0); ?>
                                    </span>
                                </div>
                                <div class="payment-item">
                                    <span class="text-success">Amount Paid:</span>
                                    <span class="fw-bold text-success">
                                        <i class="fas fa-rupee-sign"></i><?= number_format($total_amount_paid, 0); ?>
                                    </span>
                                </div>
                                <div class="payment-item">
                                    <span class="text-danger">Remaining Balance:</span>
                                    <span class="fw-bold text-danger">
                                        <i class="fas fa-rupee-sign"></i><?= number_format($total_course_price - $total_amount_paid, 0); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo asset_url('js/jquery-3.3.1.min.js'); ?>"></script>
    <script src="<?php echo asset_url('js/popper.min.js'); ?>"></script>
    <script src="<?php echo asset_url('js/bootstrap.min.js'); ?>"></script>
    
    <script>
        // Add smooth scrolling and animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const cards = document.querySelectorAll('.modern-card, .stat-card, .course-item');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeIn 0.6s ease-in';
                    }
                });
            });
            
            cards.forEach(card => {
                observer.observe(card);
            });
        
        });
    </script>
</body>

</html>
