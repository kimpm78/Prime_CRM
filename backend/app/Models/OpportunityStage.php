<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityStage extends Model
{
    protected $fillable = ['stage_name', 'probability', 'sort_order'];

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
