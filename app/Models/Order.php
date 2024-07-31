<?php

namespace App\Models;

use App\Models\Table;
use App\Models\RestaurantRoom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        "restaurant_id",
        "user_id",
        "order_time",
        "guest",
        "status"
    ];
    function user(){
        return $this->belongsTo(User::class);
    }
    function tables(){
        return $this->belongsTo(Table::class,'table_id');
    }
    function rooms(){
        return $this->belongsTo(RestaurantRoom::class,'room_id');
    }

    public function restaurant()
    {
        return $this->hasOneThrough(
            Restaurant::class,
            Table::class,
            'restaurant_id',
            'id',
            'table_id',
            'restaurant_id'
        );
    }
}
