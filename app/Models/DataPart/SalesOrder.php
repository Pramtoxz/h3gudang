<?php

namespace App\Models\DataPart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tblso';

    protected $primaryKey = 'no_so';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * Satu-satunya tabel DMS yang boleh ditulis aplikasi ini: checkout PMO
     * membuat Sales Order baru di sistem dealer.
     */
    protected $fillable = [
        'no_so',
        'jenis_so',
        'tgl_so',
        'jenis_pembayaran',
        'fk_salesman',
        'tipe_source',
        'fk_toko',
        'tipe_penjualan',
        'tgl_jatuh_tempo',
        'grand_total',
        'status_outstanding',
        'status_approve_reject',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_so' => 'datetime',
            'tgl_jatuh_tempo' => 'datetime',
            'status_outstanding' => 'boolean',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class, 'fk_so', 'no_so');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'fk_so', 'no_so');
    }
}
