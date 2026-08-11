<?php

namespace App\Models\DataFA;

use App\Models\DataPart\DeliveryOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'data_fa.tblinvoice_dealer_part';

    protected $primaryKey = 'pk_id_dealer_part';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'tgl_faktur' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class, 'fk_invoice', 'pk_id_dealer_part');
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'fk_do_part', 'no_do');
    }

    public function accountReceivable(): HasOne
    {
        return $this->hasOne(AccountReceivable::class, 'no_transaksi', 'no_faktur');
    }
}
