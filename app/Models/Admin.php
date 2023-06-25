<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property integer $phone
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 */
class Admin extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['username', 'email', 'phone', 'password', 'created_at', 'updated_at'];
}
