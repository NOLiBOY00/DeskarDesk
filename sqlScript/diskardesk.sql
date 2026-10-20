CREATE DATABASE IF NOT EXISTS diskardesk
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE diskardesk;

-- =========================================
-- USERS
-- =========================================

CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)            NOT NULL,
    email       VARCHAR(150)            NOT NULL UNIQUE,
    password    VARCHAR(255)            NOT NULL,
    role        ENUM('student', 'customer', 'admin')    NOT NULL DEFAULT 'customer',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- STUDENT PROFILES
-- =========================================

CREATE TABLE student_profiles (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    bio             TEXT,
    skills          TEXT,
    location        VARCHAR(100),
    profile_image   VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =========================================
-- CATEGORIES
-- =========================================

CREATE TABLE categories (
    id              INT     UNSIGNED    AUTO_INCREMENT  PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL UNIQUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- SERVICES
-- =========================================

CREATE TABLE services (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED    NOT NULL,
    category_id     INT UNSIGNED    NOT NULL,
    title           VARCHAR(150)    NOT NULL,
    description     TEXT            NOT NULL,
    price           DECIMAL(10,2)   NOT NULL,
    delivery_days   INT UNSIGNED    NOT NULL,
    status          ENUM('draft', 'published', 'inactive', 'active') DEFAULT 'published',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
);


-- =========================================
-- ORDERS
-- =========================================

CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id      INT UNSIGNED        NOT NULL,
    customer_id     INT UNSIGNED        NOT NULL,
    student_id      INT UNSIGNED        NOT NULL,
    price           DECIMAL(10,2)       NOT NULL,
    status          ENUM('pending', 'approved', 'rejected', 'in_progress', 
                        'delivered', 'completed', 'cancelled', 'denied')      DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded')               DEFAULT 'unpaid',
    request_message         TEXT,
    agreed_brief            TEXT NULL,
    delivery_message        TEXT,
    delivered_at            DATETIME NULL,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at            TIMESTAMP NULL,

    FOREIGN KEY (service_id)
        REFERENCES services(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (student_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
);


-- =========================================
-- MESSAGES
-- =========================================

CREATE TABLE messages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id       INT UNSIGNED    NOT NULL,
    receiver_id     INT UNSIGNED    NOT NULL,
    order_id        INT UNSIGNED    NULL,
    message         TEXT            NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (receiver_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE SET NULL,

    INDEX idx_order_created(order_id, created_at)
);


-- =========================================
-- REVIEWS
-- =========================================

CREATE TABLE reviews (
    id              INT         UNSIGNED    AUTO_INCREMENT  PRIMARY KEY,
    order_id        INT         UNSIGNED    NOT NULL,
    service_id      INT         UNSIGNED    NOT NULL,
    customer_id     INT         UNSIGNED    NOT NULL,
    student_id      INT         UNSIGNED    NOT NULL,
    rating          TINYINT     UNSIGNED    NOT NULL,
    comment         TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE,

    FOREIGN KEY (service_id)
        REFERENCES services(service_id)
        ON DELETE CASCADE,

    FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (student_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE KEY unique_order_reviews (order_id),

    CONSTRAINT chk_rating
        CHECK (rating BETWEEN 1 AND 5)
);

CREATE TABLE support_tickets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    reporter_id     INT UNSIGNED NOT NULL,
    reporter_role   ENUM('customer', 'student') NOT NULL,
    issue_type      ENUM('delayed_delivery', 'quality_concern', 'scope_creep', 
                        'revision_dispute', 'payment_delay', 'other') NOT NULL,
    subject         VARCHAR(150) NOT NULL,
    description     TEXT NOT NULL,
    status          ENUM('open', 'under_review', 'awaiting_response',
                            'resolved', 'closed') NOT NULL DEFAULT 'open',
    admin_notes     TEXT NULL,
    resolution      TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
    resolved_at     DATETIME NULL,

    INDEX idx_ticket_status (status),
    INDEX idx_ticket_order (order_id),
    INDEX idx_ticket_reporter (reporter_id),

    CONSTRAINT fk_ticket_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_ticket_reporter
        FOREIGN KEY (reporter_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
);

-- =========================================
-- DEFAULT CATEGORIES
-- =========================================

INSERT INTO categories (name) VALUES
('Tutoring'),
('Graphic Design'),
('Writing'),
('Photography'),
('Programming'),
('Arts'),
('Music');