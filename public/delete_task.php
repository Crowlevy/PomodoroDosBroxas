<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to perform this action']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($task_id === false || $task_id === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$task_id, $user_id])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Task deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Task not found or access denied']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
        }
    } catch (PDOException $e) {
        error_log("Error deleting task: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
