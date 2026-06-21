<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\RealEstateRepository;
use App\Services\AuditService;

final class AdminRealEstateController extends Controller
{
    public function __construct(
        private readonly RealEstateRepository $realEstate,
        private readonly AuditService $audit,
    ) {
    }

    public function properties(Request $request): Response
    {
        return $this->view('admin/realestate/properties', [
            'title' => 'Properties',
            'properties' => $this->realEstate->allProperties(),
        ]);
    }

    public function createProperty(Request $request): Response
    {
        $id = $this->realEstate->createProperty(
            (string) $request->input('title', ''),
            (string) $request->input('slug', ''),
            (string) $request->input('description', ''),
            $this->nullableInput($request, 'address'),
            $this->nullableInput($request, 'city'),
            $this->nullableInput($request, 'country'),
            (string) $request->input('property_type', 'residential'),
            (string) $request->input('total_value', '0'),
            (int) $request->input('total_shares', 0),
            (string) $request->input('share_price', '0'),
            (string) $request->input('expected_annual_roi_percent', '0'),
            (string) $request->input('mode', 'fractional'),
            $this->nullableInput($request, 'cover_image_path'),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'property.create', 'property', $id, [], $request);

        Session::flash('success', 'Property created.');

        return $this->redirect('/admin/realestate/properties');
    }

    public function updateProperty(Request $request): Response
    {
        $id = (int) $request->input('id', 0);

        $this->realEstate->updateProperty(
            $id,
            (string) $request->input('title', ''),
            (string) $request->input('description', ''),
            $this->nullableInput($request, 'address'),
            $this->nullableInput($request, 'city'),
            $this->nullableInput($request, 'country'),
            (string) $request->input('property_type', 'residential'),
            (string) $request->input('total_value', '0'),
            (int) $request->input('total_shares', 0),
            (string) $request->input('share_price', '0'),
            (string) $request->input('expected_annual_roi_percent', '0'),
            $this->nullableInput($request, 'cover_image_path'),
        );

        $this->audit->log((int) $this->currentUserId(), 'admin', 'property.update', 'property', $id, [], $request);

        Session::flash('success', 'Property updated.');

        return $this->redirect('/admin/realestate/properties');
    }

    public function togglePropertyActive(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $this->realEstate->togglePropertyActive($id);
        $this->audit->log((int) $this->currentUserId(), 'admin', 'property.toggle_active', 'property', $id, [], $request);

        Session::flash('success', 'Property status toggled.');

        return $this->redirect('/admin/realestate/properties');
    }

    public function listings(Request $request): Response
    {
        return $this->view('admin/realestate/listings', [
            'title' => 'Listing Moderation',
            'listings' => $this->realEstate->allListings(),
        ]);
    }

    public function reviewListing(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $status = (string) $request->input('status', '');

        if (!in_array($status, ['active', 'withdrawn'], true)) {
            Session::flash('error', 'Invalid moderation decision.');

            return $this->redirect('/admin/realestate/listings');
        }

        $this->realEstate->reviewListing($id, $status, (int) $this->currentUserId());
        $this->audit->log((int) $this->currentUserId(), 'admin', 'listing.review', 'listing', $id, ['status' => $status], $request);

        Session::flash('success', 'Listing moderation decision recorded.');

        return $this->redirect('/admin/realestate/listings');
    }

    private function nullableInput(Request $request, string $key): ?string
    {
        $value = $request->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
