<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h1 class="text-center mb-5 display-4 text-primary">Welcome to Student Portal</h1>
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-5">
                        <h5 class="card-title text-center mb-4 text-secondary">Student Actions</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item rounded-3 mb-3 bg-light">
                                <a href="views/students/registration.php" class="d-flex justify-content-between align-items-center text-decoration-none text-dark">
                                    <span class="fw-bold">Register New Student</span>
                                    <span class="badge bg-primary rounded-pill px-3 py-2">→</span>
                                </a>
                            </li>
                            <li class="list-group-item rounded-3 bg-light">
                                <a href="views/auth/index.php" class="d-flex justify-content-between align-items-center text-decoration-none text-dark">
                                    <span class="fw-bold">Students Login</span>
                                    <span class="badge bg-primary rounded-pill px-3 py-2">→</span>
                                </a>
                            </li>
                        </ul>   
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="/public/js/popper.min.js"></script>
    <script src="/public/js/bootstrap.min.js"></script>
</body>

</html>