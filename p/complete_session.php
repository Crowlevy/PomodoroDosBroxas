<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faça login para completar a ação']);
    exit();
}

// req que pega a última hora de login do usuário -> implementar talvez por cookies, seria bem mais prático
$_SESSION['last_activity'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$task_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit();
    }

    try {
        // verificação de tasks, analisando quais estão pendentes e completas
        $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$task_id, $user_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'A tarefa não foi completada ou não existe']);
            exit();
        }

        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET status = 'completed',
                completed_at = NOW()
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        
        if ($stmt->execute([$task_id, $user_id])) {
            echo json_encode(['success' => true, 'message' => 'Task completed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update task']);
        }

    } catch (PDOException $e) {
        error_log("Erro ao tentar completar a tarefa: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database erro eba: ' . $e->getMessage()]);
    }
}
?>
