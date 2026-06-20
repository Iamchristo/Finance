<?php

declare(strict_types=1);

namespace App\Controllers\Marketing;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('marketing/home', ['title' => 'Invest. Trade. Own.']);
    }
}
