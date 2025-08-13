CREATE TABLE category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE joke (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  title VARCHAR(180) NULL,
  body_text TEXT NOT NULL,
  language CHAR(2) NOT NULL DEFAULT 'fr',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  nsfw TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_joke_category (category_id),
  INDEX idx_joke_active (is_active),
  INDEX idx_joke_lang (language),
  CONSTRAINT fk_joke_category
    FOREIGN KEY (category_id) REFERENCES category(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE purchase (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  recipient_email VARCHAR(180) NOT NULL,
  price_cents INT NOT NULL DEFAULT 99,
  currency CHAR(3) NOT NULL DEFAULT 'EUR',
  status ENUM('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  stripe_checkout_session_id VARCHAR(255) NULL,
  stripe_payment_intent_id VARCHAR(255) NULL,
  joke_id INT NULL,
  joke_snapshot TEXT NOT NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_purchase_user (user_id),
  INDEX idx_purchase_status (status),
  INDEX idx_purchase_sent_at (sent_at),
  CONSTRAINT fk_purchase_user
    FOREIGN KEY (user_id) REFERENCES user(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT fk_purchase_joke
    FOREIGN KEY (joke_id) REFERENCES joke(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
