<?php

namespace App\Services\Picking;

use App\Models\AdminUser;
use App\Models\H3\AksesArea;
use App\Models\OperatorLoginCode;
use Carbon\Carbon;

class OperatorLoginCodeService
{
    private const PANJANG_KODE = 6;
    private const DURASI_MENIT = 5;

    public function generateKode(string $operatorEmail, string $createdByEmail): OperatorLoginCode
    {
        $user = AdminUser::query()->where('email', $operatorEmail)->first();
        if (! $user) {
            throw new \InvalidArgumentException('Operator dengan email tersebut tidak ditemukan.');
        }

        // Hanguskan kode aktif sebelumnya milik operator ini
        OperatorLoginCode::query()
            ->where('email', $operatorEmail)
            ->whereNull('used_at')
            ->update(['expires_at' => now('Asia/Jakarta')]);

        $kode = $this->generateUniqueKode();

        return OperatorLoginCode::create([
            'email' => $operatorEmail,
            'kode' => $kode,
            'created_by_email' => $createdByEmail,
            'expires_at' => now('Asia/Jakarta')->addMinutes(self::DURASI_MENIT),
            'used_at' => null,
        ]);
    }

    public function verifikasiKode(string $kode): ?AdminUser
    {
        $codeRecord = OperatorLoginCode::query()
            ->where('kode', $kode)
            ->aktif()
            ->first();

        if (! $codeRecord) {
            return null;
        }

        $codeRecord->update(['used_at' => now('Asia/Jakarta')]);

        return AdminUser::query()->where('email', $codeRecord->email)->first();
    }

    public function daftarOperatorDenganKode(): array
    {
        $operatorList = AksesArea::query()->orderBy('email')->get();

        $dmsUsers = AdminUser::query()
            ->whereIn('email', $operatorList->pluck('email')->all())
            ->pluck('name', 'email');

        $activeCodes = OperatorLoginCode::query()
            ->aktif()
            ->whereIn('email', $operatorList->pluck('email')->all())
            ->get()
            ->keyBy('email');

        $sekarang = now('Asia/Jakarta');

        return $operatorList->map(function (AksesArea $akses) use ($dmsUsers, $activeCodes, $sekarang) {
            /** @var OperatorLoginCode|null $activeCode */
            $activeCode = $activeCodes->get($akses->email);

            $expiresAtWib = $activeCode ? Carbon::parse($activeCode->expires_at)->setTimezone('Asia/Jakarta') : null;
            $sisaDetik = $expiresAtWib ? max(0, (int) floor($sekarang->diffInSeconds($expiresAtWib, false))) : 0;

            return [
                'email' => $akses->email,
                'nama' => $dmsUsers->get($akses->email) ?? $akses->username ?? $akses->email,
                'area' => $akses->area ?? 'SEMUA AREA',
                'level' => $akses->level,
                'kode_aktif' => ($activeCode && $sisaDetik > 0) ? [
                    'kode' => $activeCode->kode,
                    'expires_at' => $expiresAtWib->toIso8601String(),
                    'sisa_detik' => $sisaDetik,
                    'created_by' => $activeCode->created_by_email,
                ] : null,
            ];
        })->all();
    }

    private function generateUniqueKode(): string
    {
        do {
            $kode = str_pad((string) random_int(100000, 999999), self::PANJANG_KODE, '0', STR_PAD_LEFT);
            $sudahAda = OperatorLoginCode::query()->where('kode', $kode)->aktif()->exists();
        } while ($sudahAda);

        return $kode;
    }
}