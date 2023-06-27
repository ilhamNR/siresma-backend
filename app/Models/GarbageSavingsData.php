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
class SavingsBalanceData extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'iot_id', 'balance', 'trash_category', 'weight', 'created_at', 'updated_at'];

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
        return $this->belongsTo('App\Models\Iot');
    }
}
