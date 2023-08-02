<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GarbageSavingsData;

class TransactionLog extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['code', 'type', 'user_id', 'garbage_savings_data_id', 'amount', 'trash_bank_id' , 'is_approved', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    public function garbageSavingsData()
    {
        return $this->belongsTo(GarbageSavingsData::class, 'garbage_savings_data_id');
    }
}
