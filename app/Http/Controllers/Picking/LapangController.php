<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Picking\AreaOperatorService;
use App\Services\Picking\PickingPartService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Layar lapangan untuk operator yang memakai HP landscape di lengan.
 *
 * Berbeda dengan halaman admin (`picking/*`) yang memakai tabel lengkap,
 * layar lapangan dirancang untuk dilihat sekilas dan ditekan satu tangan:
 * satu part per layar, kode rak dan jumlah dibesarkan, tombol besar.
 *
 * Semua data disaring ke area rak jatah operator — operator hanya melihat
 * dan mengerjakan part di rak yang menjadi tanggung jawabnya.
 */
class LapangController extends Controller
{
    private const STATUS_LAPANGAN = 'Waiting'; // Hanya DO dengan part yang masih waiting

    public function __construct(
        private readonly PickingPartService $pickingPart,
        private readonly AreaOperatorService $areaOperator,
    ) {
    }

    /**
     * Layar awal: daftar DO yang masih punya part menunggu di area operator.
     * Operator memilih satu DO untuk mulai bekerja.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        assert($user instanceof AdminUser);

        $paginator = $this->pickingPart->daftarDo($user, ['status' => self::STATUS_LAPANGAN]);

        return Inertia::render('picking/lapangan/Index', [
            'daftarDo' => $paginator,
            'areaOperator' => $this->areaOperator->areaUntuk($user),
        ]);
    }

    /**
     * Layar kerja satu DO: seluruh part milik operator di DO ini, urut
     * lokasi rak (operator jalan menyusuri rak) — waiting dulu, done di
     * bawah. Frontend menampilkan satu part per layar dan maju sendiri
     * setelah operator menekan "Sudah Diambil".
     */
    public function kerja(Request $request): Response
    {
        $fkDo = (string) $request->query('do');
        abort_unless($fkDo !== '', 404);

        $user = $request->user();
        assert($user instanceof AdminUser);

        $daftarPart = $this->pickingPart->daftarPartDalamDo($user, $fkDo);

        abort_if($daftarPart === [], 404, 'DO tidak ditemukan atau tidak ada part di area Anda.');

        $barisPertama = $daftarPart[0];

        return Inertia::render('picking/lapangan/Kerja', [
            'fkDo' => $fkDo,
            'daftarPart' => $daftarPart,
            'isBundling' => $this->pickingPart->doBundling($fkDo),
            'infoDo' => [
                'nama_channel' => $barisPertama['nama_channel'],
                'area' => $barisPertama['area'],
                'keterangan_picking' => $barisPertama['keterangan_picking'],
                'tgl_picking_list_part' => $barisPertama['tgl_picking_list_part'],
            ],
        ]);
    }
}
