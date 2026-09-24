-- Sta. Monica Parish
-- Event activity/program schedule support
-- Safe for shared hosting / InfinityFree: no stored procedures.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS event_activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    activity_type VARCHAR(50) NOT NULL DEFAULT 'activity',
    title VARCHAR(180) NOT NULL,
    description TEXT DEFAULT NULL,
    activity_date DATE NOT NULL,
    activity_end_date DATE DEFAULT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    location VARCHAR(200) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_activities_event
      FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_activity_schedule (event_id, activity_date, start_time, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
