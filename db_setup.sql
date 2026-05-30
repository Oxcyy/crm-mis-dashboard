-- ============================================================
-- db_setup.sql
-- CRM Analytics MIS — Setup Database customer_churn
-- Jalankan file ini sekali sebelum menggunakan aplikasi
-- ============================================================

CREATE DATABASE IF NOT EXISTS customer_churn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE customer_churn;

-- Tabel utama customer churn
CREATE TABLE IF NOT EXISTS customer_churn (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    customerid    VARCHAR(50)  NOT NULL UNIQUE,
    age           INT          NOT NULL,
    gender        VARCHAR(10)  NOT NULL,
    tenure        INT          DEFAULT 0,
    usage_frequency INT        DEFAULT 0,
    support_calls   INT        DEFAULT 0,
    payment_delay   INT        DEFAULT 0,
    subscription_type VARCHAR(20) NOT NULL DEFAULT 'Basic',
    contract_length   VARCHAR(20) NOT NULL DEFAULT 'Monthly',
    total_spend       DECIMAL(10,2) DEFAULT 0.00,
    last_interaction  INT        DEFAULT 0,
    churn             TINYINT(1) DEFAULT 0,
    created_at        TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel users login
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       VARCHAR(20)  DEFAULT 'admin',
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123)
INSERT IGNORE INTO users (username, password, role)
VALUES ('admin', SHA2('admin123', 256), 'admin');

-- Sample data
INSERT IGNORE INTO customer_churn
  (customerid,age,gender,tenure,usage_frequency,support_calls,payment_delay,subscription_type,contract_length,total_spend,last_interaction,churn)
VALUES
  ('CUST001',42,'Male',  24,18,3, 5, 'Premium', 'Annual',   680.50,10,0),
  ('CUST002',29,'Female', 8,12,8,27, 'Basic',   'Monthly',  210.00,45,1),
  ('CUST003',55,'Male',  36,15,2, 3, 'Standard','Quarterly',490.75, 8,0),
  ('CUST004',33,'Female',12,20,1, 0, 'Premium', 'Annual',   720.00, 5,0),
  ('CUST005',47,'Male',   5,10,9,31, 'Basic',   'Monthly',  155.20,60,1),
  ('CUST006',38,'Female',18,14,4,12, 'Standard','Quarterly',380.90,20,0),
  ('CUST007',62,'Male',  48,22,0, 1, 'Premium', 'Annual',   890.00, 3,0),
  ('CUST008',25,'Female', 3, 8,11,40,'Basic',   'Monthly',   99.99,90,1),
  ('CUST009',44,'Male',  15,13,5,18, 'Standard','Monthly',  330.00,35,1),
  ('CUST010',31,'Female',22,17,2, 4, 'Premium', 'Annual',   650.00, 7,0);
