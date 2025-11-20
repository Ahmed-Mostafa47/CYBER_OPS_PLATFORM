-- Database create + select
CREATE DATABASE IF NOT EXISTS ctf_platformlabs
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE ctf_platform;

-- ====================================================
-- Tables (InnoDB, utf8mb4). Assumes MySQL 5.7+ for JSON.
-- ====================================================

CREATE TABLE users (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  profile_meta JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  role_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL,
  description VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  permission_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) UNIQUE NOT NULL,
  description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_types (
  labtype_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL,
  description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (role_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  assigned_by INT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE labs (
  lab_id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  labtype_id INT NULL,
  difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
  points_total INT DEFAULT 0,
  created_by INT NULL,
  is_published TINYINT(1) DEFAULT 0,
  visibility ENUM('public','private','unlisted') DEFAULT 'private',
  docker_image VARCHAR(255),
  reset_interval INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE challenges (
  challenge_id INT PRIMARY KEY AUTO_INCREMENT,
  lab_id INT NULL,
  created_by INT NULL,
  title VARCHAR(255) NOT NULL,
  statement TEXT,
  order_index INT DEFAULT 0,
  max_score INT DEFAULT 0,
  difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
  whitebox_files_ref JSON,
  time_limit INT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testcases (
  testcase_id INT PRIMARY KEY AUTO_INCREMENT,
  challenge_id INT NULL,
  secret_flag_hash VARCHAR(255),
  secret_flag_plain TEXT,
  points INT DEFAULT 0,
  active TINYINT(1) DEFAULT 1,
  type ENUM('flag_match','http_response_check','script_run') DEFAULT 'flag_match',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hints (
  hint_id INT PRIMARY KEY AUTO_INCREMENT,
  challenge_id INT NULL,
  text TEXT NOT NULL,
  penalty_points INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE file_resources (
  file_id INT PRIMARY KEY AUTO_INCREMENT,
  owner_id INT NULL,
  lab_id INT NULL,
  filename VARCHAR(255) NOT NULL,
  path VARCHAR(1024) NOT NULL,
  mime_type VARCHAR(100),
  size BIGINT,
  checksum VARCHAR(128),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  access_level ENUM('public','private','restricted') DEFAULT 'private'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_instances (
  instance_id INT PRIMARY KEY AUTO_INCREMENT,
  lab_id INT NULL,
  user_id INT NULL,
  container_id VARCHAR(255),
  start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  end_time TIMESTAMP NULL DEFAULT NULL,
  status ENUM('running','stopped','crashed','expired') DEFAULT 'running',
  resources JSON,
  dynamic_flag VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submissions (
  submission_id INT PRIMARY KEY AUTO_INCREMENT,
  instance_id INT NULL,
  user_id INT NULL,
  challenge_id INT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  type ENUM('flag','report','poc','exploit') DEFAULT 'flag',
  payload_text TEXT,
  content_url VARCHAR(1024),
  auto_score INT,
  manual_score INT,
  final_score INT,
  status ENUM('pending','graded','rejected','needs_review') DEFAULT 'pending',
  reviewer_id INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submission_files (
  submission_id INT NOT NULL,
  file_id INT NOT NULL,
  PRIMARY KEY (submission_id, file_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE findings (
  finding_id INT PRIMARY KEY AUTO_INCREMENT,
  submission_id INT NULL,
  reviewer_id INT NULL,
  title VARCHAR(255),
  vuln_type VARCHAR(100),
  severity ENUM('low','medium','high','critical') DEFAULT 'low',
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  description TEXT,
  evidence TEXT,
  score_adjustment INT DEFAULT 0,
  poc TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  comment_id INT PRIMARY KEY AUTO_INCREMENT,
  submission_id INT NULL,
  challenge_id INT NULL,
  user_id INT NULL,
  text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leaderboard (
  user_id INT PRIMARY KEY,
  total_points INT DEFAULT 0,
  last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULL,
  action VARCHAR(200) NOT NULL,
  target_type VARCHAR(100),
  target_id INT NULL,
  ip_address VARCHAR(45),
  details JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attempt_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULL,
  challenge_id INT NULL,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  success TINYINT(1) DEFAULT 0,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  metadata JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blocks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULL,
  ip_address VARCHAR(45),
  challenge_id INT NULL,
  reason VARCHAR(255),
  expires_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Foreign keys (added after tables creation to avoid order issues)
-- ====================================================

ALTER TABLE role_permissions 
  ADD CONSTRAINT fk_roleperm_role FOREIGN KEY (role_id) REFERENCES roles (role_id),
  ADD CONSTRAINT fk_roleperm_perm FOREIGN KEY (permission_id) REFERENCES permissions (permission_id);

ALTER TABLE user_roles 
  ADD CONSTRAINT fk_userroles_user FOREIGN KEY (user_id) REFERENCES users (user_id),
  ADD CONSTRAINT fk_userroles_role FOREIGN KEY (role_id) REFERENCES roles (role_id),
  ADD CONSTRAINT fk_userroles_assignedby FOREIGN KEY (assigned_by) REFERENCES users (user_id);

ALTER TABLE labs 
  ADD CONSTRAINT fk_labs_labtype FOREIGN KEY (labtype_id) REFERENCES lab_types (labtype_id),
  ADD CONSTRAINT fk_labs_createdby FOREIGN KEY (created_by) REFERENCES users (user_id);

ALTER TABLE challenges 
  ADD CONSTRAINT fk_challenges_lab FOREIGN KEY (lab_id) REFERENCES labs (lab_id),
  ADD CONSTRAINT fk_challenges_createdby FOREIGN KEY (created_by) REFERENCES users (user_id);

ALTER TABLE testcases 
  ADD CONSTRAINT fk_testcases_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (challenge_id);

ALTER TABLE hints 
  ADD CONSTRAINT fk_hints_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (challenge_id);

ALTER TABLE file_resources 
  ADD CONSTRAINT fk_files_owner FOREIGN KEY (owner_id) REFERENCES users (user_id),
  ADD CONSTRAINT fk_files_lab FOREIGN KEY (lab_id) REFERENCES labs (lab_id);

ALTER TABLE lab_instances 
  ADD CONSTRAINT fk_instances_lab FOREIGN KEY (lab_id) REFERENCES labs (lab_id),
  ADD CONSTRAINT fk_instances_user FOREIGN KEY (user_id) REFERENCES users (user_id);

ALTER TABLE submissions 
  ADD CONSTRAINT fk_submissions_instance FOREIGN KEY (instance_id) REFERENCES lab_instances (instance_id),
  ADD CONSTRAINT fk_submissions_user FOREIGN KEY (user_id) REFERENCES users (user_id),
  ADD CONSTRAINT fk_submissions_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (challenge_id),
  ADD CONSTRAINT fk_submissions_reviewer FOREIGN KEY (reviewer_id) REFERENCES users (user_id);

ALTER TABLE submission_files 
  ADD CONSTRAINT fk_subfiles_submission FOREIGN KEY (submission_id) REFERENCES submissions (submission_id),
  ADD CONSTRAINT fk_subfiles_file FOREIGN KEY (file_id) REFERENCES file_resources (file_id);

ALTER TABLE findings 
  ADD CONSTRAINT fk_findings_submission FOREIGN KEY (submission_id) REFERENCES submissions (submission_id),
  ADD CONSTRAINT fk_findings_reviewer FOREIGN KEY (reviewer_id) REFERENCES users (user_id);

ALTER TABLE comments 
  ADD CONSTRAINT fk_comments_submission FOREIGN KEY (submission_id) REFERENCES submissions (submission_id),
  ADD CONSTRAINT fk_comments_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (challenge_id),
  ADD CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (user_id);

ALTER TABLE leaderboard 
  ADD CONSTRAINT fk_leaderboard_user FOREIGN KEY (user_id) REFERENCES users (user_id);

ALTER TABLE audit_logs 
  ADD CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (user_id);

ALTER TABLE attempt_logs 
  ADD CONSTRAINT fk_attempts_user FOREIGN KEY (user_id) REFERENCES users (user_id),
  ADD CONSTRAINT fk_attempts_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (challenge_id);

ALTER TABLE blocks 
  ADD CONSTRAINT fk_blocks_user FOREIGN KEY (user_id) REFERENCES users (user_id),
  ADD CONSTRAINT fk_blocks_challenge FOREIGN KEY (challenge_id) REFERENCES challenges (challenge_id);
