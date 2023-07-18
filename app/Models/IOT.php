<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $code
 * @property integer $weight
 * @property string $created_at
 * @property string $updated_at
 * @property SavingsBalance[] $savingsBalances
 */
class IOT extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'iots';

    /**
     * @var array
     */
    protected $fillable = ['garbage_savings_data_id','code', 'weight', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trashData()
    {
        return $this->belongsTo(GarbageSavingsData::class, 'iot_id');
    }
}
