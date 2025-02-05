<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Request inválido']);
    exit();
}

$task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$task_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de tarefa inválida']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, pomodoros_needed, completed_pomodoros FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Task not found or access denied']);
        exit();
    }

    //pomodoros completos
    $completed_pomodoros = min($task['completed_pomodoros'] + 1, $task['pomodoros_needed']);
    
    //update tarefa
    $stmt = $pdo->prepare("UPDATE tasks SET completed_pomodoros = ? WHERE id = ?");
    $stmt->execute([$completed_pomodoros, $task_id]);

    //calculo da taxa em porcentagem
    $percentage = ($completed_pomodoros * 100) / $task['pomodoros_needed'];
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'completed_pomodoros' => $completed_pomodoros,
        'total_pomodoros' => $task['pomodoros_needed'],
        'percentage' => $percentage,
        'is_complete' => $completed_pomodoros >= $task['pomodoros_needed']
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro no banco de dados']);
    exit();
}
