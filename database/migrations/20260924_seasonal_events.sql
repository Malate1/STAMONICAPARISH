-- Sta. Monica Parish
-- Seasonal event support for yearly public-site highlights
-- Safe for shared hosting / InfinityFree: no stored procedures.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'season_key'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD COLUMN season_key VARCHAR(50) DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'season_year'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD COLUMN season_year SMALLINT UNSIGNED DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'is_seasonal'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD COLUMN is_seasonal TINYINT(1) NOT NULL DEFAULT 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'highlight_on_home'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD COLUMN highlight_on_home TINYINT(1) NOT NULL DEFAULT 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'highlight_start'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD COLUMN highlight_start DATE DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND COLUMN_NAME = 'highlight_end'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD COLUMN highlight_end DATE DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND INDEX_NAME = 'uq_event_season_year'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD UNIQUE KEY uq_event_season_year (season_key, season_year)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'events'
      AND INDEX_NAME = 'idx_event_highlight'
  ),
  'SELECT 1',
  'ALTER TABLE events ADD INDEX idx_event_highlight (highlight_on_home, highlight_start, highlight_end)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
