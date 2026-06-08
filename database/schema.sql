-- SmartHire Pro Database Schema
CREATE DATABASE IF NOT EXISTS smarthire_pro
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smarthire_pro;

CREATE TABLE roles (
    id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (name, slug) VALUES
('Admin', 'admin'),
('Recruiter', 'recruiter'),
('Job Seeker', 'jobseeker');

CREATE TABLE users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role_id TINYINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    status ENUM('active', 'blocked', 'pending') DEFAULT 'pending',
    email_verified TINYINT(1) DEFAULT 0,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_users_role (role_id),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE job_seeker_profiles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    photo VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    location VARCHAR(150) NULL,
    education TEXT NULL,
    skills TEXT NULL,
    experience TEXT NULL,
    resume_path VARCHAR(255) NULL,
    profile_strength TINYINT UNSIGNED DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE companies (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    recruiter_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    logo VARCHAR(255) NULL,
    description TEXT NULL,
    website VARCHAR(255) NULL,
    location VARCHAR(150) NULL,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_companies_approval (approval_status)
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    posted_by INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NULL,
    location VARCHAR(150) NOT NULL,
    job_type ENUM('full-time', 'part-time', 'contract', 'internship', 'remote') NOT NULL,
    experience_level VARCHAR(50) NULL,
    salary_min DECIMAL(12,2) NULL,
    salary_max DECIMAL(12,2) NULL,
    status ENUM('draft', 'active', 'closed') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_jobs_location (location),
    INDEX idx_jobs_type (job_type),
    INDEX idx_jobs_status (status),
    FULLTEXT idx_jobs_search (title, description)
) ENGINE=InnoDB;

CREATE TABLE applications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    job_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    status ENUM(
        'applied',
        'under_review',
        'shortlisted',
        'interview_scheduled',
        'selected',
        'rejected'
    ) DEFAULT 'applied',
    cover_letter TEXT NULL,
    notes TEXT NULL,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_application (job_id, user_id),
    INDEX idx_applications_status (status)
) ENGINE=InnoDB;

CREATE TABLE saved_jobs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY uk_saved (user_id, job_id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE otp_verifications (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    purpose ENUM('email_verify', 'password_reset') NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_otp_user_purpose (user_id, purpose)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE admin_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id INT UNSIGNED NULL,
    details TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SmartHire Pro — Redesign Extensions
-- ============================================================

-- Interview scheduling on applications
ALTER TABLE applications ADD COLUMN interview_date DATETIME NULL;
ALTER TABLE applications ADD COLUMN interview_link VARCHAR(500) NULL;
ALTER TABLE applications ADD COLUMN interview_instructions TEXT NULL;
ALTER TABLE applications ADD COLUMN interview_type ENUM('google_meet','zoom','teams','in_person') NULL;

-- Admin SMTP settings table
CREATE TABLE IF NOT EXISTS app_settings (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rate limiting for login/OTP
CREATE TABLE IF NOT EXISTS rate_limits (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  identifier VARCHAR(255) NOT NULL,
  action VARCHAR(50) NOT NULL,
  attempts TINYINT UNSIGNED DEFAULT 1,
  last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rl (identifier, action)
) ENGINE=InnoDB;
