<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Technician;
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
        $year = $year??date('Y');
        $month = $month??date('m');
        $generatedMonth = date_create(($year)."-".($month)."-01");
        $scheduleMonth = Schedule::getMonth($year,$month);
        $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $prevYear = $year;
        $prevMonth = $month-1;
        if($prevMonth<1) {
            $prevYear--;
            $prevMonth = 12;
        }
        $previousDate = date_create(($prevYear)."-".($prevMonth)."-01");
        $nextYear = $year;
        $nextMonth = $month+1;
        if($nextMonth>12) {
            $nextYear++;
            $nextMonth = 1;
        }
        $nextDate = date_create(($nextYear)."-".($nextMonth)."-01");
        return view('schedules.index')
            ->with('generatedMonth',$generatedMonth)
            ->with('scheduleMonth', $scheduleMonth)
            ->with('previousDate', $previousDate)
            ->with('nextDate', $nextDate)
            ->with('calendarDays', $calendarDays);
    }

    /**
     * Generate the schedule for a month using given parameters
     */
    public function generate(Request $request)
    {
        // For the meantime, generate will create a schedule for October 2023
        // because Day 1 starts on a Sunday. Just for visualization purposes.

        // Validation of needed data
        $this->validate($request, ['month'=>'required','year'=>'required']);

        $month = $request->month;
        $year = $request->year;

        // Identify all calendar days
        $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);

        // For the meantime, select all technicians, get all id's
        $technicians = Technician::getTechnicians();

        foreach($technicians as $t) {
            // For readability, skip index 0
            $days = array();

            // Randomly assign 8 off days
            $offcount = 0;
            while($offcount < 8){
                $randomDay = rand(1,$calendarDays);
                if(empty($days[$randomDay])) {
                    $days[$randomDay] = "O";
                    $offcount++;
                }
            }

            // Progressively assign random shifts if $days[index] is empty
            for($j=1; $j<=$calendarDays; $j++) {
                if(empty($days[$j])) {
                    $days[$j] = $this->shifter();
                }
                $sched = new Schedule();
                $sched->schedule = $year."-".$month."-".$j;
                $sched->technician_id = $t->id;
                $sched->shift = $days[$j];
                $sched->save();
            }
        }

        return redirect('schedules/'.$year."/".$month)->with('success','Schedule Generated.');
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
    public function destroy(string $year, string $month)
    {
        //
        $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        Schedule::whereBetween('schedule',[$year."-".$month."-01",$year."-".$month."-".$calendarDays])->delete();
        return redirect('schedules/'.$year."/".$month)->with('success','Schedule Deleted.');
    }
}
