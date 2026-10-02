-- Sta. Monica Parish Connect
-- Sacramental record draft / verification workflow
-- Date: 2026-09-25
--
-- Existing sacramental records remain VERIFIED. New booking-generated or
-- manually encoded records can enter DRAFT until reviewed by another staff user.

SET @schema_name := DATABASE();

SET @has_record_status := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND column_name='record_status'
);
SET @sql := IF(
  @has_record_status=0,
  "ALTER TABLE sacramental_records ADD COLUMN record_status ENUM('draft','verified') NOT NULL DEFAULT 'verified' AFTER scanned_document",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_source_type := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND column_name='source_type'
);
SET @sql := IF(
  @has_source_type=0,
  "ALTER TABLE sacramental_records ADD COLUMN source_type ENUM('manual','booking','legacy_import') NOT NULL DEFAULT 'manual' AFTER record_status",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_updated_by := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND column_name='updated_by'
);
SET @sql := IF(
  @has_updated_by=0,
  "ALTER TABLE sacramental_records ADD COLUMN updated_by INT UNSIGNED DEFAULT NULL AFTER created_by, ADD CONSTRAINT fk_sacramental_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_verified_by := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND column_name='verified_by'
);
SET @sql := IF(
  @has_verified_by=0,
  "ALTER TABLE sacramental_records ADD COLUMN verified_by INT UNSIGNED DEFAULT NULL AFTER updated_by, ADD COLUMN verified_at DATETIME DEFAULT NULL AFTER verified_by, ADD CONSTRAINT fk_sacramental_verified_by FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_status_index := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND index_name='idx_sacramental_status'
);
SET @sql := IF(
  @has_status_index=0,
  "ALTER TABLE sacramental_records ADD INDEX idx_sacramental_status (record_status, record_type, sacrament_date)",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_booking_unique := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND index_name='uq_sacramental_booking'
);
SET @duplicate_booking_links := (
  SELECT COUNT(*) FROM (
    SELECT booking_id
    FROM sacramental_records
    WHERE booking_id IS NOT NULL
    GROUP BY booking_id
    HAVING COUNT(*) > 1
  ) duplicate_links
);
SET @sql := IF(
  @has_booking_unique=0 AND @duplicate_booking_links=0,
  "ALTER TABLE sacramental_records ADD UNIQUE KEY uq_sacramental_booking (booking_id)",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
