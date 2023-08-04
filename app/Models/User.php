<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * @property integer $id
 * @property integer $trash_bank_id
 * @property string $username
 * @property string $full_name
 * @property string $email
 * @property integer $phone
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 * @property TrashStoreLog[] $trashStoreLogs
 * @property TrashBank $trashBank
 * @property SavingsBalance[] $savingsBalances
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * @var array
     */
    protected $fillable = ['trash_bank_id', 'username', 'profile_picture', 'full_name', 'email', 'address', 'is_verified', "role", 'no_kk', 'phone', 'password', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trashStoreLogs()
    {
        return $this->hasMany('App\Models\TrashStoreLog');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trashBank()
    {
        return $this->belongsTo('App\Models\TrashBank');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function savingsBalances()
    {
        return $this->hasMany('App\Models\SavingsBalanceData');
    }
    protected $hidden = [
        'password'
    ];
    public function authorizeRoles($roles)
    {
      if ($this->hasAnyRole($roles)) {
        return true;
      }
      abort(401, 'This action is unauthorized.');
    }

    public function hasAnyRole($roles)
    {
      if (is_array($roles)) {
        foreach ($roles as $role) {
          if ($this->hasRole($role)) {
            return true;
          }
        }
      } else {
        if ($this->hasRole($roles)) {
          return true;
        }
      }
      return false;
    }

    public function hasRole($role)
    {
      if ($this->role == $role) {
        return true;
      }
      return false;
    }
}
