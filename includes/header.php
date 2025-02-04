<?php
ob_start(); // Ativa o buffer de saída
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pomodoro dos Broxas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a0dad; 
            --secondary-color: #8a2be2; 
            --text-color: #fff; 
            --light-bg: #f3f0fa; 
            --dark-bg: #4b0082;
            --border-color: #dcdcdc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            padding-top: 70px; 
        }

        .header {
            background: var(--primary-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--text-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 2rem;
        }

        .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--text-color);
            cursor: pointer;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            margin-left: 2rem;
            gap: 1.5rem;
            flex-grow: 1;
        }

        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: var(--secondary-color);
        }

        .nav-link.active {
            background: var(--secondary-color);
        }

        .user-menu {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: var(--secondary-color);
            color: var(--text-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: var(--dark-bg);
        }

        .back-to-dashboard {
            color: var(--text-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-dashboard:hover {
            background: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .nav-toggle {
                display: block;
                margin-left: auto;
            }

            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: var(--primary-color);
                flex-direction: column;
                padding: 2rem;
                margin: 0;
                transition: left 0.3s ease;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                width: 100%;
                text-align: center;
                padding: 1rem;
            }

            .user-menu {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: var(--primary-color);
                padding: 1rem;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">
            <i class="fas fa-clock"></i>
            Pomodoro dos Broxas
        </a>

        <button class="nav-toggle" id="navToggle">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="nav-menu" id="navMenu">
            <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="statistics.php" class="nav-link <?php echo $current_page === 'statistics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Estatísticas
            </a>
            <a href="settings.php" class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Configurações
            </a>
        </nav>

        <div class="user-menu">
            <?php if ($current_page === 'timer.php'): ?>
                <a href="dashboard.php" class="back-to-dashboard">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <?php echo $current_page === 'timer.php' ? '' : 'Sair'; ?>
            </a>
        </div>
    </header>

    <script>
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');

        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            navToggle.innerHTML = navMenu.classList.contains('active') 
                ? '<i class="fas fa-times"></i>' 
                : '<i class="fas fa-bars"></i>';
        });

        document.addEventListener('click', (e) => {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                navToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    </script>
</body>
</html>