-- Forex / Crypto / Indices / Commodities trading vertical

CREATE TABLE fx_instrument_classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE fx_instruments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    symbol VARCHAR(20) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    base_currency VARCHAR(10) NOT NULL,
    quote_currency VARCHAR(10) NOT NULL,
    current_price DECIMAL(20,8) NOT NULL,
    previous_close DECIMAL(20,8) NOT NULL,
    daily_change_percent DECIMAL(8,4) NOT NULL DEFAULT 0,
    volatility_factor DECIMAL(8,6) NOT NULL DEFAULT 0.001,
    leverage_max INT UNSIGNED NOT NULL DEFAULT 1,
    min_trade_size DECIMAL(20,8) NOT NULL DEFAULT 0.01,
    price_precision TINYINT UNSIGNED NOT NULL DEFAULT 5,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_fx_instruments_class FOREIGN KEY (class_id) REFERENCES fx_instrument_classes(id),
    INDEX idx_fx_instruments_class (class_id, is_active)
) ENGINE=InnoDB;

CREATE TABLE fx_price_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instrument_id BIGINT UNSIGNED NOT NULL,
    timeframe ENUM('1m','5m','15m','1h','4h','1d') NOT NULL,
    open DECIMAL(20,8) NOT NULL,
    high DECIMAL(20,8) NOT NULL,
    low DECIMAL(20,8) NOT NULL,
    close DECIMAL(20,8) NOT NULL,
    volume DECIMAL(20,8) NOT NULL DEFAULT 0,
    bucket_start_at DATETIME NOT NULL,
    CONSTRAINT fk_fx_price_history_instrument FOREIGN KEY (instrument_id) REFERENCES fx_instruments(id) ON DELETE CASCADE,
    UNIQUE KEY uq_fx_price_bucket (instrument_id, timeframe, bucket_start_at)
) ENGINE=InnoDB;

CREATE TABLE fx_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    instrument_id BIGINT UNSIGNED NOT NULL,
    side ENUM('buy','sell') NOT NULL,
    order_type ENUM('market','limit','stop') NOT NULL DEFAULT 'market',
    quantity DECIMAL(20,8) NOT NULL,
    requested_price DECIMAL(20,8) NULL,
    leverage INT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('pending','filled','cancelled','rejected') NOT NULL DEFAULT 'pending',
    filled_price DECIMAL(20,8) NULL,
    filled_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fx_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_fx_orders_instrument FOREIGN KEY (instrument_id) REFERENCES fx_instruments(id),
    INDEX idx_fx_orders_user (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE fx_positions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    instrument_id BIGINT UNSIGNED NOT NULL,
    opening_order_id BIGINT UNSIGNED NOT NULL,
    side ENUM('long','short') NOT NULL,
    quantity DECIMAL(20,8) NOT NULL,
    entry_price DECIMAL(20,8) NOT NULL,
    leverage INT UNSIGNED NOT NULL DEFAULT 1,
    margin_used DECIMAL(20,8) NOT NULL,
    stop_loss DECIMAL(20,8) NULL,
    take_profit DECIMAL(20,8) NULL,
    status ENUM('open','closed','liquidated') NOT NULL DEFAULT 'open',
    closing_order_id BIGINT UNSIGNED NULL,
    exit_price DECIMAL(20,8) NULL,
    realized_pnl DECIMAL(20,8) NULL,
    opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,
    CONSTRAINT fk_fx_positions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_fx_positions_instrument FOREIGN KEY (instrument_id) REFERENCES fx_instruments(id),
    CONSTRAINT fk_fx_positions_open_order FOREIGN KEY (opening_order_id) REFERENCES fx_orders(id),
    CONSTRAINT fk_fx_positions_close_order FOREIGN KEY (closing_order_id) REFERENCES fx_orders(id),
    INDEX idx_fx_positions_user (user_id, status)
) ENGINE=InnoDB;

CREATE TABLE fx_watchlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL DEFAULT 'My Watchlist',
    CONSTRAINT fk_fx_watchlists_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE fx_watchlist_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    watchlist_id BIGINT UNSIGNED NOT NULL,
    instrument_id BIGINT UNSIGNED NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_fx_watchlist_items_watchlist FOREIGN KEY (watchlist_id) REFERENCES fx_watchlists(id) ON DELETE CASCADE,
    CONSTRAINT fk_fx_watchlist_items_instrument FOREIGN KEY (instrument_id) REFERENCES fx_instruments(id) ON DELETE CASCADE,
    UNIQUE KEY uq_watchlist_instrument (watchlist_id, instrument_id)
) ENGINE=InnoDB;

CREATE TABLE fx_strategies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    risk_level ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    simulated_monthly_return_percent DECIMAL(6,3) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE fx_strategy_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    strategy_id BIGINT UNSIGNED NOT NULL,
    allocated_amount DECIMAL(20,8) NOT NULL,
    status ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    stopped_at DATETIME NULL,
    CONSTRAINT fk_fx_strategy_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_fx_strategy_sub_strategy FOREIGN KEY (strategy_id) REFERENCES fx_strategies(id)
) ENGINE=InnoDB;

INSERT INTO fx_instrument_classes (name, label) VALUES
    ('forex', 'Forex'),
    ('crypto', 'Crypto'),
    ('indices', 'Indices'),
    ('commodities', 'Commodities');
