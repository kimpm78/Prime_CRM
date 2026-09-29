<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStatus extends Model
{
    protected $fillable = ['status_name', 'sort_order'];
}
