<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'vehicle_id',
        'title',
        'description',
        'price',
        'status'
    ];

    //Una publicación puede tener una venta
    public function sales()
    {
        return $this->hasOne(Sale::class);
    }
}
