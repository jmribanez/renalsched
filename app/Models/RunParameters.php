<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RunParameters extends Model
{
    use HasFactory;

    protected $fillable = ['runForDate','populationSize','maxIterations','alpha','gamma','runTime','movements'];

    public static function getMonth($generatedMonth) {
        return DB::table('run_parameters')
            ->where('runForDate',$generatedMonth)
            ->first();
    }
}
