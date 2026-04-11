-- ============================================================
-- NSCx56WB 書籍管理系統 — 完整資料庫 (含結構 + 樣本資料)
-- MySQL 8.0+, InnoDB, utf8mb4
-- 可直接匯入即上線
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

INSERT INTO `publishers` (`id`, `publisher_name`, `publisher_address`, `publisher_phone`, `publisher_isbn_code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Worldskills 出版社', '台北市 11568 南港區經貿二路 2 號', '+886 2 1234 5678', '5678', 1, NOW(), NOW()),
(2, '台灣技能出版社', '台北市大安區忠孝東路三段 1 號', '+886 2 8765 4321', '181', 1, NOW(), NOW()),
(3, '停用測試出版社', '高雄市前鎮區中山二路 100 號', '+886 7 1234 5678', '999', 0, NOW(), NOW());

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

INSERT INTO `publisher_contacts` (`publisher_id`, `contact_name`, `contact_phone`, `contact_email`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', '+886 912 345 678', 'john.doe@example.com', NOW(), NOW()),
(1, 'Jane Smith', '+886 923 456 789', 'jane.smith@example.com', NOW(), NOW()),
(2, '王小明', '+886 933 111 222', 'wang@example.com', NOW(), NOW()),
(3, '李大華', '+886 955 666 777', 'li@example.com', NOW(), NOW());

-- -----------------------------------------------------------
-- 使用者
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

-- 密碼皆為 bcrypt('1234')
INSERT INTO `users` (`publisher_id`, `username`, `password`, `display_name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(NULL, 'admin',    '$2y$12$o8y818/1rCpu4nkc6dAlluxK/cVf.FF46tm5q1bTknUOW9ekJrela', 'Super Admin',      'super_admin',     1, NOW(), NOW()),
(1,    'ws_admin', '$2y$12$o8y818/1rCpu4nkc6dAlluxK/cVf.FF46tm5q1bTknUOW9ekJrela', 'WS管理員',         'publisher_admin', 1, NOW(), NOW()),
(2,    'tw_admin', '$2y$12$o8y818/1rCpu4nkc6dAlluxK/cVf.FF46tm5q1bTknUOW9ekJrela', '台灣技能管理員',   'publisher_admin', 1, NOW(), NOW());

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

-- 台灣技能出版社 (id=2, code=181) 的書籍
INSERT INTO `books` (`publisher_id`, `book_name`, `book_description`, `book_author`, `isbn13_digits`, `isbn13_hyphenated`, `isbn_prefix`, `registration_group`, `publisher_code`, `publication_code`, `check_digit`, `is_hidden`, `created_at`, `updated_at`) VALUES
(2, 'Organic Apple Juice',  'Our organic apple juice is pressed from 100% fresh organic apples.\nWith no added sugars or preservatives.', 'Green Orchard', '9789861817286', '978-986-181-728-6', '978', '986', '181', '728', '6', 0, NOW(), NOW()),
(2, 'Web Development Basics', '從 HTML、CSS 到 JavaScript，一步步帶你進入網頁開發的世界。', '陳建宏', '9789861810010', '978-986-181-001-0', '978', '986', '181', '001', '0', 0, NOW(), NOW()),
(2, 'Laravel 實戰手冊',      '以實際專案為導向，深入淺出學習 Laravel 框架的核心功能與最佳實踐。', '林志玲', '9789861810188', '978-986-181-018-8', '978', '986', '181', '018', '8', 0, NOW(), NOW()),
(2, 'Python 資料分析',       '使用 Python 進行資料清洗、視覺化與機器學習入門。', '張偉', '9789861810256', '978-986-181-025-6', '978', '986', '181', '025', '6', 0, NOW(), NOW()),
(2, '已隱藏的書籍',          '此書籍已被隱藏，不應顯示在公開頁面。', '測試作者', '9789861810324', '978-986-181-032-4', '978', '986', '181', '032', '4', 1, NOW(), NOW());

-- Worldskills 出版社 (id=1, code=5678) 的書籍
INSERT INTO `books` (`publisher_id`, `book_name`, `book_description`, `book_author`, `isbn13_digits`, `isbn13_hyphenated`, `isbn_prefix`, `registration_group`, `publisher_code`, `publication_code`, `check_digit`, `is_hidden`, `created_at`, `updated_at`) VALUES
(1, 'Skills Competition Guide', 'A comprehensive guide to preparing for national and international skills competitions.', 'WorldSkills Team', '9789865678012', '978-986-5678-01-2', '978', '986', '5678', '01', '2', 0, NOW(), NOW()),
(1, '技能競賽實務',            '涵蓋網頁設計、程式設計等競賽項目的考前準備與實戰技巧。', '競賽團隊', '9789865678180', '978-986-5678-18-0', '978', '986', '5678', '18', '0', 0, NOW(), NOW());

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

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2014_10_12_100000_create_personal_access_tokens_table', 1),
('2026_04_07_000001_create_publishers_table', 1),
('2026_04_07_000002_create_publisher_contacts_table', 1),
('2026_04_07_000003_create_users_table', 1),
('2026_04_07_000004_create_books_table', 1),
('2026_04_07_000005_create_book_images_table', 1);

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
