<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    #[Fillable(['user_id','reservation_date','reservation_time',
    'guest_count',
    'status',
    'special_requests'
    ])]
class Reservation extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurantTables()
    {
        return $this->belongsToMany(RestaurantTable::class,'reservation_tables');
    }
}
