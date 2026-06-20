<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Core\Controller;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\RealEstateRepository;
use App\Services\RealEstate\MarketplaceService;
use App\Services\RealEstate\PropertyInvestmentService;
use App\Services\WalletService;

final class RealEstateDashboardController extends Controller
{
    public function __construct(
        private readonly RealEstateRepository $realEstate,
        private readonly PropertyInvestmentService $investments,
        private readonly MarketplaceService $marketplace,
        private readonly WalletService $wallets,
    ) {
    }

    public function overview(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/realestate/overview', [
            'title' => 'Real Estate Overview',
            'balances' => $this->wallets->getBalances($userId),
            'portfolio' => $this->realEstate->investmentsForUser($userId),
        ]);
    }

    public function properties(Request $request): Response
    {
        return $this->view('dashboard/realestate/properties', [
            'title' => 'Properties',
            'properties' => $this->realEstate->activeProperties(),
        ]);
    }

    public function invest(Request $request): Response
    {
        $userId = $this->currentUserId();
        $propertyId = (int) $request->input('property_id', 0);
        $shares = (int) $request->input('shares', 0);

        try {
            $this->investments->invest($userId, $propertyId, $shares);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/realestate/properties');
        }

        Session::flash('success', 'Investment confirmed.');

        return $this->redirect('/dashboard/realestate/portfolio');
    }

    public function portfolio(Request $request): Response
    {
        $userId = $this->currentUserId();

        return $this->view('dashboard/realestate/portfolio', [
            'title' => 'My Portfolio',
            'portfolio' => $this->realEstate->investmentsForUser($userId),
        ]);
    }

    public function marketplace(Request $request): Response
    {
        return $this->view('dashboard/realestate/marketplace', [
            'title' => 'Marketplace',
            'listings' => $this->realEstate->activeListings(),
        ]);
    }

    public function makeOffer(Request $request): Response
    {
        $userId = $this->currentUserId();
        $listingId = (int) $request->input('listing_id', 0);
        $amount = (string) $request->input('offer_amount', '0');

        try {
            $this->marketplace->submitOffer($listingId, $userId, $amount);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/realestate/marketplace');
        }

        Session::flash('success', 'Offer submitted.');

        return $this->redirect('/dashboard/realestate/marketplace');
    }

    public function listings(Request $request): Response
    {
        $userId = $this->currentUserId();
        $listings = $this->realEstate->listingsForUser($userId);

        $listingsWithOffers = array_map(
            fn (array $listing): array => $listing + ['offers' => $this->realEstate->offersForListing((int) $listing['id'])],
            $listings
        );

        return $this->view('dashboard/realestate/listings', [
            'title' => 'My Listings',
            'listings' => $listingsWithOffers,
        ]);
    }

    public function createListing(Request $request): Response
    {
        $userId = $this->currentUserId();
        $propertyId = $request->input('property_id') !== '' ? (int) $request->input('property_id') : null;

        try {
            $this->marketplace->createListing(
                $userId,
                $propertyId,
                (string) $request->input('title', ''),
                (string) $request->input('description', ''),
                (string) $request->input('asking_price', '0'),
            );
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/realestate/listings');
        }

        Session::flash('success', 'Listing submitted for review.');

        return $this->redirect('/dashboard/realestate/listings');
    }

    public function acceptOffer(Request $request): Response
    {
        $userId = $this->currentUserId();
        $offerId = (int) $request->input('offer_id', 0);

        try {
            $this->marketplace->acceptOffer($offerId, $userId);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/realestate/listings');
        }

        Session::flash('success', 'Offer accepted and sale settled.');

        return $this->redirect('/dashboard/realestate/listings');
    }

    public function rejectOffer(Request $request): Response
    {
        $userId = $this->currentUserId();
        $offerId = (int) $request->input('offer_id', 0);

        try {
            $this->marketplace->rejectOffer($offerId, $userId);
        } catch (ValidationException $e) {
            Session::flash('error', implode(' ', $e->errors()));

            return $this->redirect('/dashboard/realestate/listings');
        }

        Session::flash('success', 'Offer rejected.');

        return $this->redirect('/dashboard/realestate/listings');
    }
}
