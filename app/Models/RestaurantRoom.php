<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantRoom extends Model
{
    use HasFactory;
    protected $fillable = [
        'restaurant_id',
        'room_number'
    ] ;
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class,'room_id');
    }
    
}
