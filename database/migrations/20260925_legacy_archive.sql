-- Sta. Monica Parish Connect
-- Legacy sacramental archive digitization
-- Date: 2026-09-25
--
-- Run after 20260925_sacramental_record_workflow.sql.

SET @schema_name := DATABASE();

CREATE TABLE IF NOT EXISTS archive_batches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    record_type ENUM('baptism','confirmation','communion','marriage','funeral') DEFAULT NULL,
    source_label VARCHAR(200) DEFAULT NULL,
    physical_location VARCHAR(200) DEFAULT NULL,
    year_from SMALLINT UNSIGNED DEFAULT NULL,
    year_to SMALLINT UNSIGNED DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('encoding','review','completed') NOT NULL DEFAULT 'encoding',
    created_by INT UNSIGNED DEFAULT NULL,
    completed_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_archive_batch_status (status, record_type, year_from, year_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS archive_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id INT UNSIGNED NOT NULL,
    registry_book VARCHAR(50) DEFAULT NULL,
    registry_page VARCHAR(50) DEFAULT NULL,
    page_label VARCHAR(120) DEFAULT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES archive_batches(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_archive_page_lookup (batch_id, registry_book, registry_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @has_archive_batch_id := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND column_name='archive_batch_id'
);
SET @sql := IF(
  @has_archive_batch_id=0,
  "ALTER TABLE sacramental_records ADD COLUMN archive_batch_id INT UNSIGNED DEFAULT NULL AFTER source_type, ADD CONSTRAINT fk_sacramental_archive_batch FOREIGN KEY (archive_batch_id) REFERENCES archive_batches(id) ON DELETE SET NULL",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_archive_page_id := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@schema_name AND table_name='sacramental_records' AND column_name='archive_page_id'
);
SET @sql := IF(
  @has_archive_page_id=0,
  "ALTER TABLE sacramental_records ADD COLUMN archive_page_id INT UNSIGNED DEFAULT NULL AFTER archive_batch_id, ADD CONSTRAINT fk_sacramental_archive_page FOREIGN KEY (archive_page_id) REFERENCES archive_pages(id) ON DELETE SET NULL",
  "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
