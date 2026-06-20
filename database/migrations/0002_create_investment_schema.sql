-- Investment vertical

CREATE TABLE investment_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    tier ENUM('starter','growth','premium','elite') NOT NULL DEFAULT 'starter',
    min_amount DECIMAL(20,8) NOT NULL,
    max_amount DECIMAL(20,8) NULL,
    roi_percent DECIMAL(6,3) NOT NULL,
    roi_period ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
    duration_days INT UNSIGNED NOT NULL,
    compounding_allowed TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE investment_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    principal_amount DECIMAL(20,8) NOT NULL,
    status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
    reinvest_enabled TINYINT(1) NOT NULL DEFAULT 0,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NULL,
    last_accrued_at DATETIME NULL,
    total_accrued DECIMAL(20,8) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inv_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_sub_plan FOREIGN KEY (plan_id) REFERENCES investment_plans(id),
    INDEX idx_inv_sub_user (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE investment_accrual_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    ledger_entry_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    accrual_date DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_accrual_subscription FOREIGN KEY (subscription_id) REFERENCES investment_subscriptions(id) ON DELETE CASCADE,
    CONSTRAINT fk_accrual_ledger FOREIGN KEY (ledger_entry_id) REFERENCES ledger_entries(id),
    UNIQUE KEY uq_accrual_subscription_date (subscription_id, accrual_date)
) ENGINE=InnoDB;

CREATE TABLE referral_relationships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_user_id BIGINT UNSIGNED NOT NULL,
    referred_user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    level TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_referral_referrer FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_referral_referred FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE referral_commissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_user_id BIGINT UNSIGNED NOT NULL,
    referred_user_id BIGINT UNSIGNED NOT NULL,
    source_subscription_id BIGINT UNSIGNED NULL,
    ledger_entry_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    commission_rate DECIMAL(6,3) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_commission_referrer FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_commission_referred FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_commission_subscription FOREIGN KEY (source_subscription_id) REFERENCES investment_subscriptions(id) ON DELETE SET NULL,
    CONSTRAINT fk_commission_ledger FOREIGN KEY (ledger_entry_id) REFERENCES ledger_entries(id)
) ENGINE=InnoDB;
