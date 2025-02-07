<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
//teste para ver se o ranking está funcionando uhuuuuuuu - retirar dps
try {
    $pdo->exec("
        INSERT INTO users (id, username, email, password) VALUES
        (1, 'camisinhausada', 'estourautero@gmail.com', '12345678'),
        (2, 'cuecaborrada', 'camisoladeborracha@gmail.com', '12345678'),
        ON DUPLICATE KEY UPDATE username = VALUES(username);
    ");

    $pdo->exec("
        INSERT INTO tasks (user_id, title, pomodoros_needed, completed_pomodoros, status, created_at, completed_at) VALUES
        (1, 'Matar uma idosa', 1, 1, 'completed', '2025-02-01 10:00:00', '2025-02-01 10:30:00'),
        (1, 'Estudar anatomia', 2, 2, 'completed', '2025-02-02 11:00:00', '2025-02-02 12:30:00'),
        (2, 'Desenhar', 1, 1, 'completed', '2025-02-01 09:00:00', '2025-02-01 09:45:00'),
        ON DUPLICATE KEY UPDATE title = VALUES(title);
    ");

    echo "Dados inseridos com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao inserir dados: " . $e->getMessage();
}

try {
    //consulta o ranking baseado no tempo total das tarefas concluídas
    $stmt = $pdo->prepare("
        SELECT 
            u.username, 
            SUM(TIMESTAMPDIFF(MINUTE, t.created_at, t.completed_at)) as total_time
        FROM tasks t
        INNER JOIN users u ON t.user_id = u.id
        WHERE t.status = 'completed' AND t.completed_at IS NOT NULL
        GROUP BY t.user_id
        ORDER BY total_time DESC
    ");
    $stmt->execute();
    $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$ranking) {
        http_response_code(404);
        echo json_encode(['error' => 'Nenhum dado encontrado']);
        exit();
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ranking de Usuários</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 800px;
                margin: 50px auto;
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            h1 {
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th {
                background-color: #f8f8f8;
            }
            tr:hover {
                background-color: #f1f1f1;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Ranking de Usuários Broxas</h1>
            <table>
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Usuário</th>
                        <th>Tempo total (minutos)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $index => $user): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['total_time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados', 'details' => $e->getMessage()]);
}
