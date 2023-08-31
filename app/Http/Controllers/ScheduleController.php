<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource using specified month and year.
     */
    public function index(string $year = null, string $month = null)
    {
        //
        // Create generated date
        $generatedMonth = date_create(($year??date('Y'))."-".($month??date('m'))."-01");
        $scheduleMonth = Schedule::getMonth($year,$month);
        return view('schedules.index')
            ->with('generatedMonth',$generatedMonth)
            ->with('scheduleMonth', $scheduleMonth);
    }

    /**
     * Generate the schedule for a month using given parameters
     */
    public function generate(Request $request)
    {
        // For the meantime, generate will create a schedule for October 2023
        // because Day 1 starts on a Sunday. Just for visualization purposes.

        // Random using for loop. i -> technician code.
        for($i=1; $i<=8; $i++) {
            // For readability, skip index 0
            $days = array();
            $offcount = 0;
            // Randomly assign 8 off days
            while($offcount < 8){
                $randomDay = rand(1,31);
                if(empty($days[$randomDay])) {
                    $days[$randomDay] = "O";
                    $offcount++;
                }
            }
            // Progressively assign random shifts if $days[index] is empty
            for($j=1; $j<=31; $j++) {
                if(empty($days[$j])) {
                    $days[$j] = $this->shifter();
                }
            }
            // Set the schedule for the user at $i for each value in $days
            for($j=1; $j<=31; $j++) {
                $sched = new Schedule();
                $sched->schedule = "2023-10-".$j;
                $sched->technician_id = $i;
                $sched->shift = $days[$j];
                $sched->save();
            }
        }
        return redirect('/')->with('success','Schedule Generated.');
    }

    private function shifter() {
        $rn = rand(1,4);
        if($rn==1)
            return "D";
        else if($rn==2)
            return "M";
        else if($rn==3)
            return "A";
        else    
            return "E";
    }

    /**
     * Display the specified schedule of the user for a given month and year.
     */
    public function individual(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
