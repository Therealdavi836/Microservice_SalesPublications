<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'customer_id',
        'seller_id', // 👈 añadido
        'sale_price',
        'sale_date'
    ];

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
}
