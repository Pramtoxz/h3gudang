<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class KatalogKendaraan extends Model
{
    protected $connection = 'pgsql_pmo';

    protected $table = 'katalog_kendaraan';

    protected $fillable = [
        'kode_motor',
        'nama_motor',
        'tahun_motor',
        'no_rangka',
        'nama_file',
        'kategori',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected function tahunMotorArray(): Attribute
    {
        return Attribute::get(function (): array {
            if (! $this->tahun_motor) {
                return [];
            }

            return array_filter(array_map('trim', explode(',', $this->tahun_motor)));
        });
    }

    protected function noRangkaArray(): Attribute
    {
        return Attribute::get(function (): array {
            if (! $this->no_rangka || $this->no_rangka === '-') {
                return [];
            }

            return array_filter(array_map('trim', explode(',', $this->no_rangka)));
        });
    }

    protected function pdfPath(): Attribute
    {
        return Attribute::get(fn (): string => 'pdf/' . $this->nama_file);
    }
}
