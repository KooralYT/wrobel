<?php
// Konfiguracja bazy danych PostgreSQL
define('DB_HOST', 'aws-0-eu-central-1.pooler.supabase.com');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres.skdyyunvyuwajyvmmysc'); // domyślny użytkownik PostgreSQL
define('DB_PASS', 'Pawroon0602'); // twoje hasło

// Maksymalna liczba prób dla pytań
define('MAX_ATTEMPTS_OPEN', 3);
define('MAX_ATTEMPTS_CLOSED', 2);

// Inicjalizacja sesji
session_start();

// Połączenie z bazą danych
try {
    // Połączenie z PostgreSQL zamiast MySQL
    $dsn = "pgsql:host=".DB_HOST.";dbname=".DB_NAME;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Błąd połączenia z bazą danych: ' . $e->getMessage());
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
