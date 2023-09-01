<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['isSenior', 'name_family', 'name_first'];

    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class);
    }

    public static function getSenior() {
        return self::where('isSenior','1')->get()->count();
    }

    public static function getOrdinary() {
        return self::where('isSenior','0')->get()->count();
    }

    public static function getTechnicians() {
        return self::all();
    }
}
