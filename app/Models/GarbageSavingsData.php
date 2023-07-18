<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $iot_id
 * @property integer $balance
 * @property string $trash_category
 * @property integer $weight
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 * @property Iot $iot
 */
class GarbageSavingsData extends Model
{
    /**
     * @var array
     */
    protected $table = 'garbage_savings_datas';

    protected $fillable = ['user_id', 'balance', 'trash_category', 'weight', 'iot_id', 'store_date', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function iot()
    {
        return $this->hasOne(IOT::class, 'id', 'iot_id');
    }
}
