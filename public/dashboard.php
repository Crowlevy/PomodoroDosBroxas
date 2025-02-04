<?php
session_start();
require_once '../includes/db_connect.php';

// check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/header.php';

$username = htmlspecialchars($_SESSION['username']);
$user_id = $_SESSION['user_id'];

$task_message = '';
$task_error = '';

// verificação dos metódos inputados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pomodoros_needed = trim($_POST['pomodoros_needed'] ?? '');
    
    if (empty($title)) {
        $task_error = "O título é necessário, você é burro?";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, pomodoros_needed) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $title, $description, $pomodoros_needed])) {
                $task_message = "Tarefa adicionada";
                $_POST = array();
            } else {
                $task_error = "Falha ao adicionar tarefa";
            }
        } catch (PDOException $e) {
            $task_error = "Erro no banco de dados";
        }
    }
}

// fetch para todas as tarefas do user
try {
    $stmt = $pdo->prepare("
        SELECT *, 
            CASE 
                WHEN status = 'completed' THEN 'Completed'
                ELSE 'Pending'
            END as status_text,
            DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as formatted_date,
            CONCAT(completed_pomodoros, '/', pomodoros_needed) as pomodoro_progress,
            (completed_pomodoros * 100 / pomodoros_needed) as pomodoro_percentage
        FROM tasks 
        WHERE user_id = ? 
        ORDER BY 
            CASE WHEN status = 'pending' THEN 0 ELSE 1 END,
            created_at DESC
    ");
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $task_error = "Failed to fetch tasks";
    $tasks = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pomodoro Dos Broxas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/style_dashboard.css">

</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <?php
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['info'])) {
                echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info']) . '</div>';
                unset($_SESSION['info']);
            }
            ?>

            <div class="welcome-banner">
                <h2>O nome do broxa que entrou é  <?php echo htmlspecialchars($username); ?>!</h2>
                <div class="session-info">
                    <?php 
                    $last_activity = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : null;
                    if ($last_activity) {
                        echo 'Última atividade: ' . date('H:i:s', $last_activity);
                    }
                    ?>
                </div>
            </div>

            <div class="task-section">
                <h3>Adicionar uma nova tarefa</h3>
                
                <?php if ($task_message): ?>
                    <div class="task-message task-success"><?php echo htmlspecialchars($task_message); ?></div>
                <?php endif; ?>
                
                <?php if ($task_error): ?>
                    <div class="task-message task-error"><?php echo htmlspecialchars($task_error); ?></div>
                <?php endif; ?>

                <form id="addTaskForm" class="task-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="form-group">
                        <input type="text" id="taskTitle" name="title" placeholder="Título para tarefa" required>
                    </div>
                    <div class="form-group">
                        <textarea id="taskDescription" name="description" placeholder="Descrição para tarefa" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="pomodoros">Números de pomodoros necessários:</label>
                        <input type="number" id="pomodoros" name="pomodoros_needed" min="1" value="1" required>
                    </div>
                    <button type="submit" name="add_task" class="submit-task-btn">Adicionar tarefa</button>
                </form>

                <div class="recent-tasks">
                    <h3>Tarefas pendentes</h3>
                    <?php
                    $pending_tasks = array_filter($tasks, function($task) {
                        return $task['status'] === 'pending';
                    });
                    
                    if (empty($pending_tasks)): ?>
                        <p class="no-tasks">Sem tarefas pendentes, cabaço, vai fazer algo</p>
                    <?php else: ?>
                        <div class="task-list">
                            <?php foreach ($pending_tasks as $task): ?>
                                <div class="task-item">
                                    <div class="task-content">
                                        <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                                        <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                        <div class="task-meta">
                                            <span class="task-date"><?php echo $task['formatted_date']; ?></span>
                                            <div class="pomodoro-progress">
                                                <span class="task-pomodoros">
                                                    <?php echo $task['pomodoro_progress']; ?> Pomodoro processo
                                                </span>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: <?php echo min(100, $task['pomodoro_percentage']); ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-actions">
                                        <a href="timer.php?task_id=<?php echo $task['id']; ?>" class="timer-btn">Começar timer</a>
                                        <form method="POST" action="delete_task.php" onsubmit="return confirmDelete(event, <?php echo $task['id']; ?>)" style="display: inline;">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="delete-btn">Deletar</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h3>Tarefeas Completas</h3>
                    <?php
                    $completed_tasks = array_filter($tasks, function($task) {
                        return $task['status'] === 'completed';
                    });
                    
                    if (!empty($completed_tasks)): ?>
                        <?php foreach ($completed_tasks as $task): ?>
                            <div class="task-item completed">
                                <div class="task-content">
                                    <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                                    <?php if (!empty($task['description'])): ?>
                                        <p><?php echo htmlspecialchars($task['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="task-meta">
                                        Criado em: <?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="task-actions">
                                    <form method="POST" action="delete_task.php" onsubmit="return confirmDelete(event, <?php echo $task['id']; ?>)">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="delete-btn">Deletar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Sem tarefas completas, que decepção</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="logout-container">
                <a href="logout.php" class="logout-btn">Sair</a>
            </div>
        </div>
    </div>

    <script>
    function deleteTask(taskId) {
        if (!confirm('Você tem certeza que deseja deletar essa tarefa? PENSE BEM')) return;

        fetch('delete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'task_id=' + taskId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                //refresh básico
                window.location.reload();
            } else {
                alert('Falha ao tentar deletar: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Falha ao tentar deletar a tarefa. Tente novamente');
        });
    }

    function confirmDelete(event, taskId) {
        event.preventDefault();
        
        if (!confirm('Você tem certeza que deseja deletar essa tarefa? PENSE BEM')) {
            return false;
        }

        fetch('delete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'task_id=' + taskId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Falha ao tentar deletar: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Falha ao tentar deletar a tarefa. Tente novamente');
        });

        return false;
    }
    </script>
</body>
</html>
