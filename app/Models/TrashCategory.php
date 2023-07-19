<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GarbageSavingsData;

class TrashCategory extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'category_name', 'created_at', 'updated_at'];

    protected $table = 'trash_categories';

    public function garbageSavingsData(){
        return $this->hasMany(GarbageSavingsData::class, 'trash_category_id', 'id' );
    }
}
