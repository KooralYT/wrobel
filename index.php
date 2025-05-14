<?php
require_once 'config.php';
require_once 'functions.php';

// Obsługa wylogowania
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// Router - obsługa różnych stron
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    
    switch ($page) {
        // Strony autoryzacji
        case 'login':
            require_once 'auth.php';
            render_login_page();
            break;
            
        case 'register':
            require_once 'auth.php';
            render_register_page();
            break;
        
        // Strony ucznia
        case 'dashboard':
            require_once 'student.php';
            check_login();
            render_dashboard();
            break;
            
        case 'lesson':
            require_once 'student.php';
            check_login();
            if (isset($_GET['id'])) {
                render_lesson($_GET['id']);
            } else {
                redirect('dashboard');
            }
            break;
            
        case 'test':
            require_once 'student.php';
            check_login();
            if (isset($_GET['id'])) {
                render_test($_GET['id']);
            } else {
                redirect('dashboard');
            }
            break;
            
        case 'test_result':
            require_once 'student.php';
            check_login();
            render_test_result();
            break;
            
        // Strony administratora
        case 'admin':
            require_once 'admin.php';
            check_admin();
            render_admin_panel();
            break;
            
        case 'admin_user_details':
            require_once 'admin.php';
            check_admin();
            if (isset($_GET['id'])) {
                render_user_details($_GET['id']);
            } else {
                redirect('admin');
            }
            break;
            
        case 'admin_test_details':
            require_once 'admin.php';
            check_admin();
            if (isset($_GET['id'])) {
                render_test_details($_GET['id']);
            } else {
                redirect('admin');
            }
            break;
            
        case 'admin_edit_lesson':
            require_once 'admin.php';
            check_admin();
            if (isset($_GET['id'])) {
                render_edit_lesson($_GET['id']);
            } else {
                redirect('admin');
            }
            break;
            
        case 'admin_add_lesson':
            require_once 'admin.php';
            check_admin();
            render_add_lesson();
            break;
            
        case 'admin_manage_questions':
            require_once 'admin.php';
            check_admin();
            if (isset($_GET['lesson_id'])) {
                render_manage_questions($_GET['lesson_id']);
            } else {
                redirect('admin');
            }
            break;
            
        case 'admin_get_question':
            require_once 'admin.php';
            check_admin();
            if (isset($_GET['id'])) {
                get_question_json($_GET['id']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Brak ID pytania']);
                exit;
            }
            break;
            
        default:
            // Domyślnie przekieruj na dashboard (dla zalogowanych) lub login
            if (isset($_SESSION['user_id'])) {
                redirect('dashboard');
            } else {
                redirect('login');
            }
            break;
    }
} else {
    // Domyślnie przekieruj na dashboard (dla zalogowanych) lub login
    if (isset($_SESSION['user_id'])) {
        redirect('dashboard');
    } else {
        redirect('login');
    }
}