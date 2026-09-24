-- Sta. Monica Parish
-- Improved booking resource protection + priest capacity
-- Date: 2026-09-24
-- InfinityFree/shared-hosting compatible: no stored routines required.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Make this migration safe even if the previous availability migration
-- was only partially applied on shared hosting.
SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_types'
      AND COLUMN_NAME = 'slot_interval_minutes'
  ),
  'SELECT 1',
  'ALTER TABLE service_types ADD COLUMN slot_interval_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_types'
      AND COLUMN_NAME = 'booking_buffer_before_minutes'
  ),
  'SELECT 1',
  'ALTER TABLE service_types ADD COLUMN booking_buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_types'
      AND COLUMN_NAME = 'booking_buffer_minutes'
  ),
  'SELECT 1',
  'ALTER TABLE service_types ADD COLUMN booking_buffer_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS(
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_types'
      AND COLUMN_NAME = 'requires_priest'
  ),
  'SELECT 1',
  'ALTER TABLE service_types ADD COLUMN requires_priest BOOLEAN NOT NULL DEFAULT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Practical starting values. Staff can change these at any time from
-- Service Configuration / Service Availability.
UPDATE service_types
SET booking_buffer_before_minutes = 60,
    booking_buffer_minutes = 45,
    requires_priest = 1
WHERE service_key = 'wedding';

UPDATE service_types
SET booking_buffer_before_minutes = 30,
    booking_buffer_minutes = 30,
    requires_priest = 1
WHERE service_key = 'baptism';

UPDATE service_types
SET booking_buffer_before_minutes = 30,
    booking_buffer_minutes = 30,
    requires_priest = 1
WHERE service_key = 'funeral';

UPDATE service_types
SET requires_priest = 1
WHERE service_key IN ('confirmation','house_blessing','vehicle_blessing','counseling');

-- The parish currently has two priests available for booking work. Keeping
-- this as a setting avoids accidentally using extra test/inactive accounts as
-- booking capacity while still allowing the parish to change it later.
INSERT INTO system_settings (setting_key, setting_value)
SELECT 'priest_booking_capacity', '2'
WHERE NOT EXISTS (
  SELECT 1 FROM system_settings WHERE setting_key = 'priest_booking_capacity'
);

SET FOREIGN_KEY_CHECKS = 1;
