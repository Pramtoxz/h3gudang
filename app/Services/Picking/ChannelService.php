<?php

namespace App\Services\Picking;

use App\Models\H3\AreaChannel;

class ChannelService
{
    public function daftar(): array
    {
        return AreaChannel::query()
            ->orderBy('area')
            ->orderBy('kode_channel')
            ->get(['id', 'area', 'kode_channel', 'nama_channel', 'nama_invoice'])
            ->map(fn (AreaChannel $channel): array => [
                'id' => $channel->id,
                'area' => $channel->area,
                'kode_channel' => $channel->kode_channel,
                'nama_channel' => $channel->nama_channel,
                'nama_invoice' => $channel->nama_invoice,
            ])
            ->all();
    }

    public function daftarArea(): array
    {
        return AreaChannel::query()
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->all();
    }

    public function simpan(array $data): AreaChannel
    {
        return AreaChannel::create($data);
    }

    public function perbarui(AreaChannel $channel, array $data): void
    {
        $channel->update($data);
    }

    public function hapus(AreaChannel $channel): void
    {
        $channel->delete();
    }
}
