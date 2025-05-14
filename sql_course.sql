CREATE DATABASE IF NOT EXISTS sql_course;
USE sql_course;

-- Tabela użytkowników
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nickname VARCHAR(50) UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    pass_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela lekcji
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(100) NOT NULL
);

-- Tabela pytań testowych
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT,
    type ENUM('single', 'multi', 'open') NOT NULL,
    text TEXT NOT NULL,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Tabela odpowiedzi
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Tabela wyników testów
CREATE TABLE user_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    lesson_id INT,
    score FLOAT DEFAULT 0,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Tabela odpowiedzi użytkownika
CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_test_id INT,
    question_id INT,
    answer_text TEXT,
    is_correct BOOLEAN DEFAULT FALSE,
    attempt_number INT DEFAULT 1,
    FOREIGN KEY (user_test_id) REFERENCES user_tests(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Przykładowe dane - admin
INSERT INTO users (email, pass_hash, role, nickname) VALUES 
('admin@example.com', '$2y$10$9bqmJu8OluFN1GAVr2TJYOBUSkBWnIVQq8UJnrz/4qEqiztT9j1eK', 'admin', 'admin'); -- hasło: admin123

-- Przykładowe lekcje
INSERT INTO lessons (title, content, category) VALUES 
('Wprowadzenie do SQL', 'SQL to język zapytań stosowany do komunikacji z bazami danych. W tej lekcji poznasz podstawowe polecenia.', 'Podstawy'),
('Polecenie SELECT', 'Polecenie SELECT służy do pobierania danych z bazy. Przykład: SELECT * FROM tabela;', 'DQL'),
('Polecenie INSERT', 'Polecenie INSERT służy do dodawania danych do tabeli. Przykład: INSERT INTO tabela (kolumna) VALUES (wartość);', 'DML'),
('Polecenie UPDATE', 'Polecenie UPDATE służy do aktualizacji danych. Przykład: UPDATE tabela SET kolumna = wartość WHERE warunek;', 'DML'),
('Polecenie DELETE', 'Polecenie DELETE służy do usuwania danych. Przykład: DELETE FROM tabela WHERE warunek;', 'DML');

-- Przykładowe pytania
INSERT INTO questions (lesson_id, type, text) VALUES
(2, 'single', 'Które polecenie służy do pobierania danych z bazy?'),
(2, 'open', 'Napisz zapytanie, które pobiera wszystkie dane z tabeli "users"'),
(3, 'multi', 'Które z poniższych są elementami polecenia INSERT?'),
(4, 'single', 'Które słowo kluczowe jest niezbędne w UPDATE, aby uniknąć aktualizacji wszystkich rekordów?'),
(5, 'open', 'Napisz zapytanie usuwające użytkownika o id=5 z tabeli "users"');

-- Przykładowe odpowiedzi
INSERT INTO answers (question_id, text, is_correct) VALUES
(1, 'SELECT', 1),
(1, 'INSERT', 0),
(1, 'UPDATE', 0),
(1, 'DELETE', 0),
(2, 'SELECT * FROM users;', 1),
(2, 'SELECT * FROM users', 1),
(3, 'INTO', 1),
(3, 'VALUES', 1),
(3, 'FROM', 0),
(3, 'WHERE', 0),
(4, 'WHERE', 1),
(4, 'SET', 0),
(4, 'FROM', 0),
(4, 'VALUES', 0),
(5, 'DELETE FROM users WHERE id=5;', 1),
(5, 'DELETE FROM users WHERE id=5', 1);

-- Dodaj indeksy
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_user_nickname ON users(nickname);
CREATE INDEX idx_lesson_category ON lessons(category);
CREATE INDEX idx_user_tests_user ON user_tests(user_id);
CREATE INDEX idx_user_tests_lesson ON user_tests(lesson_id);