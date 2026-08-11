<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesSupervisor;
use App\Services\Admin\SalesSupervisorService;
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
        return Inertia::render('admin/sales-spv/Index', [
            'daftarSalesSpv' => $this->salesSupervisorService->daftar(),
        ]);
    }

    public function show(SalesSupervisor $salesSupervisor): Response
    {
        return Inertia::render(
            'admin/sales-spv/Show',
            $this->salesSupervisorService->detail($salesSupervisor),
        );
    }

    public function destroy(SalesSupervisor $salesSupervisor): RedirectResponse
    {
        $this->salesSupervisorService->hapus($salesSupervisor);

        return to_route('admin.sales-spv.index')->with('success', 'Data berhasil dihapus');
    }
}
