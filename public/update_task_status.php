<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = (int)$_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        //verificar tasks
        $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed', updated_at = CURRENT_TIMESTAMP 
                              WHERE id = ? AND user_id = ? AND status = 'pending'");
        $result = $stmt->execute([$task_id, $user_id]);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao upar tarefa']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Request inválido']);
}
?>
