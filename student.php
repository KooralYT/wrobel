<?php
require_once 'config.php';
require_once 'functions.php';

// Sprawdź czy użytkownik jest zalogowany
check_login();

// Funkcja renderująca dashboard studenta
function render_dashboard() {
    global $db;
    $lessons = get_lessons($db);
    $user_tests = get_user_tests($db, $_SESSION['user_id']);
    
    // Grupuj lekcje według kategorii
    $lessons_by_category = [];
    foreach ($lessons as $lesson) {
        $category = $lesson['category'];
        if (!isset($lessons_by_category[$category])) {
            $lessons_by_category[$category] = [];
        }
        $lessons_by_category[$category][] = $lesson;
    }
    
    // Oblicz statystyki
    $total_tests = count($user_tests);
    $average_score = 0;
    if ($total_tests > 0) {
        $sum_score = array_sum(array_column($user_tests, 'score'));
        $average_score = $sum_score / $total_tests;
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Panel ucznia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel ucznia</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['nickname']) ?></span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard" class="active">Dashboard</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="index.php?page=admin">Panel Administratora</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <main>
            <div class="dashboard">
                <section class="stats">
                    <h2>Twoje statystyki</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Ukończone testy</h3>
                            <p class="stat-value"><?= $total_tests ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Średni wynik</h3>
                            <p class="stat-value"><?= number_format($average_score, 1) ?>%</p>
                        </div>
                    </div>
                </section>
                
                <section class="lessons">
                    <h2>Dostępne lekcje</h2>
                    <?php foreach ($lessons_by_category as $category => $category_lessons): ?>
                    <div class="category">
                        <h3><?= htmlspecialchars($category) ?></h3>
                        <div class="lessons-grid">
                            <?php foreach ($category_lessons as $lesson): ?>
                            <div class="lesson-card">
                                <h4><?= htmlspecialchars($lesson['title']) ?></h4>
                                <div class="lesson-actions">
                                    <a href="index.php?page=lesson&id=<?= $lesson['id'] ?>" class="btn btn-primary">Ucz się</a>
                                    <a href="index.php?page=test&id=<?= $lesson['id'] ?>" class="btn btn-secondary">Test</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </section>
                
                <section class="recent-tests">
                    <h2>Ostatnie testy</h2>
                    <?php if (empty($user_tests)): ?>
                    <p>Nie rozwiązałeś jeszcze żadnych testów.</p>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Lekcja</th>
                                <th>Kategoria</th>
                                <th>Wynik</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($user_tests, 0, 5) as $test): ?>
                            <tr>
                                <td><?= htmlspecialchars($test['title']) ?></td>
                                <td><?= htmlspecialchars($test['category']) ?></td>
                                <td><?= number_format($test['score'], 1) ?>%</td>
                                <td><?= date('d.m.Y H:i', strtotime($test['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </section>
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

// Funkcja renderująca lekcję
function render_lesson($lesson_id) {
    global $db;
    $lesson = get_lesson($db, $lesson_id);
    
    if (!$lesson) {
        redirect('dashboard');
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - <?= htmlspecialchars($lesson['title']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Panel ucznia</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['nickname']) ?></span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="index.php?page=admin">Panel Administratora</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <main>
            <div class="lesson-container">
                <div class="lesson-header">
                    <h2><?= htmlspecialchars($lesson['title']) ?></h2>
                    <span class="category-badge"><?= htmlspecialchars($lesson['category']) ?></span>
                </div>
                
                <div class="lesson-content">
                    <?= nl2br(htmlspecialchars($lesson['content'])) ?>
                </div>
                
                <div class="lesson-actions">
                    <a href="index.php?page=dashboard" class="btn">Powrót do listy</a>
                    <a href="index.php?page=test&id=<?= $lesson['id'] ?>" class="btn btn-primary">Rozwiąż test</a>
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

// Funkcja renderująca test
function render_test($lesson_id) {
    global $db;
    $lesson = get_lesson($db, $lesson_id);
    
    if (!$lesson) {
        redirect('dashboard');
    }
    
    $questions = get_questions_for_lesson($db, $lesson_id);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - Test: <?= htmlspecialchars($lesson['title']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .code-editor {
            font-family: monospace;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 10px;
            border-radius: 4px;
            border: none;
            width: 100%;
        }
        
        .sql-keywords {
            color: #3498db;
            font-weight: bold;
        }
        
        .sql-hint {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.9em;
        }
        
        .sql-example {
            background-color: #f0f0f0;
            padding: 8px;
            border-radius: 4px;
            font-family: monospace;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - Test</h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['nickname']) ?></span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="index.php?page=admin">Panel Administratora</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <main>
            <div class="test-container">
                <div class="test-header">
                    <h2>Test: <?= htmlspecialchars($lesson['title']) ?></h2>
                    <span class="category-badge"><?= htmlspecialchars($lesson['category']) ?></span>
                </div>
                
                <?php if (empty($questions)): ?>
                <p>Brak pytań dla tej lekcji.</p>
                <a href="index.php?page=dashboard" class="btn">Powrót do listy</a>
                
                <?php else: ?>
                <form method="post" action="index.php?page=test_result">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
                    
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card">
                        <h3>Pytanie <?= $index + 1 ?>:</h3>
                        <p class="question-text"><?= htmlspecialchars($question['text']) ?></p>
                        
                        <?php
                        // Pobierz odpowiedzi
                        $answers = get_answers_for_question($db, $question['id']);
                        
                        // Wyświetl odpowiednie pola w zależności od typu pytania
                        if ($question['type'] === 'open'):
                        ?>
                            <div class="form-group">
                                <label for="question_<?= $question['id'] ?>">Twoja odpowiedź SQL:</label>
                                <textarea name="answers[<?= $question['id'] ?>]" id="question_<?= $question['id'] ?>" 
                                          class="code-editor" required rows="3"
                                          placeholder="Wpisz swoje zapytanie SQL tutaj..."></textarea>
                                <small>Maksymalnie <?= MAX_ATTEMPTS_OPEN ?> próby.</small>
                                
                                <div class="sql-hint">
                                    <strong>Wskazówka:</strong> Pamiętaj o poprawnej składni SQL. 
                                    Zwróć uwagę na słowa kluczowe jak <span class="sql-keywords">SELECT</span>, 
                                    <span class="sql-keywords">FROM</span>, <span class="sql-keywords">WHERE</span> itd.
                                </div>
                            </div>
                        <?php elseif ($question['type'] === 'single'): ?>
                            <div class="form-group">
                                <?php foreach ($answers as $answer): ?>
                                <div class="radio-option">
                                    <input type="radio" name="answers[<?= $question['id'] ?>]" id="answer_<?= $answer['id'] ?>" value="<?= $answer['id'] ?>" required>
                                    <label for="answer_<?= $answer['id'] ?>"><?= htmlspecialchars($answer['text']) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($question['type'] === 'multi'): ?>
                            <div class="form-group">
                                <?php foreach ($answers as $answer): ?>
                                <div class="checkbox-option">
                                    <input type="checkbox" name="answers[<?= $question['id'] ?>][]" id="answer_<?= $answer['id'] ?>" value="<?= $answer['id'] ?>">
                                    <label for="answer_<?= $answer['id'] ?>"><?= htmlspecialchars($answer['text']) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <a href="index.php?page=lesson&id=<?= $lesson_id ?>" class="btn">Powrót do lekcji</a>
                        <button type="submit" class="btn btn-primary">Zakończ test</button>
                    </div>
                </form>
                <?php endif; ?>
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

// Funkcja renderująca wyniki testu - zaktualizowana o obsługę wielu prób dla pytań otwartych
function render_test_result() {
    global $db;
    check_login();
    
    if (!isset($_POST['lesson_id']) && !isset($_SESSION['ongoing_test'])) {
        redirect('dashboard');
    }
    
    // Sprawdź czy to nowy test czy kontynuacja
    if (isset($_POST['lesson_id'])) {
        // Nowy test lub kolejna próba
        if (!verify_csrf_token($_POST['csrf_token'])) {
            redirect('dashboard');
        }
        
        $lesson_id = (int)$_POST['lesson_id'];
        $lesson = get_lesson($db, $lesson_id);
        
        if (!$lesson) {
            redirect('dashboard');
        }
        
        $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
        $result = check_test_result($db, $_SESSION['user_id'], $lesson_id, $answers);
    } else {
        // Pokaż aktualne wyniki trwającego testu
        $ongoing_test = $_SESSION['ongoing_test'];
        $lesson_id = $ongoing_test['lesson_id'];
        $lesson = get_lesson($db, $lesson_id);
        $result = [
            'test_id' => $ongoing_test['test_id'],
            'score' => $ongoing_test['score'],
            'total' => $ongoing_test['total_questions'],
            'correct' => $ongoing_test['correct_answers'],
            'completed' => false,
            'incorrect_open_questions' => $ongoing_test['incorrect_open_questions'],
            'incorrect_closed_questions' => $ongoing_test['incorrect_closed_questions']
        ];
    }
    
    // Pobierz szczegóły odpowiedzi tylko dla zakończonego testu
    if ($result['completed']) {
        $stmt = $db->prepare("
            SELECT ua.*, q.text as question_text, q.type as question_type
            FROM user_answers ua
            JOIN questions q ON ua.question_id = q.id
            WHERE ua.user_test_id = ?
        ");
        $stmt->execute([$result['test_id']]);
        $answers_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs SQL - <?= $result['completed'] ? 'Wyniki testu' : 'Test w toku' ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .open-question-form, .closed-question-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .open-question-form {
            border-left: 4px solid #3498db;
        }
        
        .closed-question-form {
            border-left: 4px solid #9b59b6;
        }
        
        .attempts-info {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .hint {
            background-color: #f6e7d2;
            border-left: 4px solid #f39c12;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.9em;
        }
        
        .radio-option, .checkbox-option {
            padding: 10px;
            margin: 5px 0;
            background-color: #fff;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .radio-option:hover, .checkbox-option:hover {
            background-color: #ecf0f1;
        }
        
        .radio-option input[type="radio"], .checkbox-option input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kurs SQL - <?= $result['completed'] ? 'Wyniki testu' : 'Test w toku' ?></h1>
            <div class="user-info">
                <span>Witaj, <?= htmlspecialchars($_SESSION['nickname']) ?></span>
                <a href="index.php?action=logout" class="btn btn-sm">Wyloguj</a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php?page=dashboard">Dashboard</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="index.php?page=admin">Panel Administratora</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <main>
            <?php if (!$result['completed']): ?>
                <!-- Test w toku - pytania otwarte wymagają ponownego podejścia -->
                <div class="result-container">
                    <div class="result-header">
                        <h2>Test w toku: <?= htmlspecialchars($lesson['title']) ?></h2>
                        <span class="category-badge"><?= htmlspecialchars($lesson['category']) ?></span>
                    </div>
                    
                    <div class="result-summary">
                        <div class="result-score">
                            <h3>Twój aktualny wynik:</h3>
                            <p class="score"><?= number_format($result['score'], 1) ?>%</p>
                            <p class="score-details">
                                Poprawne odpowiedzi: <?= $result['correct'] ?> / <?= $result['total'] ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="open-questions">
                        <h3>Pytania wymagające poprawy:</h3>
                        <p>Popraw odpowiedzi na poniższe pytania otwarte:</p>
                        
                        <form method="post" action="index.php?page=test_result">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
                            
                            <?php foreach ($result['incorrect_open_questions'] as $question): ?>
                            <div class="open-question-form">
                                <h4>Pytanie:</h4>
                                <p class="question-text"><?= htmlspecialchars($question['question_text']) ?></p>
                                
                                <p class="error-message">Twoja odpowiedź była niepoprawna. Spróbuj ponownie.</p>
                                
                                <?php if ($question['attempts'] >= 1): ?>
                                <div class="hint">
                                    <strong>Wskazówka:</strong> Sprawdź poprawność składni SQL. Upewnij się, że używasz odpowiednich słów kluczowych.
                                    <?php if ($question['attempts'] >= 2): ?>
                                    <br>Zwróć uwagę na interpunkcję (przecinki, średniki) i nazwy kolumn/tabel.
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label for="question_<?= $question['question_id'] ?>">Twoja nowa odpowiedź:</label>
                                    <textarea name="answers[<?= $question['question_id'] ?>]" 
                                              id="question_<?= $question['question_id'] ?>" 
                                              required rows="3"><?= htmlspecialchars($question['user_answer']) ?></textarea>
                                    <p class="attempts-info">
                                        Próba <?= $question['attempts'] + 1 ?> z <?= MAX_ATTEMPTS_OPEN ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (!empty($result['incorrect_closed_questions'])): ?>
                            <div class="closed-questions">
                                <h3>Pytania zamknięte wymagające poprawy:</h3>
                                <p>Popraw odpowiedzi na poniższe pytania:</p>
                                
                                <?php foreach ($result['incorrect_closed_questions'] as $question): ?>
                                <div class="closed-question-form">
                                    <h4>Pytanie:</h4>
                                    <p class="question-text"><?= htmlspecialchars($question['question_text']) ?></p>
                                    
                                    <p class="error-message">Twoja odpowiedź była niepoprawna. Spróbuj ponownie.</p>
                                    
                                    <div class="form-group">
                                        <?php if ($question['question_type'] === 'single'): ?>
                                            <label>Wybierz prawidłową odpowiedź:</label>
                                            <?php foreach ($question['all_answers'] as $answer): ?>
                                            <div class="radio-option">
                                                <input type="radio" name="answers[<?= $question['question_id'] ?>]" 
                                                    id="answer_<?= $question['question_id'] ?>_<?= $answer['id'] ?>" 
                                                    value="<?= $answer['id'] ?>" 
                                                    <?= $question['user_answer'] == $answer['id'] ? 'checked' : '' ?> 
                                                    required>
                                                <label for="answer_<?= $question['question_id'] ?>_<?= $answer['id'] ?>">
                                                    <?= htmlspecialchars($answer['text']) ?>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php elseif ($question['question_type'] === 'multi'): ?>
                                            <label>Wybierz wszystkie prawidłowe odpowiedzi:</label>
                                            <?php foreach ($question['all_answers'] as $answer): ?>
                                            <div class="checkbox-option">
                                                <input type="checkbox" name="answers[<?= $question['question_id'] ?>][]" 
                                                    id="answer_<?= $question['question_id'] ?>_<?= $answer['id'] ?>" 
                                                    value="<?= $answer['id'] ?>"
                                                    <?= is_array($question['user_answer']) && in_array($answer['id'], $question['user_answer']) ? 'checked' : '' ?>>
                                                <label for="answer_<?= $question['question_id'] ?>_<?= $answer['id'] ?>">
                                                    <?= htmlspecialchars($answer['text']) ?>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <p class="attempts-info">
                                            Próba <?= $question['attempts'] + 1 ?> z <?= MAX_ATTEMPTS_CLOSED ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Wyślij poprawione odpowiedzi</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Test zakończony - finalne wyniki -->
                <div class="result-container">
                    <div class="result-header">
                        <h2>Wyniki testu: <?= htmlspecialchars($lesson['title']) ?></h2>
                        <span class="category-badge"><?= htmlspecialchars($lesson['category']) ?></span>
                    </div>
                    
                    <div class="result-summary">
                        <div class="result-score">
                            <h3>Twój wynik:</h3>
                            <p class="score"><?= number_format($result['score'], 1) ?>%</p>
                            <p class="score-details">
                                Poprawne odpowiedzi: <?= $result['correct'] ?> / <?= $result['total'] ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="result-details">
                        <h3>Szczegóły odpowiedzi:</h3>
                        <?php foreach ($answers_details as $index => $answer): ?>
                        <div class="answer-card <?= $answer['is_correct'] ? 'correct' : 'incorrect' ?>">
                            <h4>Pytanie <?= $index + 1 ?>:</h4>
                            <p class="question-text"><?= htmlspecialchars($answer['question_text']) ?></p>
                            
                            <div class="answer-details">
                                <?php if ($answer['question_type'] === 'open'): ?>
                                    <p>Twoja odpowiedź: <code><?= htmlspecialchars($answer['answer_text']) ?></code></p>
                                <?php elseif ($answer['question_type'] === 'single'): ?>
                                    <?php
                                        $answer_id = (int)$answer['answer_text'];
                                        $stmt = $db->prepare("SELECT text FROM answers WHERE id = ?");
                                        $stmt->execute([$answer_id]);
                                        $answer_text = $stmt->fetchColumn();
                                    ?>
                                    <p>Twoja odpowiedź: <?= htmlspecialchars($answer_text) ?></p>
                                <?php elseif ($answer['question_type'] === 'multi'): ?>
                                    <?php
                                        $answer_ids = explode(',', $answer['answer_text']);
                                        $answer_texts = [];
                                        
                                        foreach ($answer_ids as $id) {
                                            $stmt = $db->prepare("SELECT text FROM answers WHERE id = ?");
                                            $stmt->execute([$id]);
                                            $answer_texts[] = $stmt->fetchColumn();
                                        }
                                    ?>
                                    <p>Twoje odpowiedzi:</p>
                                    <ul>
                                        <?php foreach ($answer_texts as $text): ?>
                                        <li><?= htmlspecialchars($text) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <p class="answer-status">
                                    <?php if ($answer['is_correct']): ?>
                                    <span class="status-correct">Poprawna</span>
                                    <?php else: ?>
                                    <span class="status-incorrect">Niepoprawna</span>
                                    
                                    <?php 
                                    // Pokaż poprawną odpowiedź dla pytań otwartych
                                    if ($answer['question_type'] === 'open') {
                                        $stmt = $db->prepare("
                                            SELECT text FROM answers 
                                            WHERE question_id = ? AND is_correct = 1
                                            LIMIT 1
                                        ");
                                        $stmt->execute([$answer['question_id']]);
                                        $correct_text = $stmt->fetchColumn();
                                        if ($correct_text) {
                                            echo '<p>Poprawna odpowiedź: <code>' . htmlspecialchars($correct_text) . '</code></p>';
                                        }
                                    }
                                    ?>
                                    
                                    <?php endif; ?>
                                </p>
                                
                                <?php if ($answer['attempt_number'] > 1): ?>
                                <p class="attempts-info">
                                    Liczba prób: <?= $answer['attempt_number'] ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="result-actions">
                        <a href="index.php?page=dashboard" class="btn">Powrót do dashboardu</a>
                        <a href="index.php?page=lesson&id=<?= $lesson_id ?>" class="btn btn-secondary">Powtórz lekcję</a>
                        <a href="index.php?page=test&id=<?= $lesson_id ?>" class="btn btn-primary">Spróbuj ponownie</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; 2025 Kurs SQL. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>
<?php
}
?>