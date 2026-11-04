<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

    #[Fillable(['user_id','reservation_date','reservation_time',
    'guest_count',
    'status',
    'special_requests'
    ])]
class Reservation extends Model
{
    use HasFactory;    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tables()
    {
        return $this->belongsToMany(RestaurantTable::class,'reservation_tables');
    }
}
