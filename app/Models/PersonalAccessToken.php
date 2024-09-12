<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'token',
        'refresh_token',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
