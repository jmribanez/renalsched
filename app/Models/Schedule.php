<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\Technician;

class Schedule extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $fillable = ['schedule', 'technician_id', 'shift'];

    public function technician(): BelongsTo {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public static function getGeneratedMonths() {
        return self::select(DB::raw('DISTINCT DATE_FORMAT(schedule, "%Y-%m") AS schedule'))
            ->groupBy('schedule')
            ->orderBy('schedule')
            ->get();
    }

    public static function getMonth($year, $month) {
        return DB::table('schedules')
            ->whereBetween('schedule', [$year.'-'.$month.'-01',$year.'-'.$month.'-31'])
            ->orderBy('technician_id')
            ->get();
    }
}
