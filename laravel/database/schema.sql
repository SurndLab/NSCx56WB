CREATE TABLE publishers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  publisher_name VARCHAR(255) NOT NULL,
  publisher_address VARCHAR(255) NOT NULL,
  publisher_phone VARCHAR(32) NOT NULL,
  publisher_isbn_code VARCHAR(12) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_publishers_isbn_code (publisher_isbn_code),
  KEY idx_publishers_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE publisher_contacts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  publisher_id BIGINT UNSIGNED NOT NULL,
  contact_name VARCHAR(255) NOT NULL,
  contact_phone VARCHAR(32) NOT NULL,
  contact_email VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_contacts_publisher_id (publisher_id),
  CONSTRAINT fk_contacts_publisher
    FOREIGN KEY (publisher_id) REFERENCES publishers(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  publisher_id BIGINT UNSIGNED NULL,
  username VARCHAR(64) NOT NULL,
  password VARCHAR(255) NOT NULL,
  display_name VARCHAR(255) NOT NULL,
  role ENUM('super_admin','publisher_admin') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_users_username (username),
  KEY idx_users_is_active (is_active),
  KEY idx_users_publisher_role (publisher_id, role),
  CONSTRAINT fk_users_publisher
    FOREIGN KEY (publisher_id) REFERENCES publishers(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE books (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  publisher_id BIGINT UNSIGNED NOT NULL,
  book_name VARCHAR(255) NOT NULL,
  book_description TEXT NOT NULL,
  book_author VARCHAR(255) NOT NULL,
  isbn13_digits CHAR(13) NOT NULL,
  isbn13_hyphenated VARCHAR(32) NOT NULL,
  isbn_prefix CHAR(3) NOT NULL,
  registration_group VARCHAR(8) NOT NULL,
  publisher_code VARCHAR(12) NOT NULL,
  publication_code VARCHAR(16) NOT NULL,
  check_digit CHAR(1) NOT NULL,
  is_hidden TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_books_isbn13_digits (isbn13_digits),
  UNIQUE KEY uq_books_isbn13_hyphenated (isbn13_hyphenated),
  KEY idx_books_isbn13_digits (isbn13_digits),
  KEY idx_books_isbn13_hyphenated (isbn13_hyphenated),
  KEY idx_books_is_hidden (is_hidden),
  KEY idx_books_publisher_hidden (publisher_id, is_hidden),
  CONSTRAINT fk_books_publisher
    FOREIGN KEY (publisher_id) REFERENCES publishers(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE book_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  KEY idx_book_images_book_id (book_id),
  UNIQUE KEY uq_book_images_book_sort (book_id, sort_order),
  CONSTRAINT fk_book_images_book
    FOREIGN KEY (book_id) REFERENCES books(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
