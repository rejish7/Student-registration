<?php
/**
 * URL Helper Functions for Clean URLs
 * Provides consistent URL generation throughout the application
 */

function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $project_folder = '/Student-registration';
    $baseUrl = $protocol . '://' . $host . $project_folder;
    
    return $baseUrl . '/' . ltrim($path, '/');
}

function admin_url($path = '') {
    return base_url("admin/{$path}");
}
function user_url($id = '', $action = '') {
    if ($action && $id) {
        return base_url("admin/users/{$action}/{$id}");
    } elseif ($id && !$action) {
        return base_url("admin/users/view/{$id}");
    } elseif ($action) {
        return base_url("admin/users/{$action}");
    }
    return base_url("admin/users");
}


function student_url($id = '', $action = '') {
    if ($action && $id) {
        return base_url("admin/students/{$action}/{$id}");
    } elseif ($id && !$action) {
        return base_url("admin/students/view/{$id}");
    } elseif ($action) {
        return base_url("admin/students/{$action}");
    }
    return base_url("admin/students");
}

function frontend_student_url($action = '', $id = '') {
    if ($action === 'login') {
        return base_url("students/login");
    }elseif($action === 'register') {
        return base_url("students/register");
    } elseif ($action === 'profile' && $id) {
        return base_url("students/profile/{$id}");
    }
    return base_url("students");
}

function course_url($id = '', $action = '') {
    if ($action && $id) {
        return base_url("admin/courses/{$action}/{$id}");
    } elseif ($id) {
        return base_url("admin/courses/{$id}");
    } elseif ($action) {
        return base_url("admin/courses/{$action}");
    }
    return base_url("admin/courses");
}

function student_course_url($student_id, $action = '', $course_id = '') {
    $url = "admin/students/{$student_id}/courses";
    if ($action) {
        $url .= "/{$action}";
        if ($course_id) {
            $url .= "/{$course_id}";
        }
    }
    return base_url($url);
}

function student_course_enrollment_url($student_id = '', $course_id = '', $action = '') {
    $url = "admin/student-courses";
    if ($action) {
        $url .= "/{$action}";
    }
    if ($student_id && $course_id) {
        $url .= "/{$student_id}/course/{$course_id}";
    } elseif ($student_id) {
        $url .= "/student/{$student_id}";
    } elseif ($course_id) {
        $url .= "/course/{$course_id}";
    }
    return base_url($url);
}

function request_url($student_id, $action = '', $request_id = '') {
    $url = "admin/students/{$student_id}/requests";
    if ($action) {
        $url .= "/{$action}";
        if ($request_id) {
            $url .= "/{$request_id}";
        }
    }
    return base_url($url);
}

function payment_url($student_id, $course_id, $action = '') {
    if ($action) {
        return base_url("admin/payments/{$action}/{$student_id}/course/{$course_id}");
    }
    return base_url("admin/payments/{$student_id}/course/{$course_id}");
}

function auth_url($action = '') {
    if ($action) {
        return base_url($action);
    }
    return base_url("admin/login");
}

function profile_url($id = '') {
    if ($id) {
        return base_url("profile/{$id}");
    }
    return base_url("profile");
}

function dashboard_url() {
    return base_url("dashboard");
}

function home_url() {
    return base_url();
}

function redirect($path) {
    header("Location: " . base_url($path));
    exit();
}

function redirect_admin($path = '') {
    header("Location: " . admin_url($path));
    exit();
}

function is_current_page($path) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = parse_url(base_url(), PHP_URL_PATH);
    $relativePath = str_replace($basePath, '', $currentPath);
    return $relativePath === '/' . ltrim($path, '/');
}

function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function url_slug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

function asset_url($path) {
    return base_url("public/{$path}");
}

function admin_login_redirect() {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        redirect_admin();
    } else {
        redirect('dashboard');
    }
}

function breadcrumb_url($section, $action = '', $id = '') {
    switch ($section) {
        case 'admin':
            return admin_url($action);
        case 'students':
            return student_url($id, $action);
        case 'courses':
            return course_url($id, $action);
        default:
            return base_url($section);
    }
}
?>
