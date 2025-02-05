<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_tasks 
    FROM tasks 
    WHERE user_id = ? AND status = 'completed'
");
$stmt->execute([$user_id]);
$total_tasks = $stmt->fetch(PDO::FETCH_ASSOC)['total_tasks'];

$total_sessions = $total_tasks;

$stmt = $pdo->prepare("
    SELECT work_duration 
    FROM settings 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$avg_duration = $stmt->fetch(PDO::FETCH_ASSOC);
$avg_duration = $avg_duration ? $avg_duration['work_duration'] : 25; 

$stmt = $pdo->prepare("
    SELECT title, COUNT(*) as completion_count
    FROM tasks 
    WHERE user_id = ? AND status = 'completed'
    GROUP BY title
    ORDER BY completion_count DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$most_active_task = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT DATE(completed_at) as completion_date, COUNT(*) as tasks_completed
    FROM tasks 
    WHERE user_id = ? AND status = 'completed'
    GROUP BY DATE(completed_at)
    ORDER BY tasks_completed DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$best_day = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        DATE(completed_at) as date,
        COUNT(*) as completed_count
    FROM tasks 
    WHERE 
        user_id = ? 
        AND status = 'completed' 
        AND completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    GROUP BY DATE(completed_at)
    ORDER BY date ASC
");
$stmt->execute([$user_id]);
$daily_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dates = [];
$counts = [];
foreach ($daily_stats as $stat) {
    $dates[] = date('M d', strtotime($stat['date']));
    $counts[] = (int)$stat['completed_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Pomodoro Dos Broxas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --text-color:rgb(0, 0, 0);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: var(--light-bg);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #fff;
            margin-right:10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card .icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--text-color);
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .chart-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .nav-links {
            text-align: center;
            margin-top: 2rem;
        }

        .nav-links a {
            color: var(--primary-color);
            text-decoration: none;
            margin: 0 1rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-links a:hover {
            background-color: var(--border-color);
        }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select {
            appearance: none;
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text-color);
            background: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:hover {
            border-color: var(--primary-color);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .filter-group::after {
            content: '▼';
            font-size: 0.8rem;
            color: var(--text-color);
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(25%);
            pointer-events: none;
        }

        .filter-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text-color);
            background: #fff;
            transition: all 0.3s ease;
        }

        .filter-input:hover {
            border-color: var(--primary-color);
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .custom-range {
            display: none;
            width: 100%;
            gap: 1.5rem;
        }

        .custom-range.active {
            display: flex;
        }

        input[type="date"] {
            position: relative;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }

        .filter-group.date-range::before {
            content: '📅';
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(25%);
            z-index: 1;
            font-size: 1.2rem;
        }

        .filter-group.date-input::before {
            content: '📅';
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(25%);
            z-index: 1;
            font-size: 1.2rem;
        }

        .filter-select, .filter-input {
            padding-left: 3rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .filters {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Estatísticas de Pomodoro</h1>
        </div>

        <div class="filters">
            <div class="filter-group date-range">
                <label for="dateRange">Datas</label>
                <select id="dateRange" class="filter-select">
                    <option value="last7days">Últimos 7 Dias</option>
                    <option value="last30days">Últimos 30 Dias</option>
                    <option value="thismonth">Esse mês</option>
                    <option value="lastmonth">Último mês</option>
                    <option value="custom">Data personalizada</option>
                </select>
            </div>

            <div class="custom-range">
                <div class="filter-group date-input">
                    <label for="startDate">Começo da data</label>
                    <input type="date" id="startDate" class="filter-input">
                </div>
                <div class="filter-group date-input">
                    <label for="endDate">Final da data</label>
                    <input type="date" id="endDate" class="filter-input">
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Total de sessões</h3>
                <div class="value"><?php echo $total_sessions; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3>Tarefas Completas</h3>
                <div class="value"><?php echo $total_tasks; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <h3>Maior sessão</h3>
                <div class="value"><?php echo $avg_duration; ?> min</div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Tarefa mais ativa</h3>
                <div class="value">
                    <?php 
                    if ($most_active_task) {
                        echo htmlspecialchars(substr($most_active_task['title'], 0, 20));
                        if (strlen($most_active_task['title']) > 20) echo '...';
                    } else {
                        echo 'No tasks yet';
                    }
                    ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Melhor dia</h3>
                <div class="value">
                    <?php 
                    if ($best_day) {
                        echo date('M d', strtotime($best_day['completion_date']));
                        echo ' (' . $best_day['tasks_completed'] . ' tasks)';
                    } else {
                        echo 'Sem datas aqui';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <h2 class="chart-title">Tarefas completas (últimos 7 dias)</h2>
            <canvas id="tasksChart"></canvas>
        </div>

        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Voltar para a dashboard</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Configurações</a>
        </div>
    </div>

    <script>
        let chart;

        function initChart(labels, data) {
            const ctx = document.getElementById('tasksChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tarefas completas',
                        data: data,
                        backgroundColor: '#4CAF50',
                        borderColor: '#45a049',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function updateStatistics(data) {
            document.querySelector('.stat-card:nth-child(1) .value').textContent = data.total_sessions;
            document.querySelector('.stat-card:nth-child(2) .value').textContent = data.total_tasks;
            document.querySelector('.stat-card:nth-child(3) .value').textContent = data.avg_duration + ' min';
            
            const mostActiveTaskElement = document.querySelector('.stat-card:nth-child(4) .value');
            if (data.most_active_task) {
                const title = data.most_active_task.title.length > 20 
                    ? data.most_active_task.title.substring(0, 20) + '...' 
                    : data.most_active_task.title;
                mostActiveTaskElement.textContent = title;
            } else {
                mostActiveTaskElement.textContent = 'Sem datas aqui';
            }

            const bestDayElement = document.querySelector('.stat-card:nth-child(5) .value');
            if (data.best_day) {
                bestDayElement.textContent = `${data.best_day.date} (${data.best_day.count} tarefas)`;
            } else {
                bestDayElement.textContent = 'Sem datas aqui';
            }

            if (chart) {
                chart.destroy();
            }
            initChart(data.chart_data.labels, data.chart_data.counts);
        }

        function fetchStatistics() {
            const dateRange = document.getElementById('dateRange').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            let url = `get_statistics.php?range=${dateRange}`;
            if (dateRange === 'custom') {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        updateStatistics(response.data);
                    } else {
                        alert('Erro ao capturar informações: ' + response.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao capturar informações. Tente novamente');
                });
        }

        document.getElementById('dateRange').addEventListener('change', function() {
            const customRange = document.querySelector('.custom-range');
            if (this.value === 'custom') {
                customRange.style.display = 'flex';
            } else {
                customRange.style.display = 'none';
                fetchStatistics();
            }
        });

        document.getElementById('startDate').addEventListener('change', function() {
            if (document.getElementById('endDate').value) {
                fetchStatistics();
            }
        });

        document.getElementById('endDate').addEventListener('change', function() {
            if (document.getElementById('startDate').value) {
                fetchStatistics();
            }
        });

        initChart(<?php echo json_encode($dates); ?>, <?php echo json_encode($counts); ?>);
    </script>
</body>
</html>
