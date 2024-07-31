<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location',
        'phone',
        'allow_ordering',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function images()
    {
        return $this->hasMany(RestaurantImage::class);
    }
    public function tables()
    {
        return $this->hasMany(Table::class);
    }
    public function rooms()
    {
        return $this->hasMany(RestaurantRoom::class);
    }
    public function manager_restaurant()
    {
        return $this->belongsTo(User::class,'manager');
    }
}
