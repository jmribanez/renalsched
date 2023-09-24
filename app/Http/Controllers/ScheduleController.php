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
        $generatedMonth = date_create(($year)."-".($month)."-01");          // generatedMonth is the current month's date object
        $scheduleMonth = Schedule::getMonth($year,$month);                  // scheduleMonth is the schedule of all technicians for the month
        $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);

        // Create an array for the number of staff per shift
        $dawShift = array_fill(0, $calendarDays+1, 0);
        $morShift = array_fill(0, $calendarDays+1, 0);
        $aftShift = array_fill(0, $calendarDays+1, 0);
        $eveShift = array_fill(0, $calendarDays+1, 0);
        $offShift = array_fill(0, $calendarDays+1, 0);
        $staffDay = array_fill(0, $calendarDays+1, 0);

        // Read the scheduleMonth array to tally
        foreach($scheduleMonth as $sm) {
            $index = intval(date_format(date_create($sm->schedule),"j"));
            switch($sm->shift) {
                case 'D':
                    $dawShift[$index]++;
                    break;
                case 'M':
                    $morShift[$index]++;
                    break;
                case 'A':
                    $aftShift[$index]++;
                    break;
                case 'E':
                    $eveShift[$index]++;
                    break;
                default:
                    $offShift[$index]++;
                    break;
            }
            if($sm->shift!='O') {
                $staffDay[$index]++;
            }
        }
        $staffPerShift = array($dawShift,$morShift,$aftShift,$eveShift,$offShift);

        
        // Create previous and next dates
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
        
        // Show the views
        return view('schedules.index')
            ->with('generatedMonth',$generatedMonth)
            ->with('scheduleMonth', $scheduleMonth)
            ->with('staffPerShift', $staffPerShift)
            ->with('staffDay',$staffDay)
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

        // Identify Sundays and Weekdays
        $sundays = array();
        $weekdays = array();
        for($i=1; $i<=$calendarDays; $i++) {
            if(substr(date_format(date_create(($year)."-".($month)."-".$i),"D"),0,2)=="Su")
                array_push($sundays,$i);
            else
                array_push($weekdays,$i);
        }

        // For the meantime, select all technicians, get all id's
        $technicians = Technician::getTechnicians();

        // Start of initial solution
        // initialSolution contains an array of arrays each representing a technician in order of their ID
        // values of the arrays are the days of their shifts and what shift it is
        $initialSolution = array();
        foreach($technicians as $t) {
            // For readability, skip index 0
            $days = array();

            /**
             * TODO: Foreach sunday, randomly assign one technician
             *       Note who got assigned so they only need to work one Sunday.
             * 
             *       store all technician shift data to initialSolution
             *       Then bestSolution is initialSolution for now
             *       Modify code to store bestSolution in database
             * 
             *       
             */

            // By default, assign all Sundays to "O"
            foreach($sundays as $sun) {
                $days[$sun] = "O";
            }

            // Randomly assign 8 off days
            $offcount = sizeof($sundays);
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
     * This function computes the "brightness" of a firefly
     */
    private function getObjectiveFunctionValue() {
        /**
         * workingDays is an array of nTechnicians x nDays
         * value gets compute Days Off penalization
         * value getsadds compute Shift penalization
         * value getsadds compute Shift Off penalization
         */
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
