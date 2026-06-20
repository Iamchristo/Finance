<?php

declare(strict_types=1);

namespace App\Services\RealEstate;

use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Enums\WalletSection;
use App\Repositories\RealEstateRepository;
use App\Services\WalletService;

final class MarketplaceService
{
    public function __construct(
        private readonly RealEstateRepository $realEstate,
        private readonly WalletService $wallets,
    ) {
    }

    public function createListing(int $sellerUserId, ?int $propertyId, string $title, string $description, string $askingPrice): int
    {
        if (bccomp($askingPrice, '0') <= 0) {
            throw new ValidationException(['asking_price' => 'Asking price must be greater than zero.']);
        }

        return $this->realEstate->createListing($sellerUserId, $propertyId, $title, $description, $askingPrice);
    }

    public function submitOffer(int $listingId, int $buyerUserId, string $amount): int
    {
        if (bccomp($amount, '0') <= 0) {
            throw new ValidationException(['offer_amount' => 'Offer amount must be greater than zero.']);
        }

        $listing = $this->realEstate->findListing($listingId);

        if ($listing === null || $listing['status'] !== 'active') {
            throw new ValidationException(['listing' => 'This listing is not open for offers.']);
        }

        if ((int) $listing['seller_user_id'] === $buyerUserId) {
            throw new ValidationException(['listing' => 'You cannot make an offer on your own listing.']);
        }

        return $this->realEstate->createOffer($listingId, $buyerUserId, $amount);
    }

    public function counterOffer(int $offerId, int $sellerUserId, string $counterAmount): void
    {
        if (bccomp($counterAmount, '0') <= 0) {
            throw new ValidationException(['counter_amount' => 'Counter amount must be greater than zero.']);
        }

        Database::transaction(function () use ($offerId, $sellerUserId, $counterAmount): void {
            $offer = $this->realEstate->lockOffer($offerId);
            $listing = $offer === null ? null : $this->realEstate->findListing((int) $offer['listing_id']);

            $this->assertSellerOwnsPendingOffer($offer, $listing, $sellerUserId);

            $this->realEstate->updateOfferStatus($offerId, 'countered', $counterAmount);
        });
    }

    public function rejectOffer(int $offerId, int $sellerUserId): void
    {
        Database::transaction(function () use ($offerId, $sellerUserId): void {
            $offer = $this->realEstate->lockOffer($offerId);
            $listing = $offer === null ? null : $this->realEstate->findListing((int) $offer['listing_id']);

            $this->assertSellerOwnsPendingOffer($offer, $listing, $sellerUserId);

            $this->realEstate->updateOfferStatus($offerId, 'rejected');
        });
    }

    public function withdrawOffer(int $offerId, int $buyerUserId): void
    {
        Database::transaction(function () use ($offerId, $buyerUserId): void {
            $offer = $this->realEstate->lockOffer($offerId);

            if ($offer === null || (int) $offer['buyer_user_id'] !== $buyerUserId || $offer['status'] !== 'pending') {
                throw new ValidationException(['offer' => 'This offer can no longer be withdrawn.']);
            }

            $this->realEstate->updateOfferStatus($offerId, 'withdrawn');
        });
    }

    /**
     * Accepts an offer and settles the sale entirely within each user's
     * real-estate wallet section — simulated funds move buyer -> seller,
     * the listing is marked sold, and any other pending offers are rejected.
     */
    public function acceptOffer(int $offerId, int $sellerUserId): void
    {
        Database::transaction(function () use ($offerId, $sellerUserId): void {
            $offer = $this->realEstate->lockOffer($offerId);
            $listing = $offer === null ? null : $this->realEstate->lockListing((int) $offer['listing_id']);

            $this->assertSellerOwnsPendingOffer($offer, $listing, $sellerUserId);

            $amount = $offer['offer_amount'];
            $buyerUserId = (int) $offer['buyer_user_id'];
            $listingId = (int) $listing['id'];

            $this->wallets->debit(
                $buyerUserId,
                WalletSection::RealEstate,
                $amount,
                'realestate_purchase_debit',
                're_listing',
                $listingId,
                "Purchased listing: {$listing['title']}"
            );

            $this->wallets->credit(
                $sellerUserId,
                WalletSection::RealEstate,
                $amount,
                'realestate_listing_sale_proceeds',
                're_listing',
                $listingId,
                "Sold listing: {$listing['title']}"
            );

            $this->realEstate->updateOfferStatus($offerId, 'accepted');
            $this->realEstate->updateListingStatus($listingId, 'sold');
            $this->realEstate->withdrawOtherOffers($listingId, $offerId);
        });
    }

    /**
     * @param array<string, mixed>|null $offer
     * @param array<string, mixed>|null $listing
     */
    private function assertSellerOwnsPendingOffer(?array $offer, ?array $listing, int $sellerUserId): void
    {
        if ($offer === null || $offer['status'] !== 'pending') {
            throw new ValidationException(['offer' => 'This offer is no longer pending.']);
        }

        if ($listing === null || (int) $listing['seller_user_id'] !== $sellerUserId) {
            throw new ValidationException(['listing' => 'You do not own this listing.']);
        }
    }
}
