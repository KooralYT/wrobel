<?php
// Konfiguracja bazy danych Supabase (PostgreSQL)
// Te dane powinny pochodzić z panelu projektu Supabase
define('DB_HOST', 'db.skdyyunvyuwajyvmmysc.supabase.co');  // Zmień na właściwy host Supabase
define('DB_NAME', 'postgres');  // W Supabase domyślna nazwa bazy to postgres
define('DB_USER', 'postgres');  // W Supabase użytkownik to zwykle postgres
define('DB_PASS', 'Pawroon0602');  // Zmień na hasło z panelu Supabase
define('DB_PORT', '5432');  // Standardowy port PostgreSQL

// Maksymalna liczba prób dla pytań
define('MAX_ATTEMPTS_OPEN', 3);
define('MAX_ATTEMPTS_CLOSED', 2);

// Inicjalizacja sesji
session_start();

// Połączenie z bazą danych
try {
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_USER,
        DB_PASS
    );
    $db = new PDO($dsn);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ustawienie schematu (w Supabase najczęściej używasz schematu "public")
    $db->exec("SET search_path TO public");
} catch (PDOException $e) {
    die('Błąd połączenia z bazą danych Supabase: ' . $e->getMessage());
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
