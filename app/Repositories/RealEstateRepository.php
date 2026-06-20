<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class RealEstateRepository
{
    /** @return array<int, array<string, mixed>> */
    public function activeProperties(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM re_properties WHERE is_active = 1 ORDER BY created_at DESC"
        );

        return $stmt->fetchAll();
    }

    public function findPropertyBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_properties WHERE slug = :slug AND is_active = 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findPropertyById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_properties WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** Locks the property row for an atomic shares_sold update; must be called inside a transaction. */
    public function lockProperty(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_properties WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function recordShares(int $propertyId, int $sharesPurchased): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "UPDATE re_properties
             SET shares_sold = shares_sold + :shares,
                 funding_status = IF(shares_sold + :shares >= total_shares, 'funded', funding_status)
             WHERE id = :id"
        );
        $stmt->execute(['shares' => $sharesPurchased, 'id' => $propertyId]);
    }

    public function createInvestment(int $userId, int $propertyId, int $shares, string $amount): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO re_property_investments (user_id, property_id, shares_purchased, amount_invested)
             VALUES (:user_id, :property_id, :shares, :amount)'
        );
        $stmt->execute(['user_id' => $userId, 'property_id' => $propertyId, 'shares' => $shares, 'amount' => $amount]);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function investmentsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT i.*, p.title, p.slug, p.expected_annual_roi_percent, p.cover_image_path
             FROM re_property_investments i
             JOIN re_properties p ON p.id = i.property_id
             WHERE i.user_id = :user_id
             ORDER BY i.invested_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> active investments due for accrual today */
    public function activeInvestmentsForAccrual(): array
    {
        $stmt = Database::connection()->query(
            "SELECT i.*, p.expected_annual_roi_percent
             FROM re_property_investments i
             JOIN re_properties p ON p.id = i.property_id
             WHERE i.status = 'active'"
        );

        return $stmt->fetchAll();
    }

    public function recordAccrual(int $investmentId, int $ledgerEntryId, string $amount, string $date): bool
    {
        $stmt = Database::connection()->prepare(
            'INSERT IGNORE INTO re_accrual_logs (property_investment_id, ledger_entry_id, amount, accrual_date)
             VALUES (:investment_id, :ledger_entry_id, :amount, :date)'
        );
        $stmt->execute([
            'investment_id' => $investmentId,
            'ledger_entry_id' => $ledgerEntryId,
            'amount' => $amount,
            'date' => $date,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function createListing(int $sellerUserId, ?int $propertyId, string $title, string $description, string $askingPrice): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "INSERT INTO re_listings (seller_user_id, property_id, title, description, asking_price, status)
             VALUES (:seller_user_id, :property_id, :title, :description, :asking_price, 'pending_review')"
        );
        $stmt->execute([
            'seller_user_id' => $sellerUserId,
            'property_id' => $propertyId,
            'title' => $title,
            'description' => $description,
            'asking_price' => $askingPrice,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function activeListings(): array
    {
        $stmt = Database::connection()->query(
            "SELECT l.*, CONCAT(u.first_name, ' ', u.last_name) AS seller_name FROM re_listings l
             JOIN users u ON u.id = l.seller_user_id
             WHERE l.status = 'active' ORDER BY l.created_at DESC"
        );

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function listingsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM re_listings WHERE seller_user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function findListing(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_listings WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function lockListing(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_listings WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function updateListingStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE re_listings SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function createOffer(int $listingId, int $buyerUserId, string $amount): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO re_offers (listing_id, buyer_user_id, offer_amount) VALUES (:listing_id, :buyer_user_id, :amount)'
        );
        $stmt->execute(['listing_id' => $listingId, 'buyer_user_id' => $buyerUserId, 'amount' => $amount]);

        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function offersForListing(int $listingId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS buyer_name FROM re_offers o
             JOIN users u ON u.id = o.buyer_user_id
             WHERE o.listing_id = :listing_id ORDER BY o.created_at DESC"
        );
        $stmt->execute(['listing_id' => $listingId]);

        return $stmt->fetchAll();
    }

    public function findOffer(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_offers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function lockOffer(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM re_offers WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function updateOfferStatus(int $id, string $status, ?string $counterAmount = null): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE re_offers SET status = :status, counter_amount = :counter_amount, responded_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'counter_amount' => $counterAmount, 'id' => $id]);
    }

    public function withdrawOtherOffers(int $listingId, int $exceptOfferId): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE re_offers SET status = 'rejected', responded_at = NOW()
             WHERE listing_id = :listing_id AND id != :except_id AND status = 'pending'"
        );
        $stmt->execute(['listing_id' => $listingId, 'except_id' => $exceptOfferId]);
    }
}
