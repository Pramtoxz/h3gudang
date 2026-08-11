<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('admin/dashboard', [
            'statistik' => $this->dashboardService->statistik(),
            'cacheCollection' => $this->dashboardService->statusCacheCollection(),
        ]);
    }
}
