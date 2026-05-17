<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToolPermission extends Model
{
    protected $fillable = [
        'user_id',
        'tool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}