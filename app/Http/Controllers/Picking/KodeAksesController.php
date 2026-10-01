<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Services\Picking\OperatorLoginCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KodeAksesController extends Controller
{
    public function __construct(
        private readonly OperatorLoginCodeService $codeService,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('picking/kode-akses/Index', [
            'daftarOperator' => $this->codeService->daftarOperatorDenganKode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $adminEmail = (string) ($request->user()?->email ?? 'admin');

        $kodeBaru = $this->codeService->generateKode($validated['email'], $adminEmail);

        return back()->with('success', 'Kode akses untuk '.$validated['email'].' berhasil dibuat: '.$kodeBaru->kode);
    }
}
