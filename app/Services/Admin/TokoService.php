<?php

namespace App\Services\Admin;

use App\Exceptions\TokoMasihDipakaiException;
use App\Models\Toko;
use App\Models\User;
use App\Repositories\TokoRepository;

class TokoService
{
    public const TIPE_DISKON_DEFAULT = 'Persen';

    private const ALAMAT_KOSONG = '-';

    public function __construct(private readonly TokoRepository $tokoRepository)
    {
    }

    public function daftar(): array
    {
        return Toko::query()
            ->orderBy('kd_toko')
            ->get(['kd_toko', 'toko', 'no_telp', 'toko_active'])
            ->map(fn (Toko $toko): array => [
                'kd_toko' => $toko->kd_toko,
                'toko' => $toko->toko,
                'no_telp' => $toko->no_telp,
                'toko_active' => (bool) $toko->toko_active,
            ])
            ->all();
    }

    public function detail(Toko $toko): array
    {
        $daftarUser = $this->tokoRepository->daftarUserToko($toko->kd_toko);

        return [
            'toko' => $this->atribut($toko),
            'daftarUser' => $daftarUser,
            'pin_collection_terpasang' => collect($daftarUser)->contains(
                fn (array $user): bool => $user['punya_pin'],
            ),
        ];
    }

    public function atribut(Toko $toko): array
    {
        return [
            'kd_toko' => $toko->kd_toko,
            'toko' => $toko->toko,
            'no_telp' => $toko->no_telp,
            'npwp' => $toko->npwp,
            'alamat' => $toko->alamat,
            'kategori' => $toko->kategori,
            'kd_ahm' => $toko->kd_ahm,
            'toko_active' => (bool) $toko->toko_active,
        ];
    }

    public function simpan(array $data): Toko
    {
        return Toko::create([
            ...$data,
            'alamat' => $data['alamat'] ?: self::ALAMAT_KOSONG,
            'tipe_diskon' => self::TIPE_DISKON_DEFAULT,
        ]);
    }

    public function perbarui(Toko $toko, array $data): void
    {
        $toko->update([
            ...$data,
            'alamat' => $data['alamat'] ?: self::ALAMAT_KOSONG,
        ]);
    }

    public function hapus(Toko $toko): void
    {
        $jumlahUser = $this->tokoRepository->jumlahUser($toko->kd_toko);

        if ($jumlahUser > 0) {
            throw new TokoMasihDipakaiException($jumlahUser);
        }

        $toko->delete();
    }

    public function resetPinCollection(Toko $toko): int
    {
        return User::query()
            ->where('fk_toko', $toko->kd_toko)
            ->whereNotNull('collection_pin')
            ->update(['collection_pin' => null]);
    }
}
