<?php

namespace App\Models\DataPart;

use App\Models\DataFA\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryOrder extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_part.tbldo';

    protected $primaryKey = 'no_do';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'tgl_do' => 'datetime',
            'tgl_approve' => 'datetime',
            'total_gross' => 'decimal:2',
            'total_diskon' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'fk_so', 'no_so');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'fk_do', 'no_do');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'fk_do_part', 'no_do');
    }
}
