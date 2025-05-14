<?php
require_once 'config.php';
require_once 'functions.php';

// Funkcja logowania
function login($db, $login, $password) {
    // Sprawdź czy użytkownik próbuje zalogować się przez nickname czy email
    $stmt = $db->prepare("SELECT id, nickname, email, pass_hash, role FROM users WHERE nickname = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['pass_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nickname'] = $user['nickname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    return false;
}

// Funkcja rejestracji
function register($db, $nickname, $email, $password) {
    // Sprawdzamy czy nickname już istnieje
    $stmt = $db->prepare("SELECT id FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Ten nickname jest już zajęty'];
    }
    
    // Sprawdzamy czy email już istnieje
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Ten email jest już zarejestrowany'];
    }
    
    // Hashujemy hasło
    $pass_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Dodajemy użytkownika
    $stmt = $db->prepare("INSERT INTO users (nickname, email, pass_hash) VALUES (?, ?, ?)");
    $stmt->execute([$nickname, $email, $pass_hash]);
    
    return ['success' => true, 'user_id' => $db->lastInsertId()];
}

// Obsługa formularza logowania
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("Błąd bezpieczeństwa: nieprawidłowy token CSRF");
    }
    
    $login = sanitize($_POST['login']); // Może być nickname lub email
    $password = $_POST['password'];
    
    if (login($db, $login, $password)) {
        redirect('dashboard');
    } else {
        $login_error = "Nieprawidłowy login lub hasło";
    }
}

// Obsługa formularza rejestracji
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("Błąd bezpieczeństwa: nieprawidłowy token CSRF");
    }
    
    $nickname = sanitize($_POST['nickname']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Walidacja
    $errors = [];
    
    if (strlen($nickname) < 3 || strlen($nickname) > 50) {
        $errors[] = "Nickname musi mieć od 3 do 50 znaków";
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nickname)) {
        $errors[] = "Nickname może zawierać tylko litery, cyfry i podkreślenie";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Nieprawidłowy format email";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Hasło musi mieć co najmniej 6 znaków";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Hasła nie są identyczne";
    }
    
    if (empty($errors)) {
        $result = register($db, $nickname, $email, $password);
        if ($result['success']) {
            // Automatyczne logowanie po rejestracji
            login($db, $nickname, $password);
            redirect('dashboard');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Wylogowanie
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('login');
}

// Funkcja renderująca stronę logowania
function render_login_page() {
    global $login_error;
    ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kurs SQL - Logowanie</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <header>
                <h1>Kurs SQL</h1>
            </header>
            
            <div class="auth-container">
                <div class="auth-box">
                    <h2>Logowanie</h2>
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-error"><?= $login_error ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="index.php?page=login">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="form-group">
                            <label for="login">Nickname lub Email:</label>
                            <input type="text" name="login" id="login" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Hasło:</label>
                            <input type="password" name="password" id="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Zaloguj się</button>
                    </form>
                    
                    <p>Nie masz konta? <a href="index.php?page=register">Zarejestruj się</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Funkcja renderująca stronę rejestracji
function render_register_page() {
    global $errors;
    ?>
    <!DOCTYPE html>
    <html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kurs SQL - Rejestracja</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <header>
                <h1>Kurs SQL</h1>
            </header>
            
            <div class="auth-container">
                <div class="auth-box">
                    <h2>Rejestracja</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="index.php?page=register">
                        <input type="hidden" name="action" value="register">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="form-group">
                            <label for="nickname">Nickname:</label>
                            <input type="text" name="nickname" id="nickname" required>
                            <small>Min. 3 znaki, tylko litery, cyfry i podkreślenia</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Hasło:</label>
                            <input type="password" name="password" id="password" required>
                            <small>Min. 6 znaków</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirm">Powtórz hasło:</label>
                            <input type="password" name="password_confirm" id="password_confirm" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Zarejestruj się</button>
                    </form>
                    
                    <p>Masz już konto? <a href="index.php?page=login">Zaloguj się</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>