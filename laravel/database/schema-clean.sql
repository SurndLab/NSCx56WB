-- ============================================================
-- NSCx56WB 書籍管理系統 — 資料庫結構 (無資料)
-- MySQL 8.0+, InnoDB, utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `book_images`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `publisher_contacts`;
DROP TABLE IF EXISTS `publishers`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `migrations`;

-- -----------------------------------------------------------
-- 出版社
-- -----------------------------------------------------------
CREATE TABLE `publishers` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `publisher_name`      VARCHAR(255)    NOT NULL,
    `publisher_address`   VARCHAR(255)    NOT NULL,
    `publisher_phone`     VARCHAR(32)     NOT NULL,
    `publisher_isbn_code` VARCHAR(12)     NOT NULL,
    `is_active`           TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`          TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`          TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_publishers_isbn_code` (`publisher_isbn_code`),
    KEY `idx_publishers_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 出版社聯絡人
-- -----------------------------------------------------------
CREATE TABLE `publisher_contacts` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `publisher_id`  BIGINT UNSIGNED NOT NULL,
    `contact_name`  VARCHAR(255)    NOT NULL,
    `contact_phone` VARCHAR(32)     NOT NULL,
    `contact_email` VARCHAR(255)    NOT NULL,
    `created_at`    TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_publisher_contacts_publisher_id` (`publisher_id`),
    CONSTRAINT `fk_publisher_contacts_publisher`
        FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 使用者 (超級管理員 / 出版社管理員)
-- -----------------------------------------------------------
CREATE TABLE `users` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `publisher_id`   BIGINT UNSIGNED     DEFAULT NULL,
    `username`       VARCHAR(64)     NOT NULL,
    `password`       VARCHAR(255)    NOT NULL,
    `display_name`   VARCHAR(255)    NOT NULL,
    `role`           ENUM('super_admin','publisher_admin') NOT NULL,
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
    `remember_token`  VARCHAR(100)    DEFAULT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    KEY `idx_users_publisher_role` (`publisher_id`, `role`),
    KEY `idx_users_is_active` (`is_active`),
    CONSTRAINT `fk_users_publisher`
        FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 書籍
-- -----------------------------------------------------------
CREATE TABLE `books` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `publisher_id`        BIGINT UNSIGNED NOT NULL,
    `book_name`           VARCHAR(255)    NOT NULL,
    `book_description`    TEXT            NOT NULL,
    `book_author`         VARCHAR(255)    NOT NULL,
    `isbn13_digits`       CHAR(13)        NOT NULL,
    `isbn13_hyphenated`   VARCHAR(32)     NOT NULL,
    `isbn_prefix`         CHAR(3)         NOT NULL,
    `registration_group`  VARCHAR(8)      NOT NULL,
    `publisher_code`      VARCHAR(12)     NOT NULL,
    `publication_code`    VARCHAR(16)     NOT NULL,
    `check_digit`         CHAR(1)         NOT NULL,
    `is_hidden`           TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`          TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`          TIMESTAMP       NULL DEFAULT NULL,
    `deleted_at`          TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_books_isbn13_digits` (`isbn13_digits`),
    UNIQUE KEY `uk_books_isbn13_hyphenated` (`isbn13_hyphenated`),
    KEY `idx_books_publisher_hidden` (`publisher_id`, `is_hidden`),
    KEY `idx_books_is_hidden` (`is_hidden`),
    CONSTRAINT `fk_books_publisher`
        FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 書籍圖片
-- -----------------------------------------------------------
CREATE TABLE `book_images` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `book_id`    BIGINT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255)    NOT NULL,
    `sort_order` INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_book_images_book_id` (`book_id`),
    UNIQUE KEY `uk_book_images_book_sort` (`book_id`, `sort_order`),
    CONSTRAINT `fk_book_images_book`
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Laravel migrations tracker
-- -----------------------------------------------------------
CREATE TABLE `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT          NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Personal access tokens (Sanctum)
-- -----------------------------------------------------------
CREATE TABLE `personal_access_tokens` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tokenable_type` VARCHAR(255)    NOT NULL,
    `tokenable_id`   BIGINT UNSIGNED NOT NULL,
    `name`           VARCHAR(255)    NOT NULL,
    `token`          VARCHAR(64)     NOT NULL,
    `abilities`      TEXT                DEFAULT NULL,
    `last_used_at`   TIMESTAMP       NULL DEFAULT NULL,
    `expires_at`     TIMESTAMP       NULL DEFAULT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_personal_access_tokens_token` (`token`),
    KEY `idx_personal_access_tokens_tokenable` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
