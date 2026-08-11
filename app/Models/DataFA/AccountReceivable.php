<?php

namespace App\Models\DataFA;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountReceivable extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_fa.tblar';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'tgl_transaksi' => 'datetime',
            'bulan' => 'integer',
            'tahun' => 'integer',
            'jumlah_transaksi' => 'decimal:2',
            'jumlah_alokasi' => 'decimal:2',
            'jumlah_approve' => 'decimal:2',
            'saldo' => 'decimal:2',
            'is_data_migrasi' => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'no_transaksi', 'no_faktur');
    }

    protected function isPaid(): Attribute
    {
        return Attribute::get(fn (): bool => $this->saldo == 0);
    }

    protected function isOutstanding(): Attribute
    {
        return Attribute::get(fn (): bool => $this->saldo > 0);
    }
}
