-- Sta. Monica Parish
-- Mass Intention dated-occurrence + commentator/reader workflow
-- Date: 2026-09-24
-- InfinityFree/shared-hosting compatible: no stored procedures required.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mass_intentions'
      AND COLUMN_NAME = 'mass_date'
  ),
  'SELECT 1',
  'ALTER TABLE mass_intentions ADD COLUMN mass_date DATE DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mass_intentions'
      AND COLUMN_NAME = 'processed_by'
  ),
  'SELECT 1',
  'ALTER TABLE mass_intentions ADD COLUMN processed_by INT UNSIGNED DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mass_intentions'
      AND COLUMN_NAME = 'approved_at'
  ),
  'SELECT 1',
  'ALTER TABLE mass_intentions ADD COLUMN approved_at DATETIME DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mass_intentions'
      AND COLUMN_NAME = 'ready_at'
  ),
  'SELECT 1',
  'ALTER TABLE mass_intentions ADD COLUMN ready_at DATETIME DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mass_intentions'
      AND COLUMN_NAME = 'completed_at'
  ),
  'SELECT 1',
  'ALTER TABLE mass_intentions ADD COLUMN completed_at DATETIME DEFAULT NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Temporarily allow both legacy "listed" and the clearer new
-- "ready_for_reading" value so existing rows can be migrated safely.
ALTER TABLE mass_intentions
MODIFY COLUMN status ENUM(
  'pending',
  'approved',
  'ready_for_reading',
  'listed',
  'completed',
  'cancelled'
) NOT NULL DEFAULT 'pending';

UPDATE mass_intentions
SET status = 'ready_for_reading',
    ready_at = COALESCE(ready_at, created_at)
WHERE status = 'listed';

ALTER TABLE mass_intentions
MODIFY COLUMN status ENUM(
  'pending',
  'approved',
  'ready_for_reading',
  'completed',
  'cancelled'
) NOT NULL DEFAULT 'pending';

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mass_intentions'
      AND INDEX_NAME = 'idx_mass_intention_occurrence'
  ),
  'SELECT 1',
  'ALTER TABLE mass_intentions ADD INDEX idx_mass_intention_occurrence (mass_date, mass_schedule_id, status)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO system_settings (setting_key, setting_value)
SELECT 'mass_intention_cutoff_minutes', '30'
WHERE NOT EXISTS (
  SELECT 1 FROM system_settings WHERE setting_key = 'mass_intention_cutoff_minutes'
);

SET FOREIGN_KEY_CHECKS = 1;
