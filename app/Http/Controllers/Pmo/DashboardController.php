<?php

namespace App\Http\Controllers\Pmo;

use App\Http\Controllers\Controller;
use App\Services\Pmo\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('pmo/dashboard', [
            'statistik' => $this->dashboardService->statistik(),
            'cacheCollection' => $this->dashboardService->statusCacheCollection(),
        ]);
    }
}
