<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = [
        'base_url',
        'database',
        'period_indicator',
        'scheduler_active',
        'scheduler_interval',
        'max_retries',
    ];
}
