<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$message = '';
$settings = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_duration = filter_input(INPUT_POST, 'work_duration', FILTER_VALIDATE_INT);
    $break_duration = filter_input(INPUT_POST, 'break_duration', FILTER_VALIDATE_INT);

    if ($work_duration < 1 || $work_duration > 60 || $break_duration < 1 || $break_duration > 30) {
        $message = 'O input foi inválido';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE settings SET work_duration = ?, break_duration = ? WHERE user_id = ?");
                $stmt->execute([$work_duration, $break_duration, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO settings (user_id, work_duration, break_duration) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $work_duration, $break_duration]);
            }
            
            $message = 'Configurações salvas com sucesso';
            
        } catch (PDOException $e) {
            $message = 'Erro ao tentar salvar configurações. Tente novamente';
            error_log($e->getMessage());
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT work_duration, break_duration FROM settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching settings.';
    error_log($e->getMessage());
}

if (!$settings) {
    $settings = [
        'work_duration' => 25,
        'break_duration' => 5
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer Settings - PomodoroFlow</title>
    <style>
        :root {
            --primary-color: #6a0dad;
            --secondary-color: #8a2be2;
            --background-color: #1e1e2f;
            --light-color: #bfbfff;
            --dark-accent: #292943;
            --hover-accent: #4a4a8a;
            --button-hover: #a557ec;
            --text-color: #fff;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            margin-top:100px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .settings-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: #444;
        }

        input[type="number"] {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: var(--secondary-color);
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        .error {
            background-color: #ffebee;
            color: var(--error-color);
        }

        .nav-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .nav-links a {
            color: var(--primary-color);
            text-decoration: none;
            margin: 0 10px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Configurar tempo de pomodoro</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="settings-form" method="POST">
            <div class="form-group">
                <label for="work_duration">Tempo de estudo (em minutos):</label>
                <input type="number" id="work_duration" name="work_duration" 
                       value="<?php echo htmlspecialchars($settings['work_duration']); ?>" 
                       min="1" max="60" required>
            </div>

            <div class="form-group">
                <label for="break_duration">Tempo de descanso (em minutos):</label>
                <input type="number" id="break_duration" name="break_duration" 
                       value="<?php echo htmlspecialchars($settings['break_duration']); ?>" 
                       min="1" max="30" required>
            </div>

            <button type="submit">Salvar configurações</button>
        </form>

        <div class="nav-links">
            <a href="dashboard.php">Voltar ao dashboard</a>
        </div>
    </div>
</body>
</html>
