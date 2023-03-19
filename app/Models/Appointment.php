<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'reference', 'user_id', 'doctor_id', 'datetime', 'status', 'price', 'reason', 'symptoms', 'address', 'diagnosis', 'prescription', 'comment',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    protected $with = [
        'user', 'doctor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
