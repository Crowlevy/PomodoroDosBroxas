CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    pomodoros_needed INT DEFAULT 1,
    completed_pomodoros INT DEFAULT 0,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    -- indexs pra melhorar a perfomance (medo sincero disso dar um bug insano)
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- comentário
ALTER TABLE tasks
    COMMENT 'Tarefas para o usuário';

-- testes meio irrelevantes
/*
INSERT INTO tasks (user_id, title, description) VALUES
    (1,'projeto completo', 'escrever');
*/

/*
UPDATE tasks 
SET status = 'completed', 
    updated_at = CURRENT_TIMESTAMP 
WHERE id = 1;
*/
