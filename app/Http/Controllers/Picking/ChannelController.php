<?php

namespace App\Http\Controllers\Picking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Picking\SimpanChannelRequest;
use App\Models\H3\AreaChannel;
use App\Services\Picking\ChannelService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChannelController extends Controller
{
    public function __construct(private readonly ChannelService $channelService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('picking/channel/Index', [
            'daftarChannel' => $this->channelService->daftar(),
            'daftarArea' => $this->channelService->daftarArea(),
        ]);
    }

    public function store(SimpanChannelRequest $request): RedirectResponse
    {
        $this->channelService->simpan($request->validated());

        return to_route('picking.channel.index')->with('success', 'Channel berhasil ditambahkan');
    }

    public function update(SimpanChannelRequest $request, AreaChannel $channel): RedirectResponse
    {
        $this->channelService->perbarui($channel, $request->validated());

        return to_route('picking.channel.index')->with('success', 'Channel berhasil diperbarui');
    }

    public function destroy(AreaChannel $channel): RedirectResponse
    {
        $this->channelService->hapus($channel);

        return to_route('picking.channel.index')->with('success', 'Channel berhasil dihapus');
    }
}
