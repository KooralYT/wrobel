<?php
// Funkcja zabezpieczająca dane wejściowe
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generowanie tokenu CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Weryfikacja tokenu CSRF
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Pobieranie lekcji
function get_lessons($db) {
    $stmt = $db->query("SELECT * FROM lessons ORDER BY category, id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pobieranie jednej lekcji
function get_lesson($db, $id) {
    $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pobieranie pytań do lekcji
function get_questions_for_lesson($db, $lesson_id) {
    $stmt = $db->prepare("SELECT * FROM questions WHERE lesson_id = ?");
    $stmt->execute([$lesson_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pobieranie odpowiedzi do pytania
function get_answers_for_question($db, $question_id) {
    $stmt = $db->prepare("SELECT * FROM answers WHERE question_id = ?");
    $stmt->execute([$question_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funkcja sprawdzająca wynik testu - z obsługą wielu prób dla pytań otwartych
function check_test_result($db, $user_id, $lesson_id, $answers) {
    // Sprawdź czy istnieje już trwający test z pytaniami otwartymi
    $ongoing_test = get_ongoing_test($db, $user_id, $lesson_id);
    
    if ($ongoing_test) {
        // Kontynuuj istniejący test z pytaniami otwartymi
        return process_ongoing_test($db, $ongoing_test, $answers);
    } else {
        // Nowy test
        return process_new_test($db, $user_id, $lesson_id, $answers);
    }
}

// Funkcja pobierająca aktywny test z pytaniami otwartymi
function get_ongoing_test($db, $user_id, $lesson_id) {
    // Sprawdź sesję dla trwającego testu
    if (isset($_SESSION['ongoing_test']) && 
        $_SESSION['ongoing_test']['user_id'] == $user_id && 
        $_SESSION['ongoing_test']['lesson_id'] == $lesson_id) {
        return $_SESSION['ongoing_test'];
    }
    
    return null;
}

// Funkcja przetwarzająca nowy test - zmodyfikowana
function process_new_test($db, $user_id, $lesson_id, $submitted_answers) {
    // Pobierz wszystkie pytania dla lekcji
    $questions = get_questions_for_lesson($db, $lesson_id);
    
    // Tworzenie testu w bazie danych
    $stmt = $db->prepare("
        INSERT INTO user_tests (user_id, lesson_id, score, attempts)
        VALUES (?, ?, 0, 1)
    ");
    $stmt->execute([$user_id, $lesson_id]);
    $test_id = $db->lastInsertId();
    
    $total_questions = count($questions);
    $correct_answers = 0;
    $incorrect_open_questions = [];
    $incorrect_closed_questions = [];
    
    // Sprawdzanie każdej odpowiedzi
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $is_correct = false;
        $answer_text = '';
        
        // Sprawdź czy użytkownik odpowiedział na to pytanie
        if (isset($submitted_answers[$question_id])) {
            if ($question['type'] === 'open') {
                // Pytanie otwarte
                $user_answer = trim($submitted_answers[$question_id]);
                $answer_text = $user_answer;
                
                // Sprawdź poprawność odpowiedzi
                $stmt = $db->prepare("
                    SELECT * FROM answers 
                    WHERE question_id = ? AND is_correct = 1
                ");
                $stmt->execute([$question_id]);
                $correct_answers_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($correct_answers_db as $correct_answer) {
                    if (compare_open_answers($user_answer, $correct_answer['text'])) {
                        $is_correct = true;
                        break;
                    }
                }
                
                if (!$is_correct) {
                    // Zapamiętaj niepoprawne pytanie otwarte
                    $incorrect_open_questions[] = [
                        'question_id' => $question_id,
                        'question_text' => $question['text'],
                        'user_answer' => $user_answer,
                        'attempts' => 1
                    ];
                }
            } elseif ($question['type'] === 'single') {
                // Pytanie jednokrotnego wyboru
                $answer_id = (int)$submitted_answers[$question_id];
                $answer_text = $answer_id;
                
                // Pobierz wszystkie możliwe odpowiedzi dla wizualizacji
                $stmt = $db->prepare("SELECT id, text, is_correct FROM answers WHERE question_id = ?");
                $stmt->execute([$question_id]);
                $all_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Sprawdź czy wybrana odpowiedź jest poprawna
                $stmt = $db->prepare("SELECT is_correct FROM answers WHERE id = ?");
                $stmt->execute([$answer_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && $result['is_correct']) {
                    $is_correct = true;
                } else {
                    // Zapamiętaj niepoprawne pytanie zamknięte
                    $incorrect_closed_questions[] = [
                        'question_id' => $question_id,
                        'question_text' => $question['text'],
                        'question_type' => 'single',
                        'user_answer' => $answer_id,
                        'all_answers' => $all_answers,
                        'attempts' => 1
                    ];
                }
            } elseif ($question['type'] === 'multi') {
                // Pytanie wielokrotnego wyboru
                $selected_answers = is_array($submitted_answers[$question_id]) ? 
                    $submitted_answers[$question_id] : [];
                
                // Konwertuj na string dla zapisania w bazie
                $answer_text = implode(',', $selected_answers);
                
                // Pobierz wszystkie odpowiedzi
                $stmt = $db->prepare("SELECT id, text, is_correct FROM answers WHERE question_id = ?");
                $stmt->execute([$question_id]);
                $all_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Sprawdź czy wszystkie zaznaczone są poprawne i czy wszystkie poprawne są zaznaczone
                $all_correct = true;
                foreach ($all_answers as $answer) {
                    $is_selected = in_array($answer['id'], $selected_answers);
                    
                    if (($answer['is_correct'] && !$is_selected) || 
                        (!$answer['is_correct'] && $is_selected)) {
                        $all_correct = false;
                        break;
                    }
                }
                
                $is_correct = $all_correct;
                
                if (!$is_correct) {
                    // Zapamiętaj niepoprawne pytanie wielokrotnego wyboru
                    $incorrect_closed_questions[] = [
                        'question_id' => $question_id,
                        'question_text' => $question['text'],
                        'question_type' => 'multi',
                        'user_answer' => $selected_answers,
                        'all_answers' => $all_answers,
                        'attempts' => 1
                    ];
                }
            }
            
            // Zapisz odpowiedź użytkownika
            $stmt = $db->prepare("
                INSERT INTO user_answers (user_test_id, question_id, answer_text, is_correct, attempt_number)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$test_id, $question_id, $answer_text, $is_correct ? 1 : 0]);
            
            if ($is_correct) {
                $correct_answers++;
            }
        }
    }
    
    // Oblicz wynik testu
    $score = ($total_questions > 0) ? ($correct_answers / $total_questions) * 100 : 0;
    
    // Zaktualizuj wynik testu w bazie
    $stmt = $db->prepare("UPDATE user_tests SET score = ? WHERE id = ?");
    $stmt->execute([$score, $test_id]);
    
    // Jeśli są niepoprawne pytania, zapisz stan testu w sesji
    if (!empty($incorrect_open_questions) || !empty($incorrect_closed_questions)) {
        $_SESSION['ongoing_test'] = [
            'test_id' => $test_id,
            'user_id' => $user_id,
            'lesson_id' => $lesson_id,
            'total_questions' => $total_questions,
            'correct_answers' => $correct_answers,
            'incorrect_open_questions' => $incorrect_open_questions,
            'incorrect_closed_questions' => $incorrect_closed_questions,
            'score' => $score
        ];
        
        // Zwróć informację o niekompletnym teście
        return [
            'test_id' => $test_id,
            'score' => $score,
            'total' => $total_questions,
            'correct' => $correct_answers,
            'completed' => false,
            'incorrect_open_questions' => $incorrect_open_questions,
            'incorrect_closed_questions' => $incorrect_closed_questions
        ];
    }
    
    // Zwróć wynik kompletnego testu
    return [
        'test_id' => $test_id,
        'score' => $score,
        'total' => $total_questions,
        'correct' => $correct_answers,
        'completed' => true
    ];
}

// Funkcja przetwarzająca aktywny test (drugie i kolejne podejścia)
function process_ongoing_test($db, $ongoing_test, $submitted_answers) {
    $test_id = $ongoing_test['test_id'];
    $incorrect_open_questions = [];
    $incorrect_closed_questions = [];
    $correct_answers = $ongoing_test['correct_answers'];
    $max_attempts_open = MAX_ATTEMPTS_OPEN;
    $max_attempts_closed = MAX_ATTEMPTS_CLOSED;
    
    // Przetwarzanie pytań otwartych
    if (isset($ongoing_test['incorrect_open_questions'])) {
        foreach ($ongoing_test['incorrect_open_questions'] as $open_question) {
            $question_id = $open_question['question_id'];
            $current_attempts = $open_question['attempts'];
            $is_correct = false;
            
            // Sprawdź czy użytkownik odpowiedział na to pytanie
            if (isset($submitted_answers[$question_id])) {
                $user_answer = trim($submitted_answers[$question_id]);
                
                // Sprawdź poprawność odpowiedzi
                $stmt = $db->prepare("
                    SELECT * FROM answers 
                    WHERE question_id = ? AND is_correct = 1
                ");
                $stmt->execute([$question_id]);
                $correct_answers_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($correct_answers_db as $correct_answer) {
                    if (compare_open_answers($user_answer, $correct_answer['text'])) {
                        $is_correct = true;
                        break;
                    }
                }
                
                // Zapisz odpowiedź użytkownika
                $stmt = $db->prepare("
                    INSERT INTO user_answers (user_test_id, question_id, answer_text, is_correct, attempt_number)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$test_id, $question_id, $user_answer, $is_correct ? 1 : 0, $current_attempts + 1]);
                
                // Zwiększamy liczbę poprawnych odpowiedzi, jeśli udało się
                if ($is_correct) {
                    $correct_answers++;
                } else if ($current_attempts + 1 < $max_attempts_open) {
                    // Jeśli nadal nie poprawna i mamy jeszcze próby
                    $incorrect_open_questions[] = [
                        'question_id' => $question_id,
                        'question_text' => $open_question['question_text'],
                        'user_answer' => $user_answer,
                        'attempts' => $current_attempts + 1
                    ];
                }
            }
        }
    }
    
    // Przetwarzanie pytań zamkniętych
    if (isset($ongoing_test['incorrect_closed_questions'])) {
        foreach ($ongoing_test['incorrect_closed_questions'] as $closed_question) {
            $question_id = $closed_question['question_id'];
            $question_type = $closed_question['question_type'];
            $current_attempts = $closed_question['attempts'];
            $all_answers = $closed_question['all_answers'];
            $is_correct = false;
            
            // Sprawdź czy użytkownik odpowiedział na to pytanie
            if (isset($submitted_answers[$question_id])) {
                if ($question_type === 'single') {
                    // Pytanie jednokrotnego wyboru
                    $answer_id = (int)$submitted_answers[$question_id];
                    
                    // Sprawdź czy wybrana odpowiedź jest poprawna
                    $stmt = $db->prepare("SELECT is_correct FROM answers WHERE id = ?");
                    $stmt->execute([$answer_id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result && $result['is_correct']) {
                        $is_correct = true;
                    }
                    
                    // Zapisz odpowiedź
                    $stmt = $db->prepare("
                        INSERT INTO user_answers (user_test_id, question_id, answer_text, is_correct, attempt_number)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$test_id, $question_id, $answer_id, $is_correct ? 1 : 0, $current_attempts + 1]);
                    
                } elseif ($question_type === 'multi') {
                    // Pytanie wielokrotnego wyboru
                    $selected_answers = is_array($submitted_answers[$question_id]) ? 
                        $submitted_answers[$question_id] : [];
                    
                    // Konwertuj na string dla zapisania w bazie
                    $answer_text = implode(',', $selected_answers);
                    
                    // Sprawdź czy wszystkie zaznaczone są poprawne i czy wszystkie poprawne są zaznaczone
                    $all_correct = true;
                    foreach ($all_answers as $answer) {
                        $is_selected = in_array($answer['id'], $selected_answers);
                        
                        if (($answer['is_correct'] && !$is_selected) || 
                            (!$answer['is_correct'] && $is_selected)) {
                            $all_correct = false;
                            break;
                        }
                    }
                    
                    $is_correct = $all_correct;
                    
                    // Zapisz odpowiedź
                    $stmt = $db->prepare("
                        INSERT INTO user_answers (user_test_id, question_id, answer_text, is_correct, attempt_number)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$test_id, $question_id, $answer_text, $is_correct ? 1 : 0, $current_attempts + 1]);
                }
                
                // Zwiększamy liczbę poprawnych odpowiedzi, jeśli udało się
                if ($is_correct) {
                    $correct_answers++;
                } else if ($current_attempts + 1 < $max_attempts_closed) {
                    // Jeśli nadal nie poprawna i mamy jeszcze próby
                    $incorrect_closed_questions[] = [
                        'question_id' => $question_id,
                        'question_text' => $closed_question['question_text'],
                        'question_type' => $question_type,
                        'user_answer' => $question_type === 'single' ? (int)$submitted_answers[$question_id] : $submitted_answers[$question_id],
                        'all_answers' => $all_answers,
                        'attempts' => $current_attempts + 1
                    ];
                }
            }
        }
    }
    
    // Oblicz nowy wynik testu
    $score = ($ongoing_test['total_questions'] > 0) ? 
        ($correct_answers / $ongoing_test['total_questions']) * 100 : 0;
    
    // Zaktualizuj wynik testu w bazie
    $stmt = $db->prepare("UPDATE user_tests SET score = ?, attempts = attempts + 1 WHERE id = ?");
    $stmt->execute([$score, $test_id]);
    
    // Jeśli są nadal niepoprawne pytania z dostępnymi próbami, zaktualizuj sesję
    if (!empty($incorrect_open_questions) || !empty($incorrect_closed_questions)) {
        $_SESSION['ongoing_test'] = [
            'test_id' => $test_id,
            'user_id' => $ongoing_test['user_id'],
            'lesson_id' => $ongoing_test['lesson_id'],
            'total_questions' => $ongoing_test['total_questions'],
            'correct_answers' => $correct_answers,
            'incorrect_open_questions' => $incorrect_open_questions,
            'incorrect_closed_questions' => $incorrect_closed_questions,
            'score' => $score
        ];
        
        // Zwróć informację o niekompletnym teście
        return [
            'test_id' => $test_id,
            'score' => $score,
            'total' => $ongoing_test['total_questions'],
            'correct' => $correct_answers,
            'completed' => false,
            'incorrect_open_questions' => $incorrect_open_questions,
            'incorrect_closed_questions' => $incorrect_closed_questions
        ];
    }
    
    // Jeśli wszystkie próby wykorzystane lub wszystkie odpowiedzi poprawne, kończymy test
    unset($_SESSION['ongoing_test']);
    
    // Zwróć wynik kompletnego testu
    return [
        'test_id' => $test_id,
        'score' => $score,
        'total' => $ongoing_test['total_questions'],
        'correct' => $correct_answers,
        'completed' => true
    ];
}

// Funkcja pobierająca pytania i odpowiedzi dla pytań otwartych
function get_open_question_with_answers($db, $question_id) {
    // Pobierz pytanie
    $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$question) {
        return null;
    }
    
    // Pobierz odpowiedzi
    $stmt = $db->prepare("SELECT * FROM answers WHERE question_id = ? AND is_correct = 1");
    $stmt->execute([$question_id]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'question' => $question,
        'answers' => $answers
    ];
}

// Pobieranie pojedynczego pytania
function get_question($db, $question_id) {
    $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pobieranie wyników testów użytkownika
function get_user_tests($db, $user_id) {
    $stmt = $db->prepare("
        SELECT ut.*, l.title, l.category
        FROM user_tests ut
        JOIN lessons l ON ut.lesson_id = l.id
        WHERE ut.user_id = ?
        ORDER BY ut.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pobieranie użytkowników (dla admina)
function get_all_users($db) {
    $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Dodatkowa funkcja do poprawy sprawdzania pytań otwartych

// Ulepszona wersja porównania odpowiedzi na pytanie otwarte
function compare_open_answers($user_answer, $correct_answer) {
    // Standardowe oczyszczenie
    $user_answer = strtolower(trim($user_answer));
    $correct_answer = strtolower(trim($correct_answer));
    
    // Usuń końcowy średnik (opcjonalny w SQL)
    $user_answer = rtrim($user_answer, ';');
    $correct_answer = rtrim($correct_answer, ';');
    
    // Usuń nadmiarowe spacje
    $user_answer = preg_replace('/\s+/', ' ', $user_answer);
    $correct_answer = preg_replace('/\s+/', ' ', $correct_answer);
    
    // Bezpośrednie porównanie
    if ($user_answer === $correct_answer) {
        return true;
    }
    
    // Porównanie z ignorowaniem kolejności atrybutów w niektórych zapytaniach
    // Na przykład: "SELECT id, name FROM users" vs "SELECT name, id FROM users"
    if (stripos($user_answer, 'select') === 0 && stripos($correct_answer, 'select') === 0) {
        // Rozbij na części: SELECT [columns] FROM [table] [rest]
        $pattern = '/^select\s+(.*?)\s+from\s+(.*?)(?:\s+(.*))?$/i';
        
        if (preg_match($pattern, $user_answer, $user_matches) && 
            preg_match($pattern, $correct_answer, $correct_matches)) {
            
            // Sprawdź czy tabele są takie same
            if (trim($user_matches[2]) === trim($correct_matches[2])) {
                
                // Porównaj kolumny niezależnie od kolejności
                $user_columns = array_map('trim', explode(',', $user_matches[1]));
                $correct_columns = array_map('trim', explode(',', $correct_matches[1]));
                
                sort($user_columns);
                sort($correct_columns);
                
                if ($user_columns === $correct_columns) {
                    // Sprawdź czy reszta (WHERE, ORDER BY, itp.) jest taka sama
                    $user_rest = isset($user_matches[3]) ? trim($user_matches[3]) : '';
                    $correct_rest = isset($correct_matches[3]) ? trim($correct_matches[3]) : '';
                    
                    if ($user_rest === $correct_rest) {
                        return true;
                    }
                }
            }
        }
    }
    
    return false;
}
?>