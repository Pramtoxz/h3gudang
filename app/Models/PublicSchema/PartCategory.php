<?php

namespace App\Models\PublicSchema;

use App\Models\PartCategoryImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PartCategory extends Model
{
    protected $connection = 'pgsql_dms';

    protected $table = 'public.tbldetail_sub_kelompok_part_id';

    protected $primaryKey = 'kd_detail_sub_kelompok_part';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function image(): HasOne
    {
        return $this->hasOne(PartCategoryImage::class, 'kode_kelompok', 'kd_detail_sub_kelompok_part');
    }
}
