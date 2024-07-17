<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    function restaurant(){
        return $this->belongsTo(Restaurant::class);
    }
}
