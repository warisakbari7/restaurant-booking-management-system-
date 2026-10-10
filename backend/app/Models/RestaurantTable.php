<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;



    #[Fillable([
        'table_number',
        'capacity',
        'status',
        'floor'
    ])]
class RestaurantTable extends Model
{
    public function reservations()
    {
        return $this->belongsToMany(Reservation::class,'reservation_tables');
    }
}
