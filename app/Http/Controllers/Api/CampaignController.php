<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignDetailResource;
use App\Http\Resources\CampaignListResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::query();

        if ($request->has('type')) {
            $query->where('badge', 'LIKE', '%' . $request->get('type') . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        return ApiResponse::success(CampaignListResource::collection($query->get()));
    }

    public function show(string $id): JsonResponse
    {
        return ApiResponse::success(new CampaignDetailResource(Campaign::findOrFail($id)));
    }

    public function myAchievement(): JsonResponse
    {
        $kampanye = Campaign::where('status', 'active')->first();

        if (! $kampanye) {
            return ApiResponse::success(['currentCampaign' => null]);
        }

        return ApiResponse::success([
            'currentCampaign' => [
                'id' => (string) $kampanye->id,
                'title' => $kampanye->judul,
                'endDate' => $kampanye->tanggal_selesai->format('Y-m-d H:i:s'),
                'achievementPercentage' => 0,
                'achievementLabel' => '0%',
            ],
        ]);
    }
}
