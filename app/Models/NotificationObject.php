<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationObject extends Model
{
    use HasFactory;
    protected $fillable = [
        "entity_type_id",
        "entity_id",
    ];
}
