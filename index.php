<?php
session_start();
require_once './includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ./p/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pomodoro dos Broxas - O pior do Brasil</title>
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
            color: #d9d9d9;
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-btn {
            background: var(--primary-color);
            color: white;
        }

        .register-btn {
            border: 2px solid var(--primary-color);
            color: #fff;
        }

        .register-btn:hover{
            background-color: var(--primary-color);
            transition: ease-in 0.2s ;
        }

        @media (max-width: 584px){
            .logo-text{
                display: none;
            }
        }

        .hero {
            
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 2rem 2rem;
            background:var(--background-color);
            text-align: center;
        }

        .hero-content {
            max-width: 1000px;
            margin-top: 60px;
            z-index: 3;
        }

        .hero h1 {
            font-size: 4em;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            line-height: 1.2;
        }

        .purple{
            color: #8a2be2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #d9d9d9;
            font-weight: 400;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .timer-demo {
            background: var(--dark-accent);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 3rem auto;
            max-width: 300px;
        }

        .timer-display {
            font-size: 4rem;
            font-weight: bold;
            color: var(--primary-color);
            font-family: monospace;
            margin: 1rem 0;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            text-align: left;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: var(--dark-accent);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .feature i {
            font-size: 1.5rem;
            color: var(--primary-color);
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 50%;
        }

        .feature-text h3 {
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .feature-text p {
            font-size: 0.95rem;
            color: var(--text-color);
            margin: 0;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero {
                padding-top: 5rem;
            }

            .features {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .get-started {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 500;
            text-decoration: none;
            margin-top: 2rem;
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
            box-shadow: 0 0 15px rgba(144, 0, 255, 0.7);
        }

        .get-started:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Teste de background */

        .background-container {
            top: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: #1c1b22;
            overflow: hidden;
            z-index: 1;

    }

        .bg-circle {
            position: absolute;
            border-radius: 50%;
        }

        .bg-circle-1 {
            top: 5%;
            left: 5%;
            width: 300px;
            height: 300px;
            background-color: rgba(147, 51, 234, 0.1);
            filter: blur(40px);
        }

        .bg-circle-2 {
            top: 10%;
            right: 5%;
            width: 400px;
            height: 400px;
            background-color: rgba(88, 28, 135, 0.1);
            filter: blur(50px);
        }

        .bg-circle-3 {
            bottom: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 500px;
            height: 500px;
            background-color: rgba(168, 85, 247, 0.05);
            filter: blur(60px);
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
        }

        .bg-shape-1 {
            top: 5%;
            right: 10%;
            width: 80px;
            height: 80px;
            border: 1px solid rgba(147, 51, 234, 0.2);
        }

        .bg-shape-2 {
            bottom: 10%;
            left: 5%;
            width: 120px;
            height: 120px;
            border: 2px solid rgba(147, 51, 234, 0.1);
        }

        .bg-shape-3 {
            top: 15%;
            left: 45%;
            width: 60px;
            height: 60px;
            border: 1px solid rgba(168, 85, 247, 0.2);
            transform: rotate(45deg);
        }

        .bg-dots {
            position: absolute;
            top: 20%;
            right: 15%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .bg-dots div {
            width: 6px;
            height: 6px;
            background-color: rgba(147, 51, 234, 0.3);
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- isso aqui é somente o backround -->
    <div class="background-container">
        <div class="bg-circle bg-circle-1"></div>
        <div class="bg-circle bg-circle-2"></div>
        <div class="bg-circle bg-circle-3"></div>
        
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
        
        <div class="bg-dots">
            <div></div><div></div><div></div>
            <div></div><div></div><div></div>
            <div></div><div></div><div></div>
        </div>
    </div>
    <!-- fim -->



    <nav class="nav">
        <a href="index.php" class="logo">
            <i class="fas fa-clock"></i>
            <span class="logo-text">Pomodoro Dos Broxas</span>
        </a>
        <div class="nav-buttons">
            <a href="./p/login.php" class="btn login-btn">Login</a>
            <a href="./p/register.php" class="btn register-btn">Registrar</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Melhore sua <span class="purple">produtividade</span> e esqueça que é broxa</h1>
            <p>Se mantenha focado a todo momento, estamos aqui para te ajudar e fazer esquecer do seu problema peniano!</p>

            <a href="./p/register.php" class="get-started">
                Comece agora
                <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </a>
            
            <div class="timer-demo">
                <div class="timer-display">25:00</div>
                <p>Tudo de ruim pode acontecer</p>
            </div>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-brain"></i>
                    <div class="feature-text">
                        <h3>Se mantenha focado</h3>
                        <p>Estude/Trabalhe com um pico maior de produtividade.</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <div class="feature-text">
                        <h3>Veja seu progresso</h3>
                        <p>Monitore todos os seus progressos e tarefas feitas anteriormente.</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-coffee"></i>
                    <div class="feature-text">
                        <h3>Quebras rápidas</h3>
                        <p>Mantenha a energia com quebras rápidas, somos seres humanos.</p>
                    </div>
                </div>
            </div>

            
        </div>
    </section>
</body>
</html>
