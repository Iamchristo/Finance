-- Real Estate vertical: fractional/pooled investment + user-to-user marketplace

CREATE TABLE re_properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    property_type ENUM('residential','commercial','mixed_use','land') NOT NULL DEFAULT 'residential',
    total_value DECIMAL(20,8) NOT NULL,
    total_shares INT UNSIGNED NOT NULL DEFAULT 0,
    share_price DECIMAL(20,8) NOT NULL DEFAULT 0,
    shares_sold INT UNSIGNED NOT NULL DEFAULT 0,
    expected_annual_roi_percent DECIMAL(6,3) NOT NULL DEFAULT 0,
    funding_status ENUM('open','funded','closed') NOT NULL DEFAULT 'open',
    mode ENUM('fractional','marketplace') NOT NULL DEFAULT 'fractional',
    cover_image_path VARCHAR(500) NULL,
    gallery JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE re_property_investments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    property_id BIGINT UNSIGNED NOT NULL,
    shares_purchased INT UNSIGNED NOT NULL,
    amount_invested DECIMAL(20,8) NOT NULL,
    status ENUM('active','exited') NOT NULL DEFAULT 'active',
    invested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exited_at DATETIME NULL,
    CONSTRAINT fk_re_inv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_re_inv_property FOREIGN KEY (property_id) REFERENCES re_properties(id),
    INDEX idx_re_inv_user (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE re_accrual_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_investment_id BIGINT UNSIGNED NOT NULL,
    ledger_entry_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    accrual_date DATE NOT NULL,
    CONSTRAINT fk_re_accrual_investment FOREIGN KEY (property_investment_id) REFERENCES re_property_investments(id) ON DELETE CASCADE,
    CONSTRAINT fk_re_accrual_ledger FOREIGN KEY (ledger_entry_id) REFERENCES ledger_entries(id),
    UNIQUE KEY uq_re_accrual_investment_date (property_investment_id, accrual_date)
) ENGINE=InnoDB;

CREATE TABLE re_listings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_user_id BIGINT UNSIGNED NOT NULL,
    property_id BIGINT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    asking_price DECIMAL(20,8) NOT NULL,
    status ENUM('draft','pending_review','active','under_offer','sold','withdrawn') NOT NULL DEFAULT 'draft',
    reviewed_by_user_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_re_listings_seller FOREIGN KEY (seller_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_re_listings_property FOREIGN KEY (property_id) REFERENCES re_properties(id) ON DELETE SET NULL,
    CONSTRAINT fk_re_listings_reviewer FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_re_listings_status (status)
) ENGINE=InnoDB;

CREATE TABLE re_offers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id BIGINT UNSIGNED NOT NULL,
    buyer_user_id BIGINT UNSIGNED NOT NULL,
    offer_amount DECIMAL(20,8) NOT NULL,
    status ENUM('pending','accepted','rejected','withdrawn','countered') NOT NULL DEFAULT 'pending',
    counter_amount DECIMAL(20,8) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    CONSTRAINT fk_re_offers_listing FOREIGN KEY (listing_id) REFERENCES re_listings(id) ON DELETE CASCADE,
    CONSTRAINT fk_re_offers_buyer FOREIGN KEY (buyer_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
