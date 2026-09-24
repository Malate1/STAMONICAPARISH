-- Sta. Monica Parish - Availability-based sacramental booking upgrade
-- Run this ONCE on the existing database before using the new booking UI.

ALTER TABLE service_types
  ADD COLUMN uses_main_church TINYINT(1) NOT NULL DEFAULT 1 AFTER duration_minutes,
  ADD COLUMN allow_special_booking TINYINT(1) NOT NULL DEFAULT 0 AFTER uses_main_church,
  ADD COLUMN special_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER allow_special_booking,
  ADD COLUMN special_start_time TIME NULL AFTER special_fee,
  ADD COLUMN special_end_time TIME NULL AFTER special_start_time,
  ADD COLUMN slot_interval_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60 AFTER special_end_time,
  ADD COLUMN booking_buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER slot_interval_minutes,
  ADD COLUMN booking_buffer_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER booking_buffer_before_minutes,
  ADD COLUMN requires_priest TINYINT(1) NOT NULL DEFAULT 1 AFTER booking_buffer_minutes,
  ADD COLUMN min_advance_days SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER requires_priest,
  ADD COLUMN max_advance_days SMALLINT UNSIGNED NOT NULL DEFAULT 365 AFTER min_advance_days;

CREATE TABLE service_schedule_rules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_type_id INT UNSIGNED NOT NULL,
  rule_name VARCHAR(150) NOT NULL,
  day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday ... 6=Saturday',
  week_numbers VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5' COMMENT 'Comma separated occurrences in month, e.g. 2,4',
  start_time TIME DEFAULT NULL,
  fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  capacity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  valid_from DATE DEFAULT NULL,
  valid_until DATE DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_service_schedule_rule_service
    FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE,
  INDEX idx_service_rule_active (service_type_id, is_active),
  INDEX idx_service_rule_weekday (day_of_week)
) ENGINE=InnoDB;

ALTER TABLE service_bookings
  ADD COLUMN booking_type ENUM('regular','special') NOT NULL DEFAULT 'special' AFTER service_type_id,
  ADD COLUMN schedule_rule_id INT UNSIGNED DEFAULT NULL AFTER booking_type,
  ADD CONSTRAINT fk_booking_schedule_rule
    FOREIGN KEY (schedule_rule_id) REFERENCES service_schedule_rules(id) ON DELETE SET NULL,
  ADD INDEX idx_booking_schedule (confirmed_date, status);

-- Wedding policy requested for Sta. Monica Parish:
-- Free: every 2nd and 4th Thursday at 6:00 AM.
-- Special booking: all other eligible dates, PHP 9,000.
UPDATE service_types
SET allow_special_booking = 1,
    special_fee = 9000.00,
    uses_main_church = 1,
    booking_buffer_before_minutes = 60,
    booking_buffer_minutes = 45,
    requires_priest = 1
WHERE service_key = 'wedding';

INSERT INTO service_schedule_rules
(service_type_id, rule_name, day_of_week, week_numbers, start_time, fee_amount, capacity, is_active, display_order)
SELECT id, 'Free Wedding - 2nd & 4th Thursday', 4, '2,4', '06:00:00', 0.00, 1, 1, 10
FROM service_types
WHERE service_key = 'wedding';

-- Baptism policy requested:
-- Free: every 2nd and 4th Saturday.
-- The exact time is intentionally left NULL so parish staff can set the official
-- time in Service Configuration before public free slots are offered.
UPDATE service_types
SET allow_special_booking = 1,
    special_fee = base_fee,
    uses_main_church = 1,
    booking_buffer_before_minutes = 30,
    booking_buffer_minutes = 30,
    requires_priest = 1
WHERE service_key = 'baptism';

INSERT INTO service_schedule_rules
(service_type_id, rule_name, day_of_week, week_numbers, start_time, fee_amount, capacity, is_active, display_order)
SELECT id, 'Free Baptism - 2nd & 4th Saturday', 6, '2,4', NULL, 0.00, 1, 1, 10
FROM service_types
WHERE service_key = 'baptism';

-- Suggested resource behavior for services normally performed away from the main church.
UPDATE service_types
SET booking_buffer_before_minutes = 30,
    booking_buffer_minutes = 30,
    requires_priest = 1
WHERE service_key = 'funeral';

UPDATE service_types
SET uses_main_church = 0,
    requires_priest = 1
WHERE service_key IN ('house_blessing', 'vehicle_blessing', 'counseling');

INSERT INTO system_settings (setting_key, setting_value)
SELECT 'priest_booking_capacity', '2'
WHERE NOT EXISTS (SELECT 1 FROM system_settings WHERE setting_key = 'priest_booking_capacity');

-- IMPORTANT:
-- Configure special_start_time / special_end_time for Wedding and Baptism in the
-- staff Service Configuration screen before enabling public special time slots.
