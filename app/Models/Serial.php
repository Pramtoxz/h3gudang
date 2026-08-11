<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Serial extends Model
{
    protected $connection = 'pgsql_live';

    protected $table = 'public.tblserial';

    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'counter',
        'last_date',
    ];

    protected function casts(): array
    {
        return [
            'counter' => 'integer',
            'last_date' => 'datetime',
        ];
    }

    public static function generateSO(): string
    {
        return DB::connection('pgsql_live')->transaction(function (): string {
            $serial = self::where('name', 'POD-PD')->lockForUpdate()->first();

            if (! $serial) {
                $serial = self::create([
                    'name' => 'POD-PD',
                    'counter' => 0,
                    'last_date' => now(),
                ]);
            }

            $counterBaru = $serial->counter + 1;

            $serial->update([
                'counter' => $counterBaru,
                'last_date' => now(),
            ]);

            return sprintf('%s/%06d/POD-PD', date('Y'), $counterBaru);
        });
    }
}
