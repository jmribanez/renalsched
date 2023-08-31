<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Technician;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index() {
        $seniorCount = Technician::getSenior();
        $ordinaryCount = Technician::getOrdinary();
        $generatedMonths = Schedule::getGeneratedMonths();
        return view('welcome')
            ->with('seniorCount', $seniorCount)
            ->with('ordinaryCount', $ordinaryCount)
            ->with('generatedMonths', $generatedMonths);
    }
}
