<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'institution',
        'type',
        'currency',
        'current_balance',
        'ceiling_amount',
        'target_amount',
        'is_active',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'ceiling_amount' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
