<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantLike extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'plant_id',
        'like'
    ];

    public function user (){
        return $this.$this->hasOne(User::class, 'id', 'user_id' );
    }

    public function plant() {
        return $this.$this->hasMany(Plant::class, 'id', 'plant_id' );
    }
}
