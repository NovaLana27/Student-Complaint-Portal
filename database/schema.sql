CREATE DATABASE IF NOT EXISTS ru_complaint_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ru_complaint_db;

CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  faculty VARCHAR(120) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  email VARCHAR(150) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  unit_id INT NOT NULL,
  default_priority VARCHAR(10) NOT NULL DEFAULT 'medium',
  CONSTRAINT fk_categories_unit FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  matric_no VARCHAR(30) NULL UNIQUE,
  level INT NULL,
  department_id INT NULL,
  unit_id INT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT 'student',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created DATETIME NULL,
  modified DATETIME NULL,
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS complaints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reference_no VARCHAR(30) NULL UNIQUE,
  student_id INT NOT NULL,
  category_id INT NOT NULL,
  assigned_to INT NULL,
  description TEXT NOT NULL,
  priority VARCHAR(10) NOT NULL DEFAULT 'medium',
  status VARCHAR(20) NOT NULL DEFAULT 'Submitted',
  is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
  resolved_at DATETIME NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  INDEX idx_complaints_status (status),
  INDEX idx_complaints_student (student_id),
  INDEX idx_complaints_assigned (assigned_to),
  CONSTRAINT fk_complaints_student FOREIGN KEY (student_id) REFERENCES users(id),
  CONSTRAINT fk_complaints_category FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_complaints_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  complaint_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NULL,
  size_bytes INT NULL,
  created DATETIME NULL,
  CONSTRAINT fk_attachments_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  complaint_id INT NOT NULL,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created DATETIME NULL,
  CONSTRAINT fk_responses_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
  CONSTRAINT fk_responses_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS status_histories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  complaint_id INT NOT NULL,
  old_status VARCHAR(20) NULL,
  new_status VARCHAR(20) NOT NULL,
  changed_by INT NULL,
  note VARCHAR(500) NULL,
  created DATETIME NULL,
  CONSTRAINT fk_history_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
  CONSTRAINT fk_history_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  complaint_id INT NULL,
  channel VARCHAR(10) NOT NULL DEFAULT 'in-app',
  message VARCHAR(500) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created DATETIME NULL,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_notifications_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(60) NOT NULL,
  entity VARCHAR(60) NULL,
  entity_id INT NULL,
  ip_address VARCHAR(45) NULL,
  created DATETIME NULL,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO departments (id, name, faculty) VALUES
 (1,'Computer Science','Natural Sciences'),
 (2,'Software Engineering','Natural Sciences'),
 (3,'Information Technology','Natural Sciences'),
 (4,'Cyber Security','Natural Sciences'),
 (5,'Accounting','Management Sciences'),
 (6,'Business Administration','Management Sciences'),
 (7,'Law','Law'),
 (8,'Nursing','Basic Medical and Health Sciences'),
 (9,'Mass Communication','Humanities');

INSERT IGNORE INTO units (id, name, email) VALUES
 (1,'Students Affairs Office','studentaffairs@example.edu'),
 (2,'ICT Support','ict@example.edu'),
 (3,'Bursary','bursary@example.edu'),
 (4,'Academic Affairs','academics@example.edu'),
 (5,'Hostel Management','hostels@example.edu'),
 (6,'Security Unit','security@example.edu');

INSERT IGNORE INTO categories (id, name, unit_id, default_priority) VALUES
 (1,'Academic',4,'medium'),
 (2,'Hostel and Accommodation',5,'medium'),
 (3,'Fees and Payments',3,'medium'),
 (4,'ICT and Network',2,'medium'),
 (5,'Lecturer or Staff Conduct',1,'high'),
 (6,'Security',6,'high'),
 (7,'Health and Welfare',1,'medium'),
 (8,'Other',1,'low');