<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionCacheStatus extends Model
{
    protected $table = 'pmov2.collection_cache_status';

    protected $fillable = [
        'status',
        'last_refresh_at',
        'total_shops_processed',
        'total_records',
        'duration_seconds',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'last_refresh_at' => 'datetime',
            'total_shops_processed' => 'integer',
            'total_records' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }
}
