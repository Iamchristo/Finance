-- Demo content seed: simulated investment plans, tradable instruments,
-- strategies, and fractional real-estate properties. No user data.

INSERT INTO investment_plans (name, slug, description, tier, min_amount, max_amount, roi_percent, roi_period, duration_days, compounding_allowed, is_active, sort_order) VALUES
    ('Starter Yield', 'starter-yield', 'Entry-level plan for new investors looking for steady simulated daily returns.', 'starter', 100.00000000, 4999.00000000, 0.500, 'daily', 30, 0, 1, 1),
    ('Growth Accelerator', 'growth-accelerator', 'Higher simulated daily yield with optional auto-reinvest compounding.', 'growth', 5000.00000000, 24999.00000000, 0.800, 'daily', 60, 1, 1, 2),
    ('Premium Income', 'premium-income', 'Weekly simulated payouts designed for larger allocations.', 'premium', 25000.00000000, 99999.00000000, 6.500, 'weekly', 90, 1, 1, 3),
    ('Elite Capital', 'elite-capital', 'Our top-tier simulated plan for high-net-worth demo portfolios.', 'elite', 100000.00000000, NULL, 18.000, 'monthly', 180, 1, 1, 4);

INSERT INTO fx_instruments (class_id, symbol, display_name, base_currency, quote_currency, current_price, previous_close, daily_change_percent, volatility_factor, leverage_max, min_trade_size, price_precision, is_active, sort_order) VALUES
    (1, 'EURUSD', 'Euro / US Dollar', 'EUR', 'USD', 1.08450000, 1.08200000, 0.2311, 0.000400, 100, 0.01000000, 5, 1, 1),
    (1, 'GBPUSD', 'British Pound / US Dollar', 'GBP', 'USD', 1.26800000, 1.26550000, 0.1976, 0.000450, 100, 0.01000000, 5, 1, 2),
    (1, 'USDJPY', 'US Dollar / Japanese Yen', 'USD', 'JPY', 156.32000000, 155.98000000, 0.2180, 0.000500, 100, 0.01000000, 3, 1, 3),
    (1, 'AUDUSD', 'Australian Dollar / US Dollar', 'AUD', 'USD', 0.66420000, 0.66510000, -0.1353, 0.000420, 50, 0.01000000, 5, 1, 4),
    (1, 'USDCHF', 'US Dollar / Swiss Franc', 'USD', 'CHF', 0.90120000, 0.90050000, 0.0777, 0.000380, 50, 0.01000000, 5, 1, 5),
    (1, 'NZDUSD', 'New Zealand Dollar / US Dollar', 'NZD', 'USD', 0.60980000, 0.61050000, -0.1146, 0.000450, 50, 0.01000000, 5, 1, 6),
    (2, 'BTCUSD', 'Bitcoin', 'BTC', 'USD', 67850.00000000, 66920.00000000, 1.3897, 0.020000, 20, 0.00010000, 2, 1, 1),
    (2, 'ETHUSD', 'Ethereum', 'ETH', 'USD', 3540.50000000, 3498.20000000, 1.2095, 0.022000, 20, 0.00100000, 2, 1, 2),
    (2, 'SOLUSD', 'Solana', 'SOL', 'USD', 168.40000000, 162.10000000, 3.8865, 0.030000, 10, 0.01000000, 2, 1, 3),
    (2, 'XRPUSD', 'Ripple', 'XRP', 'USD', 0.52300000, 0.51400000, 1.7510, 0.025000, 10, 1.00000000, 4, 1, 4),
    (2, 'BNBUSD', 'BNB', 'BNB', 'USD', 595.20000000, 588.40000000, 1.1557, 0.021000, 10, 0.01000000, 2, 1, 5),
    (3, 'US500', 'US 500 Index', 'USD', 'USD', 5430.20000000, 5402.80000000, 0.5073, 0.000900, 30, 0.10000000, 2, 1, 1),
    (3, 'US30', 'US Wall Street 30', 'USD', 'USD', 39120.00000000, 38950.00000000, 0.4365, 0.000850, 30, 0.10000000, 2, 1, 2),
    (3, 'NAS100', 'US Tech 100', 'USD', 'USD', 19340.00000000, 19180.00000000, 0.8342, 0.001100, 30, 0.10000000, 2, 1, 3),
    (3, 'UK100', 'UK 100 Index', 'GBP', 'GBP', 8210.00000000, 8195.50000000, 0.1770, 0.000800, 20, 0.10000000, 2, 1, 4),
    (3, 'GER40', 'Germany 40 Index', 'EUR', 'EUR', 18420.00000000, 18375.00000000, 0.2451, 0.000850, 20, 0.10000000, 2, 1, 5),
    (4, 'XAUUSD', 'Gold Spot', 'XAU', 'USD', 2345.80000000, 2338.40000000, 0.3165, 0.000700, 20, 0.01000000, 2, 1, 1),
    (4, 'XAGUSD', 'Silver Spot', 'XAG', 'USD', 30.62000000, 30.18000000, 1.4579, 0.001200, 20, 0.10000000, 3, 1, 2),
    (4, 'USOIL', 'WTI Crude Oil', 'USD', 'USD', 78.45000000, 79.10000000, -0.8217, 0.001500, 20, 0.10000000, 2, 1, 3),
    (4, 'NATGAS', 'Natural Gas', 'USD', 'USD', 2.68000000, 2.71000000, -1.1070, 0.002200, 15, 1.00000000, 3, 1, 4);

