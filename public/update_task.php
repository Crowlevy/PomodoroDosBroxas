<?php
session_start();
require_once '../includes/db_connect.php';

// array response
$response = [
    'success' => false,
    'message' => '',
    'redirect' => 'dashboard.php'
];

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Faça login para conseguir acessar";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($task_id === false || $task_id === null) {
        $_SESSION['error'] = "ID de tarefa inválido";
        header("Location: dashboard.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['error'] = "A tarefa não foi aceita ou o acesso foi negado";
            header("Location: dashboard.php");
            exit();
        }

        // update status
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET status = 'completed',
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? 
            AND user_id = ? 
            AND status = 'pending'
        ");
        
        if ($stmt->execute([$task_id, $user_id])) {
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Task marked as completed successfully!";
            } else {
                $_SESSION['info'] = "Task was already completed";
            }
        } else {
            $_SESSION['error'] = "Failed to update task status";
        }

    } catch (PDOException $e) {
        //problema de log no ambiente
        error_log("Erro ao upar tarefa: " . $e->getMessage());
        $_SESSION['error'] = "O erro está continuando ao upar a tarefa";
    }

} else {
    $_SESSION['error'] = "Método de request inválido";
}

header("Location: dashboard.php");
exit();
?>
