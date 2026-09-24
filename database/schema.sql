-- ============================================================================
-- Sta. Monica Parish Church - Digital Parish Management & Online Services
-- Database Schema (MySQL / MariaDB)
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS stamonica_parish CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stamonica_parish;

-- ----------------------------------------------------------------------------
-- 1. USERS & ROLES
-- ----------------------------------------------------------------------------

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(30) NOT NULL UNIQUE,   -- admin, secretary, priest, parishioner
    role_name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO roles (role_key, role_name) VALUES
('admin', 'Administrator'),
('secretary', 'Parish Secretary'),
('priest', 'Priest'),
('parishioner', 'Parishioner');

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile_number VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- Extra profile info specific to priests
CREATE TABLE priest_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    title VARCHAR(50) DEFAULT 'Rev. Fr.',
    position VARCHAR(100) DEFAULT NULL, -- Parish Priest, Parochial Vicar, Assistant Priest
    bio TEXT DEFAULT NULL,
    is_public BOOLEAN NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Priest unavailable / blocked dates
CREATE TABLE priest_unavailability (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    priest_id INT UNSIGNED NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (priest_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 2. MASS SCHEDULES
-- ----------------------------------------------------------------------------

CREATE TABLE mass_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL DEFAULT 'Holy Mass',
    day_of_week TINYINT UNSIGNED DEFAULT NULL, -- 0=Sun..6=Sat, NULL if specific_date used
    specific_date DATE DEFAULT NULL,           -- for special/holy day schedules
    mass_time TIME NOT NULL,
    language VARCHAR(50) DEFAULT 'Filipino/English',
    location VARCHAR(150) DEFAULT 'Main Church',
    presider_id INT UNSIGNED DEFAULT NULL,
    schedule_type ENUM('regular','first_friday','holy_day','fiesta','christmas','holy_week','simbang_gabi','special','memorial','healing') NOT NULL DEFAULT 'regular',
    is_override BOOLEAN NOT NULL DEFAULT 0,    -- true = overrides regular schedule for that date
    is_active BOOLEAN NOT NULL DEFAULT 1,
    notes VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (presider_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 3. SERVICE TYPES & REQUIREMENTS (configurable by Admin)
-- ----------------------------------------------------------------------------

CREATE TABLE service_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_key VARCHAR(50) NOT NULL UNIQUE, -- baptism, wedding, funeral, confirmation, blessing, counseling, mass_intention...
    name VARCHAR(150) NOT NULL,
    category ENUM('sacrament','blessing','service','intention') NOT NULL DEFAULT 'service',
    description TEXT DEFAULT NULL,
    base_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 60,
    uses_main_church BOOLEAN NOT NULL DEFAULT 1,
    allow_special_booking BOOLEAN NOT NULL DEFAULT 0,
    special_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    special_capacity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    special_start_time TIME DEFAULT NULL,
    special_end_time TIME DEFAULT NULL,
    slot_interval_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    booking_buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    booking_buffer_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    requires_priest BOOLEAN NOT NULL DEFAULT 1,
    min_advance_days SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    max_advance_days SMALLINT UNSIGNED NOT NULL DEFAULT 365,
    requires_schedule BOOLEAN NOT NULL DEFAULT 1,
    requires_approval_workflow BOOLEAN NOT NULL DEFAULT 0, -- e.g. wedding: interview -> priest review -> confirm
    icon VARCHAR(50) DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE service_schedule_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_type_id INT UNSIGNED NOT NULL,
    rule_name VARCHAR(150) NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL, -- 0=Sunday ... 6=Saturday
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
    FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE,
    INDEX idx_service_rule_active (service_type_id, is_active)
) ENGINE=InnoDB;

CREATE TABLE service_requirements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_type_id INT UNSIGNED NOT NULL,
    label VARCHAR(150) NOT NULL,       -- e.g. "Birth Certificate (PSA)"
    is_required BOOLEAN NOT NULL DEFAULT 1,
    accepts_file_upload BOOLEAN NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (service_type_id) REFERENCES service_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 4. SERVICE BOOKINGS (Baptism, Wedding, Funeral, Blessing, Counseling, etc.)
-- ----------------------------------------------------------------------------

CREATE TABLE service_bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(30) NOT NULL UNIQUE, -- e.g. SMC-BAP-2026-00042
    service_type_id INT UNSIGNED NOT NULL,
    booking_type ENUM('regular','special') NOT NULL DEFAULT 'special',
    schedule_rule_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED NOT NULL,           -- applicant/parishioner
    assigned_priest_id INT UNSIGNED DEFAULT NULL,
    preferred_date DATE DEFAULT NULL,
    alternative_date DATE DEFAULT NULL,
    confirmed_date DATETIME DEFAULT NULL,
    location VARCHAR(150) DEFAULT 'Main Church',
    status ENUM(
        'draft','submitted','under_review','missing_requirements','requirements_complete',
        'interview_processing','priest_review','awaiting_payment','payment_verification',
        'approved','scheduled','completed','cancelled','returned'
    ) NOT NULL DEFAULT 'submitted',
    fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    details JSON DEFAULT NULL,  -- flexible per-service fields (child info, sponsors, bride/groom info, deceased info, etc.)
    staff_notes TEXT DEFAULT NULL,
    rejection_reason VARCHAR(255) DEFAULT NULL,
    processed_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    FOREIGN KEY (schedule_rule_id) REFERENCES service_schedule_rules(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_priest_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_preferred_date (preferred_date),
    INDEX idx_booking_schedule (confirmed_date, status)
) ENGINE=InnoDB;

CREATE TABLE booking_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    requirement_id INT UNSIGNED DEFAULT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    verified BOOLEAN NOT NULL DEFAULT 0,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES service_bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_id) REFERENCES service_requirements(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE booking_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    from_status VARCHAR(30) DEFAULT NULL,
    to_status VARCHAR(30) NOT NULL,
    remarks VARCHAR(255) DEFAULT NULL,
    changed_by INT UNSIGNED DEFAULT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES service_bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 5. SACRAMENTAL RECORDS (registry, searchable)
-- ----------------------------------------------------------------------------

CREATE TABLE sacramental_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    record_type ENUM('baptism','confirmation','communion','marriage','funeral') NOT NULL,
    booking_id INT UNSIGNED DEFAULT NULL, -- link if it originated from an online booking
    full_name VARCHAR(200) NOT NULL,
    birth_date DATE DEFAULT NULL,
    sacrament_date DATE DEFAULT NULL,
    father_name VARCHAR(200) DEFAULT NULL,
    mother_name VARCHAR(200) DEFAULT NULL,
    spouse_name VARCHAR(200) DEFAULT NULL, -- for marriage records
    minister_name VARCHAR(150) DEFAULT NULL,
    registry_book VARCHAR(50) DEFAULT NULL,
    registry_page VARCHAR(50) DEFAULT NULL,
    registry_entry_no VARCHAR(50) DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    scanned_document VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES service_bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FULLTEXT KEY ft_search (full_name, father_name, mother_name)
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 6. CERTIFICATE REQUESTS
-- ----------------------------------------------------------------------------

CREATE TABLE certificate_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_code VARCHAR(30) NOT NULL UNIQUE, -- SMC-CERT-2026-00125
    user_id INT UNSIGNED NOT NULL,
    certificate_type ENUM('baptismal','confirmation','marriage','no_record','other') NOT NULL,
    record_id INT UNSIGNED DEFAULT NULL, -- matched sacramental_records row
    purpose VARCHAR(150) DEFAULT NULL,
    number_of_copies TINYINT UNSIGNED NOT NULL DEFAULT 1,
    fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    release_method ENUM('pickup','representative','courier','digital') NOT NULL DEFAULT 'pickup',
    status ENUM(
        'submitted','searching_record','record_found','no_record_found',
        'awaiting_payment','payment_verification','preparing','ready_for_release','released','cancelled'
    ) NOT NULL DEFAULT 'submitted',
    qr_code_token VARCHAR(64) DEFAULT NULL UNIQUE,
    certificate_file VARCHAR(255) DEFAULT NULL,
    processed_by INT UNSIGNED DEFAULT NULL,
    released_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (record_id) REFERENCES sacramental_records(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 7. PAYMENTS (GCash manual verification, extensible to gateway)
-- ----------------------------------------------------------------------------

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_code VARCHAR(30) NOT NULL UNIQUE, -- SMC-PAY-2026-00142
    user_id INT UNSIGNED NOT NULL,
    payable_type ENUM('service_booking','certificate_request','mass_intention','donation','event_registration') NOT NULL,
    payable_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('gcash','cash','bank_transfer','gateway') NOT NULL DEFAULT 'gcash',
    gcash_reference_no VARCHAR(50) DEFAULT NULL,
    proof_of_payment VARCHAR(255) DEFAULT NULL,
    status ENUM('awaiting_payment','submitted','payment_verified','rejected','refunded') NOT NULL DEFAULT 'awaiting_payment',
    verified_by INT UNSIGNED DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    receipt_no VARCHAR(30) DEFAULT NULL,
    remarks VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_payable (payable_type, payable_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 8. MASS INTENTIONS & PRAYER REQUESTS
-- ----------------------------------------------------------------------------

CREATE TABLE mass_intentions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    mass_schedule_id INT UNSIGNED DEFAULT NULL,
    mass_date DATE DEFAULT NULL,
    intention_type ENUM('thanksgiving','birthday','healing','special','safe_travel','souls_departed','anniversary','other') NOT NULL,
    offered_for VARCHAR(255) NOT NULL,
    requestor_name VARCHAR(150) NOT NULL,
    requestor_contact VARCHAR(100) DEFAULT NULL,
    status ENUM('pending','approved','ready_for_reading','completed','cancelled') NOT NULL DEFAULT 'pending',
    fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    processed_by INT UNSIGNED DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    ready_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (mass_schedule_id) REFERENCES mass_schedules(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mass_intention_occurrence (mass_date, mass_schedule_id, status)
) ENGINE=InnoDB;

CREATE TABLE prayer_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    requestor_name VARCHAR(150) DEFAULT NULL,
    is_confidential BOOLEAN NOT NULL DEFAULT 1,
    request_text TEXT NOT NULL,
    assigned_to ENUM('priest','prayer_ministry','organization','special_mass') DEFAULT NULL,
    status ENUM('new','acknowledged','prayed_for','closed') NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 9. ANNOUNCEMENTS, EVENTS, MINISTRIES, DONATIONS
-- ----------------------------------------------------------------------------

CREATE TABLE announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    body TEXT NOT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    category ENUM('notice','schedule_change','fiesta','activity','ministry','emergency','diocesan','holiday') NOT NULL DEFAULT 'notice',
    publish_date DATETIME NOT NULL,
    expiration_date DATETIME DEFAULT NULL,
    is_pinned BOOLEAN NOT NULL DEFAULT 0,
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL, -- feast, novena, procession, fiesta, youth, formation, catechism, outreach...
    season_key VARCHAR(50) DEFAULT NULL,
    season_year SMALLINT UNSIGNED DEFAULT NULL,
    is_seasonal BOOLEAN NOT NULL DEFAULT 0,
    highlight_on_home BOOLEAN NOT NULL DEFAULT 0,
    highlight_start DATE DEFAULT NULL,
    highlight_end DATE DEFAULT NULL,
    event_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    event_time TIME DEFAULT NULL,
    location VARCHAR(200) DEFAULT NULL,
    allow_registration BOOLEAN NOT NULL DEFAULT 0,
    registration_limit INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','published','cancelled','completed') NOT NULL DEFAULT 'published',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_event_season_year (season_key, season_year),
    INDEX idx_event_highlight (highlight_on_home, highlight_start, highlight_end)
) ENGINE=InnoDB;

CREATE TABLE event_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    full_name VARCHAR(150) NOT NULL,
    contact_number VARCHAR(30) DEFAULT NULL,
    status ENUM('registered','attended','cancelled') NOT NULL DEFAULT 'registered',
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE ministries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(170) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    contact_person VARCHAR(150) DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE ministry_interests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ministry_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    full_name VARCHAR(150) NOT NULL,
    contact_number VARCHAR(30) DEFAULT NULL,
    message VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','contacted','joined','declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ministry_id) REFERENCES ministries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE volunteers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    full_name VARCHAR(150) NOT NULL,
    contact_number VARCHAR(30) DEFAULT NULL,
    activity VARCHAR(200) DEFAULT NULL,
    ministry_id INT UNSIGNED DEFAULT NULL,
    skills VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','confirmed','completed') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (ministry_id) REFERENCES ministries(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE donation_campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    goal_amount DECIMAL(12,2) DEFAULT NULL,
    status ENUM('active','closed') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    donor_name VARCHAR(150) DEFAULT NULL,
    is_anonymous BOOLEAN NOT NULL DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL,
    message VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES donation_campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- 10. NOTIFICATIONS, WEBSITE CONTENT, SETTINGS, AUDIT
-- ----------------------------------------------------------------------------

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    message VARCHAR(500) NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read BOOLEAN NOT NULL DEFAULT 0,
    channel ENUM('system','email','sms') NOT NULL DEFAULT 'system',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE website_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(50) NOT NULL UNIQUE, -- about, history, contact
    title VARCHAR(200) NOT NULL,
    content LONGTEXT DEFAULT NULL,
    updated_by INT UNSIGNED DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO system_settings (setting_key, setting_value) VALUES
('parish_name', 'Sta. Monica Parish Church'),
('parish_address', ''),
('parish_contact', ''),
('gcash_qr_image', ''),
('gcash_account_name', ''),
('gcash_account_number', ''),
('priest_booking_capacity', '2'),
('mass_intention_cutoff_minutes', '30');

CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(150) NOT NULL,
    module VARCHAR(50) DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- SEED DATA
-- ----------------------------------------------------------------------------

-- Default admin: email admin@stamonicaparish.local / password: Admin@12345
-- (bcrypt hash generated for 'Admin@12345')
INSERT INTO users (role_id, first_name, last_name, email, password_hash, status, email_verified_at)
VALUES (1, 'Parish', 'Administrator', 'admin@stamonicaparish.local',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', NOW());

INSERT INTO service_types (service_key, name, category, description, base_fee, requires_schedule, requires_approval_workflow, icon, display_order) VALUES
('baptism', 'Baptism', 'sacrament', 'Sacrament of Baptism for infants and children.', 500.00, 1, 0, 'droplet', 1),
('wedding', 'Wedding', 'sacrament', 'Sacrament of Holy Matrimony.', 5000.00, 1, 1, 'rings', 2),
('funeral', 'Funeral / Burial Mass', 'sacrament', 'Funeral Mass, blessing and burial assistance.', 1500.00, 1, 0, 'cross', 3),
('confirmation', 'Confirmation', 'sacrament', 'Sacrament of Confirmation.', 500.00, 1, 0, 'sparkles', 4),
('house_blessing', 'House Blessing', 'blessing', 'Blessing of homes.', 300.00, 1, 0, 'home', 5),
('vehicle_blessing', 'Vehicle Blessing', 'blessing', 'Blessing of vehicles.', 300.00, 1, 0, 'car', 6),
('counseling', 'Pastoral Counseling', 'service', 'Appointment for pastoral counseling.', 0.00, 1, 0, 'chat', 7);

-- Availability defaults. Staff may change these in Service Configuration.
UPDATE service_types SET allow_special_booking = 1, special_fee = 9000.00, uses_main_church = 1, booking_buffer_before_minutes = 60, booking_buffer_minutes = 45, requires_priest = 1 WHERE service_key = 'wedding';
UPDATE service_types SET allow_special_booking = 1, special_fee = base_fee, special_capacity = 1, uses_main_church = 1, booking_buffer_before_minutes = 30, booking_buffer_minutes = 30, requires_priest = 1 WHERE service_key = 'baptism';
UPDATE service_types SET booking_buffer_before_minutes = 30, booking_buffer_minutes = 30, requires_priest = 1 WHERE service_key = 'funeral';
UPDATE service_types SET uses_main_church = 0, requires_priest = 1 WHERE service_key IN ('house_blessing','vehicle_blessing','counseling');

INSERT INTO service_schedule_rules (service_type_id, rule_name, day_of_week, week_numbers, start_time, fee_amount, capacity, is_active, display_order)
SELECT id, 'Free Wedding - 2nd & 4th Thursday', 4, '2,4', '06:00:00', 0.00, 1, 1, 10 FROM service_types WHERE service_key = 'wedding';

INSERT INTO service_schedule_rules (service_type_id, rule_name, day_of_week, week_numbers, start_time, fee_amount, capacity, is_active, display_order)
SELECT id, 'Free Baptism - 2nd & 4th Saturday', 6, '2,4', NULL, 0.00, 1, 1, 10 FROM service_types WHERE service_key = 'baptism';

INSERT INTO service_requirements (service_type_id, label, is_required, display_order) VALUES
(1, 'Child''s Birth Certificate (PSA)', 1, 1),
(1, 'Parents'' Marriage Certificate (if married)', 0, 2),
(1, 'Baptismal Sponsor(s) Information', 1, 3),
(2, 'Baptismal Certificate (Bride & Groom)', 1, 1),
(2, 'Confirmation Certificate (Bride & Groom)', 1, 2),
(2, 'CENOMAR / Marriage License', 1, 3),
(2, 'Pre-Cana / Marriage Seminar Certificate', 1, 4),
(3, 'Death Certificate', 1, 1);

INSERT INTO mass_schedules (title, day_of_week, mass_time, schedule_type) VALUES
('Daily Mass', 1, '06:00:00', 'regular'),
('Daily Mass', 2, '06:00:00', 'regular'),
('Daily Mass', 3, '06:00:00', 'regular'),
('Daily Mass', 4, '06:00:00', 'regular'),
('Daily Mass', 5, '06:00:00', 'regular'),
('Daily Mass', 6, '06:00:00', 'regular'),
('Sunday Mass', 0, '06:00:00', 'regular'),
('Sunday Mass', 0, '08:00:00', 'regular'),
('Sunday Mass', 0, '10:00:00', 'regular'),
('Sunday Mass', 0, '17:30:00', 'regular');

SET FOREIGN_KEY_CHECKS = 1;
