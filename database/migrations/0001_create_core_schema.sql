-- Core: users, roles/permissions, auth support tables, audit, notifications, settings, rate limiting.

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active','suspended','banned','pending_verification') NOT NULL DEFAULT 'pending_verification',
    kyc_status ENUM('not_submitted','pending','approved','rejected') NOT NULL DEFAULT 'not_submitted',
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_secret VARCHAR(255) NULL,
    referral_code VARCHAR(20) NOT NULL UNIQUE,
    referred_by_user_id BIGINT UNSIGNED NULL,
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_users_status (status),
    INDEX idx_users_kyc_status (kyc_status)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE two_factor_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    purpose ENUM('login','sensitive_action') NOT NULL DEFAULT 'login',
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_two_factor_codes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE kyc_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('id_card','passport','proof_of_address') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by_user_id BIGINT UNSIGNED NULL,
    rejection_reason VARCHAR(255) NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    CONSTRAINT fk_kyc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_kyc_reviewer FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id BIGINT UNSIGNED NULL,
    actor_type ENUM('user','admin','system') NOT NULL DEFAULT 'system',
    action VARCHAR(100) NOT NULL,
    subject_type VARCHAR(100) NULL,
    subject_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_subject (subject_type, subject_id),
    INDEX idx_audit_actor (actor_user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    data JSON NULL,
    channel ENUM('in_app','email') NOT NULL DEFAULT 'in_app',
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, read_at)
) ENGINE=InnoDB;

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('string','int','bool','json') NOT NULL DEFAULT 'string'
) ENGINE=InnoDB;

CREATE TABLE rate_limit_hits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bucket_key VARCHAR(150) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rate_limit_bucket (bucket_key, created_at)
) ENGINE=InnoDB;

-- Wallet / ledger core: the cross-vertical glue. One wallet row per user.

CREATE TABLE wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    main_balance DECIMAL(20,8) NOT NULL DEFAULT 0,
    investment_balance DECIMAL(20,8) NOT NULL DEFAULT 0,
    forex_balance DECIMAL(20,8) NOT NULL DEFAULT 0,
    realestate_balance DECIMAL(20,8) NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    version INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_wallets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ledger_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    wallet_section ENUM('main','investment','forex','realestate') NOT NULL,
    direction ENUM('credit','debit') NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    balance_after DECIMAL(20,8) NOT NULL,
    type ENUM(
        'transfer_in','transfer_out',
        'investment_subscribe','investment_roi_accrual','investment_payout','investment_reinvest',
        'referral_commission',
        'forex_order_open','forex_order_close','forex_pnl_credit','forex_pnl_debit','forex_fee',
        'realestate_investment','realestate_roi_accrual','realestate_payout',
        'realestate_listing_sale_proceeds','realestate_purchase_debit',
        'admin_adjustment_credit','admin_adjustment_debit'
    ) NOT NULL,
    reference_type VARCHAR(100) NULL,
    reference_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NULL,
    related_transfer_id BIGINT UNSIGNED NULL,
    created_by_user_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ledger_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ledger_related_transfer FOREIGN KEY (related_transfer_id) REFERENCES ledger_entries(id) ON DELETE SET NULL,
    CONSTRAINT fk_ledger_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ledger_user_created (user_id, created_at),
    INDEX idx_ledger_section (wallet_section, created_at),
    INDEX idx_ledger_reference (reference_type, reference_id)
) ENGINE=InnoDB;

-- Seed roles and permissions.

INSERT INTO roles (name, label, description) VALUES
    ('super_admin', 'Super Admin', 'Full platform control'),
    ('admin', 'Admin', 'Operational administration'),
    ('support', 'Support', 'Customer support, read-mostly access'),
    ('compliance', 'Compliance', 'KYC review and account actions'),
    ('user', 'User', 'Standard platform user');

INSERT INTO permissions (name, label) VALUES
    ('users.view', 'View users'),
    ('users.suspend', 'Suspend or ban users'),
    ('users.role_assign', 'Assign user roles'),
    ('kyc.review', 'Review KYC submissions'),
    ('ledger.view', 'View ledger entries'),
    ('ledger.adjust', 'Create manual ledger adjustments'),
    ('investment.manage', 'Manage investment plans'),
    ('forex.manage', 'Manage forex instruments and orders'),
    ('realestate.manage', 'Manage real estate properties and listings'),
    ('settings.manage', 'Manage platform settings'),
    ('audit.view', 'View audit log');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.name = 'super_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name IN (
    'users.view','users.suspend','kyc.review','ledger.view','ledger.adjust',
    'investment.manage','forex.manage','realestate.manage','audit.view'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'compliance' AND p.name IN ('users.view','kyc.review','ledger.view','audit.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'support' AND p.name IN ('users.view','ledger.view');
