<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Konfiguration
$useDatabase = false; // true für Datenbank, false für LocalStorage
$host = 'localhost';
$dbname = 'todo_db';
$user = 'root';
$pass = '';

try {
    if ($useDatabase) {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Datenbankverbindung fehlgeschlagen']);
    exit;
}

// API-Logik
$method = $_SERVER['REQUEST_METHOD'];

if ($useDatabase) {
    switch ($method) {
        case 'GET': // Aufgaben abrufen
            $stmt = $pdo->query('SELECT id, task FROM tasks');
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tasks);
            break;

        case 'POST': // Aufgabe hinzufügen
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['task']) || empty(trim($data['task']))) {
                echo json_encode(['error' => 'Ungültige Aufgabe']);
                exit;
            }
            $task = htmlspecialchars($data['task'], ENT_QUOTES, 'UTF-8');
            $stmt = $pdo->prepare('INSERT INTO tasks (task) VALUES (:task)');
            $stmt->bindParam(':task', $task, PDO::PARAM_STR);
            $stmt->execute();
            echo json_encode(['message' => 'Aufgabe hinzugefügt']);
            break;

        case 'DELETE': // Aufgabe löschen
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id']) || !is_numeric($data['id'])) {
                echo json_encode(['error' => 'Ungültige ID']);
                exit;
            }
            $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['message' => 'Aufgabe gelöscht']);
            break;

        default:
            echo json_encode(['error' => 'Ungültige Anfrage']);
    }
} else {
    echo json_encode(['message' => 'Datenbank ist deaktiviert']);
}
?>
