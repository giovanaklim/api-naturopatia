<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'name',
        'scientific_name',
        'file_path',
    ];

    public function indication() {
        return $this->hasMany(Indications::class, 'plant_id', 'id');
    }

    public function contraIndication() {
        return $this->hasMany(ContraIndications::class, 'plant_id', 'id');
    }

    public function drugInteraction() {
        return $this->hasMany(DrugInteraction::class, 'plant_id', 'id');
    }

    public  function  plantLike (){
        return $this->hasOne(PlantLike::class,'plant_id', 'id');
    }
}
