<?php

namespace App\Services;

use App\Models\OTP;
use Carbon\Carbon;

class OTPService
{
    private const MASA_BERLAKU_MENIT = 5;

    public function __construct(private readonly WhatsAppGateway $whatsapp)
    {
    }

    public function generateOTP(string $nohp, string $type = 'register'): string
    {
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OTP::create([
            'nohp' => $nohp,
            'otp_code' => $otpCode,
            'type' => $type,
            'is_used' => false,
            'expires_at' => Carbon::now()->addMinutes(self::MASA_BERLAKU_MENIT),
        ]);

        return $otpCode;
    }

    public function sendOTP(string $nohp, string $otpCode): void
    {
        $pesan = " *SALAM SATU HATI*\nKode OTP Anda: *{$otpCode}*\n\nKode berlaku selama "
            . self::MASA_BERLAKU_MENIT . " menit.\nJangan bagikan kode ini kepada siapapun.";

        $this->whatsapp->sendToPhone($nohp, $pesan);
    }

    public function verifyOTP(string $nohp, string $otpCode): bool
    {
        $otp = OTP::where('nohp', $nohp)
            ->where('otp_code', $otpCode)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $otp) {
            return false;
        }

        $otp->update(['is_used' => true]);

        return true;
    }
}
