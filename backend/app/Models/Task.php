<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'account_id',
        'opportunity_id',
        'assigned_user_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_deleted' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
