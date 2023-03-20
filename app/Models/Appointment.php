<?php

namespace App\Models;

use App\QueryFilters\DateRange;
use App\QueryFilters\Sort;
use App\QueryFilters\Status;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Pipeline\Pipeline;

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
        'user',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function hasBeenPaidFor(): bool
    {
        return $this->transaction?->count() > 0 && $this->transaction->status === 'success';
    }

    public static function allAppointments()
    {
        return app(Pipeline::class)
                ->send(Appointment::query())
                ->through([
                    Status::class,
                    Sort::class,
                    DateRange::class,
                ])->thenReturn();
    }
}
