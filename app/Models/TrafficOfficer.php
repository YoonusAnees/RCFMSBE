<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrafficOfficer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_number',
        'station',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
