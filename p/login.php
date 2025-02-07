<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Senha ou email inválidos";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pomodoro Dos Broxas</title>
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
            background-image: radial-gradient(#1c1b22,rgb(28, 27, 34));
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            overflow-x: hidden;
        }

        .nav {
            position: fixed;
            top: 0;
            width: 100%;
            backdrop-filter: blur(8px);
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
            color: var(--primary-color);
            text-decoration: none;
            color: #d9d9d9;
        }

        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-box {
            margin-top:50px;

            padding: 2.5rem;
            border-radius: 15px;

            width: 100%;
            max-width: 500px;
            text-align: center;

            
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h1 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: var(--text-color);
            font-size: 2rem;

        }

        .login-header p {
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

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .register-link {
            margin-top: 1.5rem;
            color:#c9c9c9;
        }

        .register-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-box {
                padding: 2rem;
            }
        }

        .dot{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            background-color:rgba(164, 87, 236, 0.09);
            border-radius: 50%;
            filter: blur(23px);
            box-shadow: 0 4px 100px rgba(161, 31, 242, 0.3);
            z-index: -1;
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
        <a href="../index.php" class="logo">
            <i class="fas fa-clock"></i>
            Pomodoro Dos Broxas
        </a>
    </nav>

    <div class="dot"></div>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Bem vindo de volta</h1>
                <p>Sabemos que não aguenta ficar longe</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form-box">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="submit-btn">Logar <i class="fa-solid fa-arrow-right"></i></button>
            </form>

            <div class="register-link">
                Não tem uma conta? <a href="register.php">Registre aqui</a>
            </div>
        </div>
    </div>
</body>
</html>
