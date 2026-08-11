<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Services\Admin\TokoService;
use Illuminate\Http\RedirectResponse;

class TokoPinController extends Controller
{
    public function __construct(private readonly TokoService $tokoService)
    {
    }

    public function __invoke(Toko $toko): RedirectResponse
    {
        $jumlahUser = $this->tokoService->resetPinCollection($toko);

        if ($jumlahUser === 0) {
            return back()->with('info', 'Tidak ada PIN yang perlu direset untuk toko ini');
        }

        return back()->with(
            'success',
            "PIN Collection berhasil direset untuk {$jumlahUser} user. User dapat setup PIN baru."
        );
    }
}
