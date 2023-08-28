<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $fillable = ['schedule', 'technician_id', 'shift'];

    public function technician(): BelongsTo {
        return $this->belongsTo(Technician::class);
    }
}
