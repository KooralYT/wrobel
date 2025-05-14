<?php
require_once 'config.php';
require_once 'functions.php';

// Sprawdź czy użytkownik jest zalogowany i ma uprawnienia administratora
check_admin();

// Funkcja renderująca panel administratora
function render_admin_panel() {
    global $db;
    
    // Pobierz wszystkich użytkowników
    $users = get_all_users($db);
    
    // Pobierz wszystkie testy
    $stmt = $db->query("
        SELECT ut.*, u.email, l.title as lesson_title
        FROM user_tests ut
        JOIN users u ON ut.user_id = u.id
        JOIN lessons l ON ut.lesson_id = l.id
        ORDER BY ut.created_at DESC
        LIMIT 50
    ");
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pobierz lekcje
    $lessons = get_lessons($db);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Panel Administratora</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel Administratora</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['email']) ?> (Administrator)</span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=admin" class="active">Panel Administratora</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="admin-panel">
                <div class="admin-nav">
                    <ul class="tab-links">
                        <li><a href="#users" class="active">Użytkownicy</a></li>
                        <li><a href="#tests">Testy</a></li>
                        <li><a href="#lessons">Lekcje</a></li>
                    </ul>
                </div>
                
                <div class="tab-content">
                    <!-- Zakładka użytkownicy -->
                    <div id="users" class="tab-pane active">
                        <h2>Lista użytkowników</h2>
                        
                        <div class="search-form">
                            <input type="text" id="user-search" placeholder="Szukaj użytkownika...">
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Rola</th>
                                    <th>Data rejestracji</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="index.php?page=admin_user_details&id=<?= $user['id'] ?>" class="btn btn-sm">Szczegóły</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Zakładka testy -->
                    <div id="tests" class="tab-pane">
                        <h2>Historia testów</h2>
                        
                        <div class="search-form">
                            <input type="text" id="test-search" placeholder="Szukaj testu...">
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Użytkownik</th>
                                    <th>Lekcja</th>
                                    <th>Wynik</th>
                                    <th>Próby</th>
                                    <th>Data</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tests as $test): ?>
                                <tr>
                                    <td><?= $test['id'] ?></td>
                                    <td><?= htmlspecialchars($test['email']) ?></td>
                                    <td><?= htmlspecialchars($test['lesson_title']) ?></td>
                                    <td><?= number_format($test['score'], 1) ?>%</td>
                                    <td><?= $test['attempts'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($test['created_at'])) ?></td>
                                    <td>
                                        <a href="index.php?page=admin_test_details&id=<?= $test['id'] ?>" class="btn btn-sm">Szczegóły</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Zakładka lekcje -->
                    <div id="lessons" class="tab-pane">
                        <h2>Zarządzanie lekcjami</h2>
                        
                        <div class="admin-actions">
                            <a href="index.php?page=admin_add_lesson" class="btn btn-primary">Dodaj nową lekcję</a>
                        </div>
                        
                        <div class="search-form">
                            <input type="text" id="lesson-search" placeholder="Szukaj lekcji...">
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tytuł</th>
                                    <th>Kategoria</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lessons as $lesson): ?>
                                <tr>
                                    <td><?= $lesson['id'] ?></td>
                                    <td><?= htmlspecialchars($lesson['title']) ?></td>
                                    <td><?= htmlspecialchars($lesson['category']) ?></td>
                                    <td>
                                        <a href="index.php?page=admin_edit_lesson&id=<?= $lesson['id'] ?>" class="btn btn-sm">Edytuj</a>
                                        <a href="index.php?page=admin_manage_questions&lesson_id=<?= $lesson['id'] ?>" class="btn btn-sm">Pytania</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obsługa zakładek
        const tabLinks = document.querySelectorAll('.tab-links a');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Usuń klasę active ze wszystkich linków i paneli
                tabLinks.forEach(l => l.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));
                
                // Dodaj klasę active do klikniętego linku
                this.classList.add('active');
                
                // Aktywuj odpowiedni panel
                const target = this.getAttribute('href').substring(1);
                document.getElementById(target).classList.add('active');
            });
        });
        
        // Funkcja wyszukiwania w tabelach
        function setupSearch(inputId, tableRows, columnIndex) {
            const searchInput = document.getElementById(inputId);
            if (!searchInput) return;
            
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const text = row.cells[columnIndex].textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
        
        // Ustawienie wyszukiwania dla każdej tabeli
        setupSearch('user-search', document.querySelectorAll('#users tbody tr'), 1); // Email
        setupSearch('test-search', document.querySelectorAll('#tests tbody tr'), 2); // Lekcja
        setupSearch('lesson-search', document.querySelectorAll('#lessons tbody tr'), 1); // Tytuł

        // Funkcja do pokazywania/ukrywania odpowiednich pól w zależności od typu pytania
        function toggleAnswerFields() {
            if (questionType.value === 'open') {
                openAnswer.style.display = 'block';
                choiceAnswers.style.display = 'none';
                
                // Upewnij się, że pole open_answer jest wymagane tylko gdy jest widoczne
                document.getElementById('open_answer').required = true;
                
                // Usuń atrybut required z pól odpowiedzi wyboru
                const answerInputs = answersContainer.querySelectorAll('input[type="text"]');
                answerInputs.forEach(input => {
                    input.required = false;
                });
            } else {
                openAnswer.style.display = 'none';
                choiceAnswers.style.display = 'block';
                
                // Usuń wymaganie dla pola open_answer gdy jest ukryte
                document.getElementById('open_answer').required = false;
                
                // Ustaw wymagane dla pól odpowiedzi wyboru
                const answerInputs = answersContainer.querySelectorAll('input[type="text"]');
                answerInputs.forEach(input => {
                    input.required = true;
                });
            }
        }
    });
    </script>
