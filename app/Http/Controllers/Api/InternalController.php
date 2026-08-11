<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RefreshCollectionCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    public function refreshCollectionCache(Request $request): JsonResponse
    {
        $kunci = config('app.internal_cron_key');

        if (blank($kunci) || ! hash_equals($kunci, (string) $request->header('X-Internal-Key'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        RefreshCollectionCache::dispatch();

        return response()->json(['message' => 'Cache refresh dimulai di background']);
    }
}
