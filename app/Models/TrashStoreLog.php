<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $store_date
 * @property string $trash_category
 * @property string $address
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class TrashStoreLog extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'store_date', 'trash_category', 'address', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
