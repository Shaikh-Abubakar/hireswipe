USE hireswipe;

CREATE TABLE IF NOT EXISTS candidate_interactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  employer_id BIGINT UNSIGNED NOT NULL,
  seeker_user_id BIGINT UNSIGNED NOT NULL,
  action ENUM('interest','rejected') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_employer_candidate (employer_id, seeker_user_id),
  KEY idx_interactions_candidate (seeker_user_id),
  CONSTRAINT fk_interaction_employer FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_interaction_seeker FOREIGN KEY (seeker_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  message VARCHAR(1000) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notifications_user_read_created (user_id, is_read, created_at),
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
