-- Sta. Monica Parish
-- Chapels, chapel Mass schedules, GSK clusters and leadership directory
-- Safe for shared hosting / InfinityFree: no stored procedures.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS chapels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    patron_saint VARCHAR(180) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    barangay VARCHAR(120) DEFAULT NULL,
    location_notes VARCHAR(255) DEFAULT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    contact_number VARCHAR(50) DEFAULT NULL,
    feast_date VARCHAR(80) DEFAULT NULL,
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_chapel_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapel_mass_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chapel_id INT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL,
    mass_time TIME NOT NULL,
    title VARCHAR(150) DEFAULT 'Holy Mass',
    language VARCHAR(80) DEFAULT NULL,
    recurrence_note VARCHAR(180) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_chapel_mass_chapel FOREIGN KEY (chapel_id) REFERENCES chapels(id) ON DELETE CASCADE,
    INDEX idx_chapel_mass_order (chapel_id, day_of_week, mass_time, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gsk_clusters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chapel_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    code VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    coverage_area VARCHAR(255) DEFAULT NULL,
    meeting_schedule VARCHAR(180) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_gsk_cluster_chapel FOREIGN KEY (chapel_id) REFERENCES chapels(id) ON DELETE CASCADE,
    INDEX idx_gsk_cluster_order (chapel_id, is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS church_officials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scope_type ENUM('parish','chapel','cluster') NOT NULL DEFAULT 'parish',
    chapel_id INT UNSIGNED DEFAULT NULL,
    cluster_id INT UNSIGNED DEFAULT NULL,
    full_name VARCHAR(180) NOT NULL,
    position_title VARCHAR(180) NOT NULL,
    committee_area VARCHAR(120) DEFAULT NULL,
    contact_number VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    bio VARCHAR(500) DEFAULT NULL,
    term_start DATE DEFAULT NULL,
    term_end DATE DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_official_chapel FOREIGN KEY (chapel_id) REFERENCES chapels(id) ON DELETE CASCADE,
    CONSTRAINT fk_official_cluster FOREIGN KEY (cluster_id) REFERENCES gsk_clusters(id) ON DELETE CASCADE,
    INDEX idx_official_scope (scope_type, chapel_id, cluster_id, is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
