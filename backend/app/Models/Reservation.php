<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

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

    public function tables()
    {
        return $this->belongsToMany(RestaurantTable::class,'reservation_tables');
    }
}