</body>
</html>
<?php
}

// Funkcja renderująca szczegóły użytkownika z rozszerzonymi statystykami
function render_user_details($user_id) {
    global $db;
    
    // Pobierz dane użytkownika
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        redirect('admin');
    }
    
    // Pobierz testy użytkownika
    $stmt = $db->prepare("
        SELECT ut.*, l.title as lesson_title, l.category
        FROM user_tests ut
        JOIN lessons l ON ut.lesson_id = l.id
        WHERE ut.user_id = ?
        ORDER BY ut.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Oblicz podstawowe statystyki
    $total_tests = count($tests);
    $average_score = 0;
    $total_correct_answers = 0;
    $total_answered_questions = 0;
    $best_score = 0;
    $worst_score = 100;
    $category_stats = [];
    
    if ($total_tests > 0) {
        // Średni wynik
        $sum_score = array_sum(array_column($tests, 'score'));
        $average_score = $sum_score / $total_tests;
        
        // Najlepszy i najgorszy wynik
        $scores = array_column($tests, 'score');
        $best_score = max($scores);
        $worst_score = min($scores);
        
        // Pobierz łączną liczbę poprawnych odpowiedzi
        $stmt = $db->prepare("
            SELECT COUNT(*) as total, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct
            FROM user_answers ua
            JOIN user_tests ut ON ua.user_test_id = ut.id
            WHERE ut.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $answers_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($answers_stats) {
            $total_correct_answers = $answers_stats['correct'] ?? 0;
            $total_answered_questions = $answers_stats['total'] ?? 0;
        }
        
        // Statystyki według kategorii
        foreach ($tests as $test) {
            $category = $test['category'];
            if (!isset($category_stats[$category])) {
                $category_stats[$category] = [
                    'count' => 0,
                    'sum_score' => 0,
                    'avg_score' => 0
                ];
            }
            $category_stats[$category]['count']++;
            $category_stats[$category]['sum_score'] += $test['score'];
        }
        
        // Oblicz średnie wyniki dla każdej kategorii
        foreach ($category_stats as $category => $stats) {
            $category_stats[$category]['avg_score'] = 
                $stats['sum_score'] / $stats['count'];
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Szczegóły użytkownika</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .user-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .stat-box {
            background-color: #f5f5f5;
            border-left: 3px solid #3498db;
            padding: 15px;
            border-radius: 4px;
        }
        
        .stat-box h4 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .progress-container {
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #27ae60;
            border-radius: 5px;
        }
        
        .category-stats {
            margin-top: 20px;
        }
        
        .category-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .category-name {
            font-weight: bold;
        }
        
        .category-average {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel Administratora</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['nickname']) ?> (Administrator)</span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=admin" class="active">Panel Administratora</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="admin-panel">
                <div class="breadcrumb">
                    <a href="index.php?page=admin">Panel Administratora</a> &gt; Szczegóły użytkownika
                </div>
                
                <div class="user-details">
                    <h2>Szczegóły użytkownika</h2>
                    
                    <div class="user-info-card">
                        <h3>Informacje podstawowe</h3>
                        <table class="details-table">
                            <tr>
                                <th>ID:</th>
                                <td><?= $user['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Nickname:</th>
                                <td><?= htmlspecialchars($user['nickname']) ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Rola:</th>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                            </tr>
                            <tr>
                                <th>Data rejestracji:</th>
                                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="user-stats-card">
                        <h3>Statystyki</h3>
                        
                        <div class="user-stats-grid">
                            <div class="stat-box">
                                <h4>Ukończone testy</h4>
                                <p class="stat-value"><?= $total_tests ?></p>
                            </div>
                            
                            <div class="stat-box">
                                <h4>Średni wynik</h4>
                                <p class="stat-value"><?= number_format($average_score, 1) ?>%</p>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?= min(100, $average_score) ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="stat-box">
                                <h4>Najlepszy wynik</h4>
                                <p class="stat-value"><?= number_format($best_score, 1) ?>%</p>
                            </div>
                            
                            <div class="stat-box">
                                <h4>Najgorszy wynik</h4>
                                <p class="stat-value"><?= $total_tests > 0 ? number_format($worst_score, 1) . '%' : 'N/A' ?></p>
                            </div>
                            
                            <div class="stat-box">
                                <h4>Poprawne odpowiedzi</h4>
                                <p class="stat-value"><?= $total_correct_answers ?> / <?= $total_answered_questions ?></p>
                                <?php if ($total_answered_questions > 0): ?>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?= ($total_correct_answers / $total_answered_questions) * 100 ?>%"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="stat-box">
                                <h4>Skuteczność</h4>
                                <?php $effectiveness = $total_answered_questions > 0 ? 
                                    ($total_correct_answers / $total_answered_questions) * 100 : 0; ?>
                                <p class="stat-value"><?= number_format($effectiveness, 1) ?>%</p>
                            </div>
                        </div>
                        
                        <?php if (!empty($category_stats)): ?>
                        <div class="category-stats">
                            <h4>Wyniki według kategorii</h4>
                            
                            <?php foreach ($category_stats as $category => $stats): ?>
                            <div class="category-row">
                                <span class="category-name"><?= htmlspecialchars($category) ?></span>
                                <span class="category-tests"><?= $stats['count'] ?> testów</span>
                                <span class="category-average"><?= number_format($stats['avg_score'], 1) ?>%</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-tests-card">
                        <h3>Historia testów</h3>
                        
                        <?php if (empty($tests)): ?>
                        <p>Ten użytkownik nie rozwiązał jeszcze żadnych testów.</p>
                        <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lekcja</th>
                                    <th>Kategoria</th>
                                    <th>Wynik</th>
                                    <th>Próby</th>
                                    <th>Data</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tests as $test): ?>
                                <tr>
                                    <td><?= $test['id'] ?></td>
                                    <td><?= htmlspecialchars($test['lesson_title']) ?></td>
                                    <td><?= htmlspecialchars($test['category']) ?></td>
                                    <td><?= number_format($test['score'], 1) ?>%</td>
                                    <td><?= $test['attempts'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($test['created_at'])) ?></td>
                                    <td>
                                        <a href="index.php?page=admin_test_details&id=<?= $test['id'] ?>" class="btn btn-sm">Szczegóły</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php?page=admin" class="btn">Powrót</a>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>
<?php
}

// Funkcja renderująca szczegóły testu z dokładnymi odpowiedziami ucznia
function render_test_details($test_id) {
    global $db;
    
    // Pobierz dane testu
    $stmt = $db->prepare("
        SELECT ut.*, u.nickname, u.email, l.title as lesson_title, l.category
        FROM user_tests ut
        JOIN users u ON ut.user_id = u.id
        JOIN lessons l ON ut.lesson_id = l.id
        WHERE ut.id = ?
    ");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$test) {
        redirect('admin');
    }
    
    // Pobierz szczegóły odpowiedzi
    $stmt = $db->prepare("
        SELECT ua.*, q.text as question_text, q.type as question_type
        FROM user_answers ua
        JOIN questions q ON ua.question_id = q.id
        WHERE ua.user_test_id = ?
    ");
    $stmt->execute([$test_id]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Szczegóły testu</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .answer-comparison {
            display: flex;
            margin-top: 15px;
            gap: 20px;
        }
        
        .student-answer, .correct-answer {
            flex: 1;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
        }
        
        .student-answer {
            border-left: 4px solid #e74c3c;
        }
        
        .student-answer.correct {
            border-left: 4px solid #27ae60;
        }
        
        .correct-answer {
            border-left: 4px solid #27ae60;
        }
        
        .answer-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .answer-code {
            background-color: #fff;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .answer-options {
            list-style-type: none;
            padding: 0;
        }
        
        .answer-option {
            padding: 5px 0;
        }
        
        .answer-option.selected {
            font-weight: bold;
        }
        
        .answer-option.correct-option {
            color: #27ae60;
        }
        
        .answer-option.incorrect-option {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel Administratora</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['nickname']) ?> (Administrator)</span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=admin" class="active">Panel Administratora</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="admin-panel">
                <div class="breadcrumb">
                    <a href="index.php?page=admin">Panel Administratora</a> &gt; Szczegóły testu
                </div>
                
                <div class="test-details">
                    <h2>Szczegóły testu</h2>
                    
                    <div class="test-info-card">
                        <h3>Informacje o teście</h3>
                        <table class="details-table">
                            <tr>
                                <th>ID testu:</th>
                                <td><?= $test['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Użytkownik:</th>
                                <td><?= htmlspecialchars($test['nickname']) ?> (<?= htmlspecialchars($test['email']) ?>)</td>
                            </tr>
                            <tr>
                                <th>Lekcja:</th>
                                <td><?= htmlspecialchars($test['lesson_title']) ?></td>
                            </tr>
                            <tr>
                                <th>Kategoria:</th>
                                <td><?= htmlspecialchars($test['category']) ?></td>
                            </tr>
                            <tr>
                                <th>Wynik:</th>
                                <td><?= number_format($test['score'], 1) ?>%</td>
                            </tr>
                            <tr>
                                <th>Próby:</th>
                                <td><?= $test['attempts'] ?></td>
                            </tr>
                            <tr>
                                <th>Data:</th>
                                <td><?= date('d.m.Y H:i', strtotime($test['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="test-answers-card">
                        <h3>Odpowiedzi</h3>
                        
                        <?php foreach ($answers as $index => $answer): ?>
                        <div class="answer-card <?= $answer['is_correct'] ? 'correct' : 'incorrect' ?>">
                            <h4>Pytanie <?= $index + 1 ?>:</h4>
                            <p class="question-text"><?= htmlspecialchars($answer['question_text']) ?></p>
                            
                            <div class="answer-details">
                                <p><strong>Typ pytania:</strong> 
                                    <?php
                                    switch($answer['question_type']) {
                                        case 'single': echo 'Jednokrotnego wyboru'; break;
                                        case 'multi': echo 'Wielokrotnego wyboru'; break;
                                        case 'open': echo 'Otwarte'; break;
                                    }
                                    ?>
                                </p>
                                
                                <div class="answer-comparison">
                                    <div class="student-answer <?= $answer['is_correct'] ? 'correct' : '' ?>">
                                        <div class="answer-title">Odpowiedź ucznia:</div>
                                        
                                        <?php if ($answer['question_type'] === 'open'): ?>
                                            <div class="answer-code"><?= htmlspecialchars($answer['answer_text']) ?></div>
                                        <?php elseif ($answer['question_type'] === 'single'): ?>
                                            <?php
                                            // Pobierz wszystkie możliwe odpowiedzi
                                            $stmt = $db->prepare("
                                                SELECT a.*, a.id = ? as is_selected
                                                FROM answers a
                                                WHERE a.question_id = ?
                                                ORDER BY a.id
                                            ");
                                            $stmt->execute([$answer['answer_text'], $answer['question_id']]);
                                            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            
                                            <ul class="answer-options">
                                                <?php foreach ($options as $option): ?>
                                                <li class="answer-option <?= $option['is_selected'] ? 'selected' : '' ?> <?= $option['is_selected'] && $option['is_correct'] ? 'correct-option' : ($option['is_selected'] ? 'incorrect-option' : '') ?>">
                                                    <?= $option['is_selected'] ? '✓' : '○' ?> <?= htmlspecialchars($option['text']) ?>
                                                    <?= $option['is_selected'] && $option['is_correct'] ? ' (Poprawnie)' : ($option['is_selected'] ? ' (Niepoprawnie)' : '') ?>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php elseif ($answer['question_type'] === 'multi'): ?>
                                            <?php
                                            // Rozbij łańcuch na tablica ID odpowiedzi
                                            $selected_ids = explode(',', $answer['answer_text']);
                                            
                                            // Pobierz wszystkie możliwe odpowiedzi
                                            $stmt = $db->prepare("
                                                SELECT a.*
                                                FROM answers a
                                                WHERE a.question_id = ?
                                                ORDER BY a.id
                                            ");
                                            $stmt->execute([$answer['question_id']]);
                                            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            
                                            <ul class="answer-options">
                                                <?php foreach ($options as $option): 
                                                    $is_selected = in_array($option['id'], $selected_ids);
                                                    $css_class = '';
                                                    if ($is_selected && $option['is_correct']) {
                                                        $css_class = 'correct-option';
                                                    } elseif ($is_selected && !$option['is_correct']) {
                                                        $css_class = 'incorrect-option';
                                                    } elseif (!$is_selected && $option['is_correct']) {
                                                        $css_class = 'missed-option';
                                                    }
                                                ?>
                                                <li class="answer-option <?= $is_selected ? 'selected' : '' ?> <?= $css_class ?>">
                                                    <?= $is_selected ? '✓' : '○' ?> <?= htmlspecialchars($option['text']) ?>
                                                    <?php 
                                                        if ($is_selected && $option['is_correct']) {
                                                            echo ' (Poprawnie)';
                                                        } elseif ($is_selected && !$option['is_correct']) {
                                                            echo ' (Niepoprawnie)';
                                                        } elseif (!$is_selected && $option['is_correct']) {
                                                            echo ' (Pominięta poprawna)';
                                                        }
                                                    ?>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="correct-answer">
                                        <div class="answer-title">Poprawna odpowiedź:</div>
                                        
                                        <?php if ($answer['question_type'] === 'open'): ?>
                                            <?php
                                            $stmt = $db->prepare("
                                                SELECT a.text
                                                FROM answers a
                                                WHERE a.question_id = ? AND a.is_correct = 1
                                                LIMIT 1
                                            ");
                                            $stmt->execute([$answer['question_id']]);
                                            $correct = $stmt->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <div class="answer-code"><?= htmlspecialchars($correct['text'] ?? 'Brak wzorca odpowiedzi') ?></div>
                                        <?php elseif ($answer['question_type'] === 'single'): ?>
                                            <?php
                                            $stmt = $db->prepare("
                                                SELECT a.*
                                                FROM answers a
                                                WHERE a.question_id = ? AND a.is_correct = 1
                                                LIMIT 1
                                            ");
                                            $stmt->execute([$answer['question_id']]);
                                            $correct = $stmt->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <?php if ($correct): ?>
                                            <p><?= htmlspecialchars($correct['text']) ?></p>
                                            <?php else: ?>
                                            <p>Brak poprawnej odpowiedzi w bazie.</p>
                                            <?php endif; ?>
                                        <?php elseif ($answer['question_type'] === 'multi'): ?>
                                            <?php
                                            $stmt = $db->prepare("
                                                SELECT a.*
                                                FROM answers a
                                                WHERE a.question_id = ? AND a.is_correct = 1
                                                ORDER BY a.id
                                            ");
                                            $stmt->execute([$answer['question_id']]);
                                            $corrects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            <ul class="answer-options">
                                                <?php foreach ($corrects as $correct): ?>
                                                <li class="answer-option"><?= htmlspecialchars($correct['text']) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php if (empty($corrects)): ?>
                                            <p>Brak poprawnych odpowiedzi w bazie.</p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p class="answer-status">
                                    <strong>Status:</strong> 
                                    <?php if ($answer['is_correct']): ?>
                                    <span class="status-correct">Poprawna</span>
                                    <?php else: ?>
                                    <span class="status-incorrect">Niepoprawna</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php?page=admin" class="btn">Powrót</a>
                        <a href="index.php?page=admin_user_details&id=<?= $test['user_id'] ?>" class="btn btn-secondary">Profil użytkownika</a>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>
<?php
}

// Funkcja renderująca formularz dodawania lekcji
function render_add_lesson() {
    global $db;
    
    $message = '';
    $messageType = '';
    
    // Obsługa formularza
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'], $_POST['category'])) {
        if (verify_csrf_token($_POST['csrf_token'])) {
            $title = sanitize($_POST['title']);
            $content = $_POST['content']; // Nie sanityzujemy zawartości, bo może zawierać format tekstu
            $category = sanitize($_POST['category']);
            
            try {
                $stmt = $db->prepare("INSERT INTO lessons (title, content, category) VALUES (?, ?, ?)");
                $stmt->execute([$title, $content, $category]);
                $message = "Lekcja została pomyślnie dodana";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Wystąpił błąd podczas dodawania lekcji: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Dodaj lekcję</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel Administratora</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['email']) ?> (Administrator)</span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=admin" class="active">Panel Administratora</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="admin-panel">
                <div class="breadcrumb">
                    <a href="index.php?page=admin">Panel Administratora</a> &gt; Dodaj lekcję
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>
                
                <div class="lesson-form">
                    <h2>Dodaj nową lekcję</h2>
                    
                    <form method="post" action="index.php?page=admin_add_lesson">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="form-group">
                            <label for="title">Tytuł lekcji:</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Kategoria:</label>
                            <select name="category" id="category" required>
                                <option value="">Wybierz kategorię</option>
                                <option value="Podstawy">Podstawy</option>
                                <option value="DQL">DQL</option>
                                <option value="DML">DML</option>
                                <option value="DDL">DDL</option>
                                <option value="Zaawansowane">Zaawansowane</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Treść lekcji:</label>
                            <textarea name="content" id="content" required rows="15"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php?page=admin" class="btn">Anuluj</a>
                            <button type="submit" class="btn btn-primary">Zapisz lekcję</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>
<?php
}

// Funkcja renderująca formularz edycji lekcji
function render_edit_lesson($lesson_id) {
    global $db;
    
    $message = '';
    $messageType = '';
    
    // Pobierz dane lekcji
    $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lesson) {
        redirect('admin');
    }
    
    // Obsługa formularza
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'], $_POST['category'])) {
        if (verify_csrf_token($_POST['csrf_token'])) {
            $title = sanitize($_POST['title']);
            $content = $_POST['content']; // Nie sanityzujemy zawartości, bo może zawierać format tekstu
            $category = sanitize($_POST['category']);
            
            try {
                $stmt = $db->prepare("UPDATE lessons SET title = ?, content = ?, category = ? WHERE id = ?");
                $stmt->execute([$title, $content, $category, $lesson_id]);
                $message = "Lekcja została pomyślnie zaktualizowana";
                $messageType = "success";
                
                // Zaktualizuj dane lekcji po edycji
                $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
                $stmt->execute([$lesson_id]);
                $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $message = "Wystąpił błąd podczas aktualizacji lekcji: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Edytuj lekcję</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel Administratora</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['email']) ?> (Administrator)</span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=admin" class="active">Panel Administratora</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="admin-panel">
                <div class="breadcrumb">
                    <a href="index.php?page=admin">Panel Administratora</a> &gt; Edytuj lekcję
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>
                
                <div class="lesson-form">
                    <h2>Edytuj lekcję: <?= htmlspecialchars($lesson['title']) ?></h2>
                    
                    <form method="post" action="index.php?page=admin_edit_lesson&id=<?= $lesson_id ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="form-group">
                            <label for="title">Tytuł lekcji:</label>
                            <input type="text" name="title" id="title" required value="<?= htmlspecialchars($lesson['title']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Kategoria:</label>
                            <select name="category" id="category" required>
                                <option value="">Wybierz kategorię</option>
                                <option value="Podstawy" <?= $lesson['category'] === 'Podstawy' ? 'selected' : '' ?>>Podstawy</option>
                                <option value="DQL" <?= $lesson['category'] === 'DQL' ? 'selected' : '' ?>>DQL</option>
                                <option value="DML" <?= $lesson['category'] === 'DML' ? 'selected' : '' ?>>DML</option>
                                <option value="DDL" <?= $lesson['category'] === 'DDL' ? 'selected' : '' ?>>DDL</option>
                                <option value="Zaawansowane" <?= $lesson['category'] === 'Zaawansowane' ? 'selected' : '' ?>>Zaawansowane</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Treść lekcji:</label>
                            <textarea name="content" id="content" required rows="15"><?= htmlspecialchars($lesson['content']) ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php?page=admin" class="btn">Anuluj</a>
                            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>
<?php
}

// Funkcja renderująca zarządzanie pytaniami
function render_manage_questions($lesson_id) {
    global $db;
    
    $message = '';
    $messageType = '';
    
    // Pobierz dane lekcji
    $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lesson) {
        redirect('admin');
    }
    
    // Pobierz pytania dla tej lekcji
    $questions = get_questions_for_lesson($db, $lesson_id);
    
    // Obsługa dodawania nowego pytania
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_question') {
        if (verify_csrf_token($_POST['csrf_token'])) {
            $question_text = sanitize($_POST['question_text']);
            $question_type = sanitize($_POST['question_type']);
            
            try {
                // Dodaj nowe pytanie
                $stmt = $db->prepare("INSERT INTO questions (lesson_id, type, text) VALUES (?, ?, ?)");
                $stmt->execute([$lesson_id, $question_type, $question_text]);
                $question_id = $db->lastInsertId();
                
                // Dodaj odpowiedzi dla pytania
                if ($question_type === 'open') {
                    // Dla pytań otwartych dodajemy tylko jedną poprawną odpowiedź
                    $answer_text = sanitize($_POST['open_answer']);
                    $stmt = $db->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, 1)");
                    $stmt->execute([$question_id, $answer_text]);
                } else {
                    // Dla pytań zamkniętych dodajemy wszystkie opcje
                    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
                    $correct_answers = isset($_POST['correct_answers']) ? $_POST['correct_answers'] : [];
                    
                    foreach ($answers as $index => $answer_text) {
                        $is_correct = in_array($index, $correct_answers) ? 1 : 0;
                        $stmt = $db->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)");
                        $stmt->execute([$question_id, $answer_text, $is_correct]);
                    }
                }
                
                $message = "Pytanie zostało pomyślnie dodane";
                $messageType = "success";
                
                // Odświeżanie listy pytań po dodaniu
                $questions = get_questions_for_lesson($db, $lesson_id);
            } catch (Exception $e) {
                $message = "Wystąpił błąd podczas dodawania pytania: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // Obsługa edycji pytania
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_question') {
        if (verify_csrf_token($_POST['csrf_token'])) {
            $question_id = (int)$_POST['question_id'];
            $question_text = sanitize($_POST['question_text']);
            
            try {
                // Aktualizuj pytanie
                $stmt = $db->prepare("UPDATE questions SET text = ? WHERE id = ? AND lesson_id = ?");
                $stmt->execute([$question_text, $question_id, $lesson_id]);
                
                // Usuń stare odpowiedzi
                $stmt = $db->prepare("DELETE FROM answers WHERE question_id = ?");
                $stmt->execute([$question_id]);
                
                // Dodaj nowe odpowiedzi
                $question_type = sanitize($_POST['question_type']);
                
                if ($question_type === 'open') {
                    $answer_text = sanitize($_POST['open_answer']);
                    $stmt = $db->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, 1)");
                    $stmt->execute([$question_id, $answer_text]);
                } else {
                    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
                    $correct_answers = isset($_POST['correct_answers']) ? $_POST['correct_answers'] : [];
                    
                    foreach ($answers as $index => $answer_text) {
                        $is_correct = in_array($index, $correct_answers) ? 1 : 0;
                        $stmt = $db->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)");
                        $stmt->execute([$question_id, $answer_text, $is_correct]);
                    }
                }
                
                $message = "Pytanie zostało pomyślnie zaktualizowane";
                $messageType = "success";
                
                // Odświeżanie listy pytań po edycji
                $questions = get_questions_for_lesson($db, $lesson_id);
            } catch (Exception $e) {
                $message = "Wystąpił błąd podczas aktualizacji pytania: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
    
    // Obsługa usuwania pytania
    if (isset($_GET['delete_question']) && is_numeric($_GET['delete_question'])) {
        $question_id = (int)$_GET['delete_question'];
        
        // Sprawdź czy pytanie należy do tej lekcji
        $stmt = $db->prepare("SELECT id FROM questions WHERE id = ? AND lesson_id = ?");
        $stmt->execute([$question_id, $lesson_id]);
        
        if ($stmt->fetch()) {
            try {
                // Usunięcie pytania (odpowiedzi usuną się automatycznie dzięki ON DELETE CASCADE)
                $stmt = $db->prepare("DELETE FROM questions WHERE id = ?");
                $stmt->execute([$question_id]);
                
                $message = "Pytanie zostało pomyślnie usunięte";
                $messageType = "success";
                
                // Odświeżanie listy pytań po usunięciu
                $questions = get_questions_for_lesson($db, $lesson_id);
            } catch (Exception $e) {
                $message = "Wystąpił błąd podczas usuwania pytania: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Zarządzanie pytaniami</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .question-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 3px solid var(--secondary-color);
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .question-meta {
            font-size: 0.9em;
            color: #777;
        }
        
        .question-answers {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .question-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .correct-answer {
            color: var(--success-color);
            font-weight: bold;
        }
        
        .incorrect-answer {
            color: inherit;
        }
        
        .answer-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .answer-row input[type="text"] {
            flex: 1;
            margin-right: 10px;
        }
        
        .btn-danger {
            background-color: var(--error-color);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 600px;
        }
        
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: var(--dark-gray);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel Administratora</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['email']) ?> (Administrator)</span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <li><a href="index.php?page=admin" class="active">Panel Administratora</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="admin-panel">
                <div class="breadcrumb">
                    <a href="index.php?page=admin">Panel Administratora</a> &gt; 
                    Pytania do lekcji: <?= htmlspecialchars($lesson['title']) ?>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>
                
                <div class="questions-panel">
                    <h2>Zarządzanie pytaniami</h2>
                    <p>Lekcja: <strong><?= htmlspecialchars($lesson['title']) ?></strong> | Kategoria: <strong><?= htmlspecialchars($lesson['category']) ?></strong></p>
                    
                    <div class="questions-list">
                        <h3>Istniejące pytania</h3>
                        
                        <?php if (empty($questions)): ?>
                        <p>Brak pytań dla tej lekcji.</p>
                        <?php else: ?>
                        <?php foreach ($questions as $index => $question): ?>
                        <div class="question-item">
                            <div class="question-header">
                                <h4>Pytanie <?= $index + 1 ?>: <?= htmlspecialchars($question['text']) ?></h4>
                                <div class="question-meta">
                                    <span class="question-type">
                                        Typ: 
                                        <?php
                                        switch($question['type']) {
                                            case 'single': echo 'Jednokrotnego wyboru'; break;
                                            case 'multi': echo 'Wielokrotnego wyboru'; break;
                                            case 'open': echo 'Otwarte'; break;
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="question-answers">
                                <h5>Odpowiedzi:</h5>
                                <?php
                                $answers = get_answers_for_question($db, $question['id']);
                                if (!empty($answers)):
                                ?>
                                <ul>
                                    <?php foreach ($answers as $answer): ?>
                                    <li class="<?= $answer['is_correct'] ? 'correct-answer' : 'incorrect-answer' ?>">
                                        <?= htmlspecialchars($answer['text']) ?>
                                        <?= $answer['is_correct'] ? ' (Poprawna)' : '' ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p>Brak odpowiedzi dla tego pytania.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="question-actions">
                                <button class="btn btn-sm edit-question-btn" data-id="<?= $question['id'] ?>" data-type="<?= $question['type'] ?>">Edytuj pytanie</button>
                                <a href="index.php?page=admin_manage_questions&lesson_id=<?= $lesson_id ?>&delete_question=<?= $question['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Czy na pewno chcesz usunąć to pytanie?')">Usuń pytanie</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="add-question">
                        <h3>Dodaj nowe pytanie</h3>
                        
                        <form method="post" action="index.php?page=admin_manage_questions&lesson_id=<?= $lesson_id ?>" id="add-question-form">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="add_question">
                            
                            <div class="form-group">
                                <label for="question_text">Treść pytania:</label>
                                <textarea name="question_text" id="question_text" required rows="3"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="question_type">Typ pytania:</label>
                                <select name="question_type" id="question_type" required>
                                    <option value="single">Jednokrotnego wyboru</option>
                                    <option value="multi">Wielokrotnego wyboru</option>
                                    <option value="open">Otwarte</option>
                                </select>
                            </div>
                            
                            <div id="open-answer" style="display: none;">
                                <div class="form-group">
                                    <label for="open_answer">Poprawna odpowiedź:</label>
                                    <input type="text" name="open_answer" id="open_answer">
                                    <small>Dla pytań otwartych, podaj poprawną odpowiedź.</small>
                                </div>
                            </div>
                            
                            <div id="choice-answers">
                                <div class="form-group">
                                    <label>Odpowiedzi:</label>
                                    <div id="answers-container">
                                        <div class="answer-row">
                                            <input type="text" name="answers[]" placeholder="Odpowiedź 1" required>
                                            <label><input type="checkbox" name="correct_answers[]" value="0"> Poprawna</label>
                                        </div>
                                        <div class="answer-row">
                                            <input type="text" name="answers[]" placeholder="Odpowiedź 2" required>
                                            <label><input type="checkbox" name="correct_answers[]" value="1"> Poprawna</label>
                                        </div>
                                    </div>
                                    <button type="button" id="add-answer" class="btn btn-sm">Dodaj kolejną odpowiedź</button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <a href="index.php?page=admin" class="btn">Powrót</a>
                                <button type="submit" class="btn btn-primary">Dodaj pytanie</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
    
    <!-- Modal do edycji pytania -->
    <div id="edit-question-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edycja pytania</h2>
            
            <form method="post" action="index.php?page=admin_manage_questions&lesson_id=<?= $lesson_id ?>" id="edit-question-form">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="edit_question">
                <input type="hidden" name="question_id" id="edit_question_id">
                <input type="hidden" name="question_type" id="edit_question_type">
                
                <div class="form-group">
                    <label for="edit_question_text">Treść pytania:</label>
                    <textarea name="question_text" id="edit_question_text" required rows="3"></textarea>
                </div>
                
                <div id="edit-open-answer" style="display: none;">
                    <div class="form-group">
                        <label for="edit_open_answer">Poprawna odpowiedź:</label>
                        <input type="text" name="open_answer" id="edit_open_answer">
                    </div>
                </div>
                
                <div id="edit-choice-answers" style="display: none;">
                    <div class="form-group">
                        <label>Odpowiedzi:</label>
                        <div id="edit-answers-container">
                            <!-- Pola odpowiedzi będą dodawane dynamicznie -->
                        </div>
                        <button type="button" id="edit-add-answer" class="btn btn-sm">Dodaj kolejną odpowiedź</button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn close-modal-btn">Anuluj</button>
                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obsługa formularza dodawania pytania
        const questionType = document.getElementById('question_type');
        const openAnswer = document.getElementById('open-answer');
        const choiceAnswers = document.getElementById('choice-answers');
        const addAnswerBtn = document.getElementById('add-answer');
        const answersContainer = document.getElementById('answers-container');
        
        // Funkcja do pokazywania/ukrywania odpowiednich pól w zależności od typu pytania
        function toggleAnswerFields() {
            if (questionType.value === 'open') {
                openAnswer.style.display = 'block';
                choiceAnswers.style.display = 'none';
                
                // Upewnij się, że pole open_answer jest wymagane tylko gdy jest widoczne
                document.getElementById('open_answer').required = true;
                
                // Usuń atrybut required z pól odpowiedzi wyboru
                const answerInputs = answersContainer.querySelectorAll('input[type="text"]');
                answerInputs.forEach(input => {
                    input.required = false;
                });
            } else {
                openAnswer.style.display = 'none';
                choiceAnswers.style.display = 'block';
                
                // Usuń wymaganie dla pola open_answer gdy jest ukryte
                document.getElementById('open_answer').required = false;
                
                // Ustaw wymagane dla pól odpowiedzi wyboru
                const answerInputs = answersContainer.querySelectorAll('input[type="text"]');
                answerInputs.forEach(input => {
                    input.required = true;
                });
            }
        }
        
        // Nasłuchiwanie zmiany typu pytania
        questionType.addEventListener('change', toggleAnswerFields);
        
        // Inicjalne ustawienie pól
        toggleAnswerFields();
        
        // Dodawanie kolejnych odpowiedzi
        let answerCount = 2;
        
        addAnswerBtn.addEventListener('click', function() {
            answerCount++;
            const answerRow = document.createElement('div');
            answerRow.className = 'answer-row';
            answerRow.innerHTML = `
                <input type="text" name="answers[]" placeholder="Odpowiedź ${answerCount}" required>
                <label><input type="checkbox" name="correct_answers[]" value="${answerCount - 1}"> Poprawna</label>
            `;
            answersContainer.appendChild(answerRow);
        });
        
        // Obsługa modala edycji pytania
        const modal = document.getElementById('edit-question-modal');
        const closeModal = document.getElementsByClassName('close-modal')[0];
        const closeModalBtn = document.getElementsByClassName('close-modal-btn')[0];
        const editForm = document.getElementById('edit-question-form');
        const editQuestionId = document.getElementById('edit_question_id');
        const editQuestionType = document.getElementById('edit_question_type');
        const editQuestionText = document.getElementById('edit_question_text');
        const editOpenAnswer = document.getElementById('edit-open-answer');
        const editChoiceAnswers = document.getElementById('edit-choice-answers');
        const editAnswersContainer = document.getElementById('edit-answers-container');
        const editAddAnswerBtn = document.getElementById('edit-add-answer');
        
        // Pobieranie wszystkich przycisków edycji
        const editButtons = document.querySelectorAll('.edit-question-btn');
        
        // Dla każdego przycisku edycji dodaj obsługę kliknięcia
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const questionId = this.getAttribute('data-id');
                const questionType = this.getAttribute('data-type');
                
                // Wypełnij formularz danymi pytania
                fillEditForm(questionId, questionType);
                
                // Pokaż modal
                modal.style.display = 'block';
            });
        });
        
        // Zamykanie modala
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Zamknij modal po kliknięciu poza nim
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Funkcja do wypełniania formularza edycji
        async function fillEditForm(questionId, questionType) {
            // Ustaw identyfikator pytania i typ
            editQuestionId.value = questionId;
            editQuestionType.value = questionType;
            
            try {
                // Pobierz dane pytania
                const response = await fetch(`index.php?page=admin_get_question&id=${questionId}`);
                const data = await response.json();
                
                // Wypełnij treść pytania
                editQuestionText.value = data.text;
                
                // Pokaż odpowiednie pola w zależności od typu pytania
                if (questionType === 'open') {
                    editOpenAnswer.style.display = 'block';
                    editChoiceAnswers.style.display = 'none';
                    
                    // Wypełnij pole odpowiedzi otwartej
                    if (data.answers && data.answers.length > 0) {
                        document.getElementById('edit_open_answer').value = data.answers[0].text;
                    }
                } else {
                    editOpenAnswer.style.display = 'none';
                    editChoiceAnswers.style.display = 'block';
                    
                    // Wyczyść istniejące odpowiedzi
                    editAnswersContainer.innerHTML = '';
                    
                    // Dodaj pola dla odpowiedzi
                    if (data.answers) {
                        data.answers.forEach((answer, index) => {
                            const answerRow = document.createElement('div');
                            answerRow.className = 'answer-row';
                            answerRow.innerHTML = `
                                <input type="text" name="answers[]" value="${answer.text}" required>
                                <label><input type="checkbox" name="correct_answers[]" value="${index}" ${answer.is_correct ? 'checked' : ''}> Poprawna</label>
                            `;
                            editAnswersContainer.appendChild(answerRow);
                        });
                    }
                }
            } catch (error) {
                console.error('Błąd podczas pobierania danych pytania:', error);
                alert('Wystąpił błąd podczas pobierania danych pytania. Spróbuj ponownie.');
                modal.style.display = 'none';
            }
        }
        
        // Dodawanie kolejnych odpowiedzi w formularzu edycji
        editAddAnswerBtn.addEventListener('click', function() {
            const answerCount = editAnswersContainer.children.length;
            const answerRow = document.createElement('div');
            answerRow.className = 'answer-row';
            answerRow.innerHTML = `
                <input type="text" name="answers[]" placeholder="Odpowiedź ${answerCount + 1}" required>
                <label><input type="checkbox" name="correct_answers[]" value="${answerCount}"> Poprawna</label>
            `;
            editAnswersContainer.appendChild(answerRow);
        });
    });
    </script>
</body>
</html>
<?php
}

// Funkcja do pobierania danych pytania w formacie JSON (do edycji)
function get_question_json($question_id) {
    global $db;
    
    // Pobierz dane pytania
    $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$question) {
        http_response_code(404);
        echo json_encode(['error' => 'Pytanie nie istnieje']);
        exit;
    }
    
    // Pobierz odpowiedzi
    $stmt = $db->prepare("SELECT * FROM answers WHERE question_id = ?");
    $stmt->execute([$question_id]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Przygotuj dane do zwrócenia
    $result = [
        'id' => $question['id'],
        'text' => $question['text'],
        'type' => $question['type'],
        'lesson_id' => $question['lesson_id'],
        'answers' => $answers
    ];
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Obsługa żądań administratora
if (isset($_GET['subpage'])) {
    $subpage = $_GET['subpage'];
    
    switch ($subpage) {
        case 'user_details':
            if (isset($_GET['id'])) {
                render_user_details($_GET['id']);
                exit;
            }
            break;
        case 'test_details':
            if (isset($_GET['id'])) {
                render_test_details($_GET['id']);
                exit;
            }
            break;
        case 'add_lesson':
            render_add_lesson();
            exit;
        case 'edit_lesson':
            if (isset($_GET['id'])) {
                render_edit_lesson($_GET['id']);
                exit;
            }
            break;
        case 'manage_questions':
            if (isset($_GET['lesson_id'])) {
                render_manage_questions($_GET['lesson_id']);
                exit;
            }
            break;
        case 'admin_get_question':
            if (isset($_GET['id'])) {
                get_question_json($_GET['id']);
                exit;
            }
            break;
        // Inne podstrony panelu administratora...
    }
}
?>