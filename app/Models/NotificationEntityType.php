<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationEntityType extends Model
{
    use HasFactory;
    protected $fillable = [
        "entity_table",
        "notification_type",
        "description"
    ];
}
