<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // validar inputs
    if (empty($username)) {
        $errors[] = "O username é necessário";
    }
    if (empty($email)) {
        $errors[] = "O email é necessário";
    }
    if (empty($password)) {
        $errors[] = "Senha é necessária";
    }
    if ($password !== $confirm_password) {
        $errors[] = "As senhas não são iguas";
    }

    // check de emails, caso tenha um já sendo utilizado
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Esse email já está sendo utilizado";
        }
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "O registro falho. Tente novamente";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - Pomodoro Dos Broxas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
         :root {
            --primary-color: #6a0dad;
            --secondary-color: #8a2be2;
            --background-color: #1c1b22;
            --light-color: #bfbfff;
            --dark-accent: #292943;
            --hover-accent: #4a4a8a;
            --button-hover: #a557ec;
            --text-color: #fff;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            overflow-x: hidden;
        }

        .nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #d9d9d9;
            text-decoration: none;
        }
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .register-box{
            margin-top:50px;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            
        }

        .register-header {
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: var(--text-color);
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .register-header p {
            color: #d9d9d9;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color:rgb(255, 255, 255);
            font-weight: 500;
            font-size: 0.9em;
        }

        .form-group input {
            width: 100%;
            padding: 10px;

            border-radius: 4px;
            font-size: 1rem;
            color: #e5e5e5;

            border: solid 1px rgb(102, 102, 102);
            background-color: transparent;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background: #8a2be2;
            color: white;
            font-size: 0.8em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1em;
        }

        .submit-btn:hover {
            background:rgb(111, 38, 180);
            transition: background 0.7s ease-in;
            transform: translateY(-1px);
        }

        .error-list {
            background: #ffebee;
            color: #c62828;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: left;
        }

        .error-list ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .login-link {
            margin-top: 1.5rem;
            color:#c9c9c9;
        }

        .login-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .register-box {
                padding: 2rem;
            }
        }

        .form-box{
            border: solid 1px rgba(80, 80, 80, 0.75);
            padding: 20px;
            border-radius: 12px;
            background-color:rgba(55, 50, 57, 0.63);
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="index.php" class="logo">
            <i class="fas fa-clock"></i>
            Pomodoro Dos Broxas
        </a>
    </nav>

    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1>Crie sua Conta</h1>
                <p>Sua vida vai melhorar entrando para o Pomodoro Dos Broxas, é sem erro</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form-box">
                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-requirements">
                        Sua senha precisa ter ao menos 8 caracteres
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="submit-btn">Criar Conta</button>
            </form>

            <div class="login-link">
                Já possui uma conta? <a href="login.php">Login aqui</a>
            </div>
        </div>
    </div>
</body>
</html>
