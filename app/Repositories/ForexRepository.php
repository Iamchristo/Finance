<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ForexRepository
{
    /** @return array<int, array<string, mixed>> */
    public function activeInstruments(): array
    {
        $stmt = Database::connection()->query(
            'SELECT i.*, c.name AS class_name, c.label AS class_label
             FROM fx_instruments i
             JOIN fx_instrument_classes c ON c.id = i.class_id
             WHERE i.is_active = 1
             ORDER BY c.id, i.sort_order'
        );

        return $stmt->fetchAll();
    }

    public function findBySymbol(string $symbol): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT i.*, c.name AS class_name, c.label AS class_label
             FROM fx_instruments i JOIN fx_instrument_classes c ON c.id = i.class_id
             WHERE i.symbol = :symbol'
        );
        $stmt->execute(['symbol' => strtoupper($symbol)]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM fx_instruments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public function candles(int $instrumentId, string $timeframe, int $limit = 200): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM fx_price_history WHERE instrument_id = :id AND timeframe = :timeframe
             ORDER BY bucket_start_at DESC LIMIT :limit'
        );
        $stmt->bindValue('id', $instrumentId, \PDO::PARAM_INT);
        $stmt->bindValue('timeframe', $timeframe);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return array_reverse($stmt->fetchAll());
    }

    public function createOrder(int $userId, int $instrumentId, string $side, string $orderType, string $quantity, int $leverage): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO fx_orders (user_id, instrument_id, side, order_type, quantity, leverage, status)
             VALUES (:user_id, :instrument_id, :side, :order_type, :quantity, :leverage, :status)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'instrument_id' => $instrumentId,
            'side' => $side,
            'order_type' => $orderType,
            'quantity' => $quantity,
            'leverage' => $leverage,
            'status' => 'pending',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function markOrderFilled(int $orderId, string $price): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE fx_orders SET status = 'filled', filled_price = :price, filled_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['price' => $price, 'id' => $orderId]);
    }

    public function openPosition(
        int $userId,
        int $instrumentId,
        int $openingOrderId,
        string $side,
        string $quantity,
        string $entryPrice,
        int $leverage,
        string $marginUsed,
        ?string $stopLoss,
        ?string $takeProfit,
    ): int {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO fx_positions
                (user_id, instrument_id, opening_order_id, side, quantity, entry_price, leverage, margin_used, stop_loss, take_profit)
             VALUES (:user_id, :instrument_id, :opening_order_id, :side, :quantity, :entry_price, :leverage, :margin_used, :stop_loss, :take_profit)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'instrument_id' => $instrumentId,
            'opening_order_id' => $openingOrderId,
            'side' => $side,
            'quantity' => $quantity,
            'entry_price' => $entryPrice,
            'leverage' => $leverage,
            'margin_used' => $marginUsed,
            'stop_loss' => $stopLoss,
            'take_profit' => $takeProfit,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function openPositionsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT p.*, i.symbol, i.display_name, i.current_price, i.price_precision
             FROM fx_positions p JOIN fx_instruments i ON i.id = p.instrument_id
             WHERE p.user_id = :user_id AND p.status = 'open' ORDER BY p.opened_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function allOpenPositions(): array
    {
        $stmt = Database::connection()->query(
            "SELECT p.*, i.current_price FROM fx_positions p JOIN fx_instruments i ON i.id = p.instrument_id WHERE p.status = 'open'"
        );

        return $stmt->fetchAll();
    }

    /**
     * Gated on status = 'open' so a concurrent close of the same position
     * (e.g. an overlapping position-monitor cron run, or a duplicate manual
     * close request) only ever wins once. Returns false when the position
     * was already closed by someone else, so the caller can skip crediting
     * the wallet a second time for the same closure.
     */
    public function closePosition(int $positionId, string $exitPrice, string $realizedPnl, string $status = 'closed'): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE fx_positions SET status = :status, exit_price = :exit_price, realized_pnl = :pnl, closed_at = NOW()
             WHERE id = :id AND status = 'open'"
        );
        $stmt->execute(['status' => $status, 'exit_price' => $exitPrice, 'pnl' => $realizedPnl, 'id' => $positionId]);

        return $stmt->rowCount() > 0;
    }

    public function findPosition(int $positionId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM fx_positions WHERE id = :id');
        $stmt->execute(['id' => $positionId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public function orderHistory(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT o.*, i.symbol FROM fx_orders o JOIN fx_instruments i ON i.id = o.instrument_id
             WHERE o.user_id = :user_id ORDER BY o.created_at DESC LIMIT 100'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function updatePrice(int $instrumentId, string $newPrice, string $previousClose, string $changePercent): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE fx_instruments SET current_price = :price, previous_close = :prev, daily_change_percent = :change WHERE id = :id'
        );
        $stmt->execute(['price' => $newPrice, 'prev' => $previousClose, 'change' => $changePercent, 'id' => $instrumentId]);
    }

    public function upsertCandle(int $instrumentId, string $timeframe, string $bucketStart, string $price): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT id, high, low FROM fx_price_history WHERE instrument_id = :id AND timeframe = :timeframe AND bucket_start_at = :bucket'
        );
        $stmt->execute(['id' => $instrumentId, 'timeframe' => $timeframe, 'bucket' => $bucketStart]);
        $existing = $stmt->fetch();

        if ($existing === false) {
            $insert = $pdo->prepare(
                'INSERT INTO fx_price_history (instrument_id, timeframe, open, high, low, close, bucket_start_at)
                 VALUES (:id, :timeframe, :open, :high, :low, :close, :bucket)'
            );
            $insert->execute([
                'id' => $instrumentId,
                'timeframe' => $timeframe,
                'open' => $price,
                'high' => $price,
                'low' => $price,
                'close' => $price,
                'bucket' => $bucketStart,
            ]);

            return;
        }

        $high = bccomp($price, $existing['high'], 8) > 0 ? $price : $existing['high'];
        $low = bccomp($price, $existing['low'], 8) < 0 ? $price : $existing['low'];

        $update = $pdo->prepare(
            'UPDATE fx_price_history SET close = :price, high = :high, low = :low WHERE id = :row_id'
        );
        $update->execute(['price' => $price, 'high' => $high, 'low' => $low, 'row_id' => $existing['id']]);
    }

    public function defaultWatchlistId(int $userId): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id FROM fx_watchlists WHERE user_id = :user_id ORDER BY id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $insert = $pdo->prepare('INSERT INTO fx_watchlists (user_id, name) VALUES (:user_id, :name)');
        $insert->execute(['user_id' => $userId, 'name' => 'My Watchlist']);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function watchlistItems(int $watchlistId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT wi.id AS watchlist_item_id, i.* FROM fx_watchlist_items wi
             JOIN fx_instruments i ON i.id = wi.instrument_id
             WHERE wi.watchlist_id = :watchlist_id
             ORDER BY wi.sort_order, i.symbol'
        );
        $stmt->execute(['watchlist_id' => $watchlistId]);

        return $stmt->fetchAll();
    }

    public function addToWatchlist(int $watchlistId, int $instrumentId): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT IGNORE INTO fx_watchlist_items (watchlist_id, instrument_id) VALUES (:watchlist_id, :instrument_id)'
        );
        $stmt->execute(['watchlist_id' => $watchlistId, 'instrument_id' => $instrumentId]);
    }

    public function removeFromWatchlist(int $watchlistId, int $instrumentId): void
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM fx_watchlist_items WHERE watchlist_id = :watchlist_id AND instrument_id = :instrument_id'
        );
        $stmt->execute(['watchlist_id' => $watchlistId, 'instrument_id' => $instrumentId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function activeStrategies(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM fx_strategies WHERE is_active = 1 ORDER BY simulated_monthly_return_percent DESC"
        );

        return $stmt->fetchAll();
    }

    public function findStrategy(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM fx_strategies WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function createStrategySubscription(int $userId, int $strategyId, string $amount): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO fx_strategy_subscriptions (user_id, strategy_id, allocated_amount) VALUES (:user_id, :strategy_id, :amount)'
        );
        $stmt->execute(['user_id' => $userId, 'strategy_id' => $strategyId, 'amount' => $amount]);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function strategySubscriptionsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, st.name, st.risk_level, st.simulated_monthly_return_percent
             FROM fx_strategy_subscriptions s
             JOIN fx_strategies st ON st.id = s.strategy_id
             WHERE s.user_id = :user_id ORDER BY s.started_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function instrumentClasses(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM fx_instrument_classes ORDER BY id');

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function allInstruments(): array
    {
        $stmt = Database::connection()->query(
            'SELECT i.*, c.name AS class_name, c.label AS class_label
             FROM fx_instruments i JOIN fx_instrument_classes c ON c.id = i.class_id
             ORDER BY c.id, i.sort_order'
        );

        return $stmt->fetchAll();
    }

    public function createInstrument(
        int $classId,
        string $symbol,
        string $displayName,
        string $baseCurrency,
        string $quoteCurrency,
        string $currentPrice,
        int $leverageMax,
        string $minTradeSize,
        int $pricePrecision,
        int $sortOrder,
    ): int {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO fx_instruments
                (class_id, symbol, display_name, base_currency, quote_currency, current_price, previous_close,
                 leverage_max, min_trade_size, price_precision, sort_order)
             VALUES (:class_id, :symbol, :display_name, :base_currency, :quote_currency, :current_price, :previous_close,
                     :leverage_max, :min_trade_size, :price_precision, :sort_order)'
        );
        $stmt->execute([
            'class_id' => $classId,
            'symbol' => strtoupper($symbol),
            'display_name' => $displayName,
            'base_currency' => strtoupper($baseCurrency),
            'quote_currency' => strtoupper($quoteCurrency),
            'current_price' => $currentPrice,
            'previous_close' => $currentPrice,
            'leverage_max' => $leverageMax,
            'min_trade_size' => $minTradeSize,
            'price_precision' => $pricePrecision,
            'sort_order' => $sortOrder,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function updateInstrument(
        int $id,
        string $displayName,
        string $baseCurrency,
        string $quoteCurrency,
        int $leverageMax,
        string $minTradeSize,
        int $pricePrecision,
        int $sortOrder,
    ): void {
        $stmt = Database::connection()->prepare(
            'UPDATE fx_instruments SET
                display_name = :display_name, base_currency = :base_currency, quote_currency = :quote_currency,
                leverage_max = :leverage_max, min_trade_size = :min_trade_size,
                price_precision = :price_precision, sort_order = :sort_order
             WHERE id = :id'
        );
        $stmt->execute([
            'display_name' => $displayName,
            'base_currency' => strtoupper($baseCurrency),
            'quote_currency' => strtoupper($quoteCurrency),
            'leverage_max' => $leverageMax,
            'min_trade_size' => $minTradeSize,
            'price_precision' => $pricePrecision,
            'sort_order' => $sortOrder,
            'id' => $id,
        ]);
    }

    public function toggleInstrumentActive(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE fx_instruments SET is_active = NOT is_active WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function allStrategies(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM fx_strategies ORDER BY id');

        return $stmt->fetchAll();
    }

    public function createStrategy(
        string $name,
        string $slug,
        string $description,
        string $riskLevel,
        string $simulatedMonthlyReturnPercent,
    ): int {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO fx_strategies (name, slug, description, risk_level, simulated_monthly_return_percent)
             VALUES (:name, :slug, :description, :risk_level, :return_percent)'
        );
        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'risk_level' => $riskLevel,
            'return_percent' => $simulatedMonthlyReturnPercent,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function updateStrategy(
        int $id,
        string $name,
        string $description,
        string $riskLevel,
        string $simulatedMonthlyReturnPercent,
    ): void {
        $stmt = Database::connection()->prepare(
            'UPDATE fx_strategies SET
                name = :name, description = :description, risk_level = :risk_level,
                simulated_monthly_return_percent = :return_percent
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'risk_level' => $riskLevel,
            'return_percent' => $simulatedMonthlyReturnPercent,
            'id' => $id,
        ]);
    }

    public function toggleStrategyActive(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE fx_strategies SET is_active = NOT is_active WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