INSERT INTO fx_strategies (name, slug, description, risk_level, simulated_monthly_return_percent, is_active) VALUES
    ('Conservative Carry', 'conservative-carry', 'Low-risk simulated carry strategy across major forex pairs.', 'low', 1.200, 1),
    ('Balanced Swing', 'balanced-swing', 'Medium-risk swing strategy diversified across forex and indices.', 'medium', 3.500, 1),
    ('Momentum Breakout', 'momentum-breakout', 'Higher-risk momentum strategy targeting breakout moves in indices and commodities.', 'high', 7.000, 1),
    ('Crypto Momentum', 'crypto-momentum', 'High-risk simulated strategy riding short-term crypto momentum.', 'high', 9.000, 1);

INSERT INTO re_properties (title, slug, description, address, city, country, property_type, total_value, total_shares, share_price, shares_sold, expected_annual_roi_percent, funding_status, mode, is_active) VALUES
    ('Harborview Residences', 'harborview-residences', 'A 48-unit residential complex with stable rental occupancy near the waterfront district.', '120 Harbor St', 'Miami', 'USA', 'residential', 4800000.00000000, 4800, 1000.00000000, 1260, 7.200, 'open', 'fractional', 1),
    ('Crestline Office Park', 'crestline-office-park', 'Class-A commercial office park leased to long-term corporate tenants.', '88 Crestline Ave', 'Austin', 'USA', 'commercial', 9600000.00000000, 9600, 1000.00000000, 5040, 8.500, 'open', 'fractional', 1),
    ('Maple Grove Townhomes', 'maple-grove-townhomes', 'Suburban townhome development with strong family-rental demand.', '14 Maple Grove Rd', 'Columbus', 'USA', 'residential', 2400000.00000000, 2400, 1000.00000000, 480, 6.800, 'open', 'fractional', 1),
    ('Riverside Mixed-Use Plaza', 'riverside-mixed-use-plaza', 'Mixed-use retail and residential plaza in a high-growth riverside district.', '500 Riverside Blvd', 'Denver', 'USA', 'mixed_use', 6200000.00000000, 6200, 1000.00000000, 3596, 9.100, 'open', 'fractional', 1),
    ('Sunset Logistics Park', 'sunset-logistics-park', 'Warehouse and logistics park serving regional last-mile distribution.', '900 Industrial Pkwy', 'Phoenix', 'USA', 'commercial', 5400000.00000000, 5400, 1000.00000000, 5400, 7.900, 'funded', 'fractional', 1);
