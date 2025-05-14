<?php
// Konfiguracja bazy danych
define('DB_HOST', 'localhost');
define('DB_NAME', 'sql_course');
define('DB_USER', 'root');
define('DB_PASS', '');

// Maksymalna liczba prób dla pytań
define('MAX_ATTEMPTS_OPEN', 3);
define('MAX_ATTEMPTS_CLOSED', 2);

// Inicjalizacja sesji
session_start();

// Połączenie z bazą danych
try {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Dodajemy niestandardową funkcję do zamiany pustych stringów na NULL
    function nullify_empty($value) {
        return $value === '' ? null : $value;
    }
} catch(PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// Funkcje pomocnicze
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }
}

function check_admin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: index.php?page=dashboard");
        exit;
    }
}

function redirect($page) {
    header("Location: index.php?page=$page");
    exit;
}
?>