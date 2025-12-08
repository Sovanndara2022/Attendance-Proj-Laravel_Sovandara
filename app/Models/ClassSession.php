<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSession extends Model
{
    protected $fillable = ['group_id', 'starts_at', 'ends_at', 'topic', 'is_substitute'];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_substitute' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_session_id');
    }

    // --- helpers used by blades ---
    public function label(): string
    {
        return sprintf('%s - %s', $this->starts_at->format('H:i'), $this->ends_at->format('H:i'));
    }

    public function dayKey(): string
    {
        return $this->starts_at->format('Y-m-d');
    }
}
