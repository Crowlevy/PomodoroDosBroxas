ALTER TABLE tasks
ADD COLUMN completed_pomodoros INT DEFAULT 0;

UPDATE tasks SET completed_pomodoros = 0 WHERE completed_pomodoros IS NULL;
