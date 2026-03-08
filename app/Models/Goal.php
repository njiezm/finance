<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'target_amount',
        'current_amount',
        'target_date',
        'type',
        'cadence',
        'start_date',
        'is_archived',
        'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
        'archived_at' => 'datetime',
        'is_archived' => 'boolean',
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];
}