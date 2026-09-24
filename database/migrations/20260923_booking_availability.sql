-- Sta. Monica Parish
-- Availability-based sacramental booking upgrade
-- Date: 2026-09-23
-- InfinityFree/shared-hosting compatible version.
--
-- IMPORTANT:
-- This script does NOT create stored procedures/functions because shared
-- hosting accounts commonly do not have CREATE/ALTER ROUTINE privileges.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- Add availability configuration columns only when missing.
-- MySQL 5.7-compatible approach using INFORMATION_SCHEMA + PREPARE.
-- -------------------------------------------------------------------------

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'duration_minutes'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN duration_minutes INT UNSIGNED NOT NULL DEFAULT 60 AFTER base_fee'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'uses_main_church'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN uses_main_church BOOLEAN NOT NULL DEFAULT 1 AFTER duration_minutes'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'allow_special_booking'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN allow_special_booking BOOLEAN NOT NULL DEFAULT 0 AFTER uses_main_church'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'special_fee'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN special_fee DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER allow_special_booking'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'special_start_time'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN special_start_time TIME DEFAULT NULL AFTER special_fee'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'special_end_time'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN special_end_time TIME DEFAULT NULL AFTER special_start_time'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'slot_interval_minutes'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN slot_interval_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60 AFTER special_end_time'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'booking_buffer_before_minutes'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN booking_buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER slot_interval_minutes'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'booking_buffer_minutes'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN booking_buffer_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER booking_buffer_before_minutes'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'requires_priest'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN requires_priest BOOLEAN NOT NULL DEFAULT 1 AFTER booking_buffer_minutes'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'min_advance_days'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN min_advance_days SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER requires_priest'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_types' AND COLUMN_NAME = 'max_advance_days'),
    'SELECT 1',
    'ALTER TABLE service_types ADD COLUMN max_advance_days SMALLINT UNSIGNED NOT NULL DEFAULT 365 AFTER min_advance_days'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_bookings' AND COLUMN_NAME = 'booking_type'),
    'SELECT 1',
    'ALTER TABLE service_bookings ADD COLUMN booking_type ENUM(''regular'',''special'') NOT NULL DEFAULT ''special'' AFTER service_type_id'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_bookings' AND COLUMN_NAME = 'schedule_rule_id'),
    'SELECT 1',
    'ALTER TABLE service_bookings ADD COLUMN schedule_rule_id INT UNSIGNED DEFAULT NULL AFTER booking_type'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------------------------------
-- Recurring regular/free schedule rules.
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS service_schedule_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_type_id INT UNSIGNED NOT NULL,
    rule_name VARCHAR(150) NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL,
    week_numbers VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5',
    start_time TIME DEFAULT NULL,
    fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    capacity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    valid_from DATE DEFAULT NULL,
    valid_until DATE DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_rule_active (service_type_id, is_active),
    CONSTRAINT fk_service_schedule_rule_type
        FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------------------
-- Parish defaults requested for Wedding and Baptism.
-- Staff can change these afterward under Service Availability.
-- -------------------------------------------------------------------------

UPDATE service_types
SET allow_special_booking = 1,
    special_fee = 9000.00,
    uses_main_church = 1,
    duration_minutes = CASE WHEN duration_minutes < 60 THEN 60 ELSE duration_minutes END,
    booking_buffer_before_minutes = 60,
    booking_buffer_minutes = 45,
    requires_priest = 1
WHERE service_key = 'wedding';

UPDATE service_types
SET allow_special_booking = 1,
    uses_main_church = 1,
    booking_buffer_before_minutes = 30,
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

-- Wedding: free every 2nd and 4th Thursday at 6:00 AM.
INSERT INTO service_schedule_rules
(service_type_id, rule_name, day_of_week, week_numbers, start_time, fee_amount, capacity, is_active, display_order)
SELECT st.id, 'Free Wedding - 2nd & 4th Thursday', 4, '2,4', '06:00:00', 0.00, 1, 1, 10
FROM service_types st
WHERE st.service_key = 'wedding'
  AND NOT EXISTS (
      SELECT 1
      FROM service_schedule_rules r
      WHERE r.service_type_id = st.id
        AND r.rule_name = 'Free Wedding - 2nd & 4th Thursday'
  );

-- Baptism: free every 2nd and 4th Saturday.
-- Start time remains NULL until parish staff publishes the official time.
INSERT INTO service_schedule_rules
(service_type_id, rule_name, day_of_week, week_numbers, start_time, fee_amount, capacity, is_active, display_order)
SELECT st.id, 'Free Baptism - 2nd & 4th Saturday', 6, '2,4', NULL, 0.00, 1, 1, 10
FROM service_types st
WHERE st.service_key = 'baptism'
  AND NOT EXISTS (
      SELECT 1
      FROM service_schedule_rules r
      WHERE r.service_type_id = st.id
        AND r.rule_name = 'Free Baptism - 2nd & 4th Saturday'
  );

-- -------------------------------------------------------------------------
-- Index used by cross-service schedule conflict checks.
-- -------------------------------------------------------------------------
SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'service_bookings'
          AND INDEX_NAME = 'idx_booking_schedule'
    ),
    'SELECT 1',
    'ALTER TABLE service_bookings ADD INDEX idx_booking_schedule (confirmed_date, status)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO system_settings (setting_key, setting_value)
SELECT 'priest_booking_capacity', '2'
WHERE NOT EXISTS (
    SELECT 1 FROM system_settings WHERE setting_key = 'priest_booking_capacity'
);

SET FOREIGN_KEY_CHECKS = 1;
