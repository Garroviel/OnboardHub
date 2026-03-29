-- OnboardHub MVP (MySQL)
-- Engine: InnoDB / Charset: utf8mb4

CREATE TABLE company (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(128) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_company_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE team (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_team_company_name (company_id, name),
  KEY idx_team_company (company_id),
  CONSTRAINT fk_team_company
    FOREIGN KEY (company_id) REFERENCES company(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  team_id INT UNSIGNED NULL,
  manager_id INT UNSIGNED NULL,

  email VARCHAR(255) NOT NULL,
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(120) NOT NULL,

  role ENUM('COLLABORATOR','MANAGER','HR','ADMIN') NOT NULL,
  provider VARCHAR(50) NOT NULL,
  external_id VARCHAR(128) NOT NULL,

  hired_at DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_users_company_email (company_id, email),
  UNIQUE KEY uq_users_provider_external (provider, external_id),

  KEY idx_users_company (company_id),
  KEY idx_users_team (team_id),
  KEY idx_users_manager (manager_id),

  CONSTRAINT fk_users_company
    FOREIGN KEY (company_id) REFERENCES company(id)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT fk_users_team
    FOREIGN KEY (team_id) REFERENCES team(id)
    ON DELETE SET NULL ON UPDATE CASCADE,

  CONSTRAINT fk_users_manager
    FOREIGN KEY (manager_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE onboarding (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,

  start_date DATE NOT NULL,
  due_date DATE NULL,

  status ENUM('ACTIVE','COMPLETED','ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,

  -- Future release (templates) : ajouter FK vers onboarding_template(id)
  template_id INT UNSIGNED NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  KEY idx_onboarding_user (user_id),
  KEY idx_onboarding_status (status),

  CONSTRAINT fk_onboarding_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE onboarding_task (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  onboarding_id INT UNSIGNED NOT NULL,
  assigned_by_user_id INT UNSIGNED NULL,

  title VARCHAR(255) NOT NULL,
  description TEXT NULL,

  status ENUM('TODO','IN_PROGRESS','DONE') NOT NULL DEFAULT 'TODO',
  priority SMALLINT UNSIGNED NOT NULL DEFAULT 3,

  due_date DATE NULL,
  completed_at DATETIME NULL,

  position INT UNSIGNED NOT NULL DEFAULT 0,

  KEY idx_otask_onboarding (onboarding_id),
  KEY idx_otask_status (status),
  KEY idx_otask_due (due_date),
  KEY idx_otask_assigned_by (assigned_by_user_id),

  CONSTRAINT fk_otask_onboarding
    FOREIGN KEY (onboarding_id) REFERENCES onboarding(id)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT fk_otask_assigned_by
    FOREIGN KEY (assigned_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE onboarding_training (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  onboarding_id INT UNSIGNED NOT NULL,

  title VARCHAR(255) NOT NULL,
  url VARCHAR(2048) NULL,

  status ENUM('TODO','IN_PROGRESS','DONE') NOT NULL DEFAULT 'TODO',
  completed_at DATETIME NULL,

  position INT UNSIGNED NOT NULL DEFAULT 0,

  KEY idx_otraining_onboarding (onboarding_id),
  KEY idx_otraining_status (status),

  CONSTRAINT fk_otraining_onboarding
    FOREIGN KEY (onboarding_id) REFERENCES onboarding(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE onboarding_software (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  onboarding_id INT UNSIGNED NOT NULL,

  name VARCHAR(255) NOT NULL,
  install_url VARCHAR(2048) NULL,

  status ENUM('TODO','DONE') NOT NULL DEFAULT 'TODO',
  completed_at DATETIME NULL,

  position INT UNSIGNED NOT NULL DEFAULT 0,

  KEY idx_osoftware_onboarding (onboarding_id),
  KEY idx_osoftware_status (status),

  CONSTRAINT fk_osoftware_onboarding
    FOREIGN KEY (onboarding_id) REFERENCES onboarding(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tech_article (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  source VARCHAR(32) NOT NULL,         -- ex: 'HN'
  external_id VARCHAR(64) NOT NULL,    -- ex: id HackerNews

  title VARCHAR(512) NOT NULL,
  url VARCHAR(2048) NOT NULL,

  author VARCHAR(255) NULL,
  score INT NULL,

  published_at DATETIME NULL,
  fetched_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_article_source_external (source, external_id),
  KEY idx_article_fetched_at (fetched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_tech_state (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  article_id INT UNSIGNED NOT NULL,

  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME NULL,

  is_saved TINYINT(1) NOT NULL DEFAULT 0,
  saved_at DATETIME NULL,

  UNIQUE KEY uq_state_user_article (user_id, article_id),
  KEY idx_state_user (user_id),
  KEY idx_state_article (article_id),

  CONSTRAINT fk_state_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT fk_state_article
    FOREIGN KEY (article_id) REFERENCES tech_article(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;