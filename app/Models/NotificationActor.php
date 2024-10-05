<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationActor extends Model
{
    use HasFactory;
    protected $fillable = [
        "notification_object_id",
        "actor_id",
    ];
}
