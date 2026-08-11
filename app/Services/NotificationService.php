<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private const JUDUL_PESANAN = [
        'created' => 'Order Berhasil Dibuat',
        'processing' => 'Order Sedang Diproses',
        'shipped' => 'Order Telah Dikirim',
        'delivered' => 'Order Telah Sampai',
        'cancelled' => 'Order Dibatalkan',
    ];

    private const PESAN_PESANAN = [
        'created' => 'Order #:nomor telah berhasil dibuat',
        'processing' => 'Order #:nomor sedang diproses',
        'shipped' => 'Order #:nomor telah dikirim',
        'delivered' => 'Order #:nomor telah sampai di tujuan',
        'cancelled' => 'Order #:nomor telah dibatalkan',
    ];

    public function __construct(private readonly FirebaseService $firebase)
    {
    }

    public function kirimKeUser(int $userId, string $judul, string $pesan, string $tipe = 'general', array $data = []): array
    {
        try {
            $user = User::find($userId);

            if (! $user) {
                Log::warning('NotificationService: user tidak ditemukan', ['user_id' => $userId]);

                return ['success' => false, 'message' => 'User not found'];
            }

            $notifikasi = Notification::create([
                'kd_toko' => $user->fk_toko,
                'judul' => $judul,
                'pesan' => $pesan,
                'tipe' => $tipe,
                'sudah_dibaca' => false,
            ]);

            if (! $user->fcm_token) {
                return ['success' => true, 'message' => 'Notification saved but not sent (no FCM token)'];
            }

            $data['type'] = $tipe;
            $data['notification_id'] = (string) $notifikasi->id;

            $hasil = $this->firebase->sendToDevice($user->fcm_token, $judul, $pesan, $data);

            $this->bersihkanTokenTidakValid($user, $hasil);

            return $hasil;
        } catch (\Throwable $e) {
            Log::error('NotificationService Error: ' . $e->getMessage(), ['user_id' => $userId, 'tipe' => $tipe]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function kirimNotifikasiPesanan(int $userId, string $nomorPesanan, string $status): array
    {
        return $this->kirimKeUser(
            $userId,
            self::JUDUL_PESANAN[$status] ?? 'Update Order',
            str_replace(
                ':nomor',
                $nomorPesanan,
                self::PESAN_PESANAN[$status] ?? 'Order #:nomor telah diupdate'
            ),
            'order',
            ['order_number' => $nomorPesanan, 'status' => $status]
        );
    }

    private function bersihkanTokenTidakValid(User $user, array $hasil): void
    {
        $tokenBermasalah = ! $hasil['success']
            && in_array($hasil['error_type'] ?? null, ['invalid_token', 'token_not_found'], true);

        if (! $tokenBermasalah) {
            return;
        }

        Log::warning('Menghapus FCM token yang tidak valid', ['user_id' => $user->id]);

        $user->fcm_token = null;
        $user->save();
    }
}
