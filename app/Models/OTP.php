<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'otps';

    /**
     * @var array
     */
    protected $fillable = ['code', 'user_id', 'number', 'is_activated', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
}
