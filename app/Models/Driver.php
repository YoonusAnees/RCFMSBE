<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory, HasUuids; // ✅ Add HasUuids to auto-generate UUIDs

    protected $table = 'drivers';

    public $incrementing = false;   // ✅ UUIDs are not auto-increment
    protected $keyType = 'string';  // ✅ UUIDs are strings

    protected $fillable = [
        'user_id',
        'license_number',
        'phone_number',
        'address',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
