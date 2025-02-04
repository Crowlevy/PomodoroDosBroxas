ALTER TABLE tasks
ADD COLUMN pomodoros_needed INT DEFAULT 1;

UPDATE tasks SET pomodoros_needed = 1 WHERE pomodoros_needed IS NULL;
