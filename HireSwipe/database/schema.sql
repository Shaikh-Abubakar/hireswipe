CREATE DATABASE IF NOT EXISTS hireswipe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hireswipe;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('employer','seeker') NULL,
  onboarding_complete TINYINT(1) NOT NULL DEFAULT 0,
  phone_verified_at DATETIME NULL,
  email_verified_at DATETIME NULL,
  status ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_phone (phone),
  KEY idx_users_role_onboarding (role,onboarding_complete),
  KEY idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employer_profiles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  company_name VARCHAR(180) NOT NULL,
  location VARCHAR(180) NOT NULL,
  industry VARCHAR(120) NOT NULL,
  position VARCHAR(160) NOT NULL,
  employment_type ENUM('full_time','part_time','contract','internship','temporary') NOT NULL,
  salary_min DECIMAL(12,2) NULL,
  salary_max DECIMAL(12,2) NULL,
  job_details TEXT NOT NULL,
  logo_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_employer_user (user_id),
  KEY idx_employer_industry (industry),
  KEY idx_employer_location (location),
  CONSTRAINT fk_employer_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_employer_salary CHECK (salary_min IS NULL OR salary_min >= 0),
  CONSTRAINT chk_employer_salary_range CHECK (salary_max IS NULL OR salary_min IS NULL OR salary_max >= salary_min)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seeker_profiles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  age TINYINT UNSIGNED NOT NULL,
  location VARCHAR(180) NOT NULL,
  nationality VARCHAR(100) NOT NULL,
  qualification VARCHAR(180) NOT NULL,
  experience_years DECIMAL(4,1) NOT NULL DEFAULT 0,
  expected_salary DECIMAL(12,2) NULL,
  employment_type ENUM('full_time','part_time','contract','internship','temporary') NOT NULL,
  industries VARCHAR(500) NOT NULL,
  picture_path VARCHAR(255) NULL,
  cv_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seeker_user (user_id),
  KEY idx_seeker_location (location),
  KEY idx_seeker_employment (employment_type),
  KEY idx_seeker_experience (experience_years),
  CONSTRAINT fk_seeker_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_seeker_age CHECK (age BETWEEN 16 AND 100),
  CONSTRAINT chk_seeker_experience CHECK (experience_years >= 0),
  CONSTRAINT chk_seeker_salary CHECK (expected_salary IS NULL OR expected_salary >= 0)
) ENGINE=InnoDB;
