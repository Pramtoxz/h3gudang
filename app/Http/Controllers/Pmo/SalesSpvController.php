<?php

namespace App\Http\Controllers\Pmo;

use App\Http\Controllers\Controller;
use App\Models\SalesSupervisor;
use App\Services\Pmo\SalesSupervisorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SalesSpvController extends Controller
{
    public function __construct(private readonly SalesSupervisorService $salesSupervisorService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('pmo/sales-spv/Index', [
            'daftarSalesSpv' => $this->salesSupervisorService->daftar(),
        ]);
    }

    public function show(SalesSupervisor $salesSupervisor): Response
    {
        return Inertia::render(
            'pmo/sales-spv/Show',
            $this->salesSupervisorService->detail($salesSupervisor),
        );
    }

    public function destroy(SalesSupervisor $salesSupervisor): RedirectResponse
    {
        $this->salesSupervisorService->hapus($salesSupervisor);

        return to_route('pmo.sales-spv.index')->with('success', 'Data berhasil dihapus');
    }
}
