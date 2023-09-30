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

        // Create the days counter
        $sundaysCount = array_fill(0,sizeof($sundays),0);
        $weekdaysCount = array_fill(0,sizeof($weekdays),0);

        // For the meantime, select all technicians, get all id's
        $technicians = Technician::getTechnicians();

        // Start of initial solution
        // initialSolution is an array of arrays each representing a technician and their shifts for the calendarMonth
        // values of the arrays are their shifts for the day.
        $initialSolution = array();
        foreach($technicians as $t) {
            // the $days variable will now be the array fed to the $initialSolution array
            $days = array();
            // $days[0] will be the technician id
            $days[0] = $t->id;

            /**
             * TODO: Foreach sunday, randomly assign one technician
             *       Note who got assigned so they only need to work one Sunday.
             * 
             *       store all technician shift data to initialSolution
             *       Then bestSolution is initialSolution for now
             *       Modify code to store bestSolution in database    
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

            // Assign Sundays per Constraint 8.
            // All technicians must have one extended shift on a Sunday. This only needs to run once.
            $sundayShiftDay = $this->dayBalancer($sundays, $sundaysCount, $days, true);
            $days[$sundayShiftDay] = "H";
            $sundaysCount[$sundayShiftDay]++;

            // Assign weekdays
            foreach($weekdays as $wd) {
                $weekdayShiftDay = $this->dayBalancer($weekdays, $weekdaysCount, $days, false);
                $days[$weekdayShiftDay] = $this->shifter();
                $weekdaysCount[$weekdayShiftDay]++;
            }

            // Save the entries to the database
            for($j=1; $j<=$calendarDays; $j++) {
                $sched = new Schedule();
                $sched->schedule = $year."-".$month."-".$j;
                $sched->technician_id = $days[0];
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
     * This function takes in arrays of days (weekdays or sundays) and counts. It randomly looks for days that 
     * do not yet have a lot of assigned technicians based on $counts and returns the selected day as an integer.
     * 
     * It refers to $days to see if the day is already occupied.
     * $sunweek and $counts have the same size
     */
    private function dayBalancer($sunweek, $counts, $days, $isSunday) {
        // Do Get the smallest value in the $counts array.
        //      Remember the indices of those with the smallest values based from the $sunweek variable and store in an array.
        //      Set selectedDay to 0
        //      Do Randomly choose from the array of smallest values
        //          If the value of the chosen index is available on $days, set SelectedDay to nonzero
        //          While selectedDay is 0
        //      smallest value++
        //      While selectedDay is 0
        $selectedDay = 0;
        $smallestValue = 100;
        $smallestValueArray = array();
        do {
            for($i=0; $i<sizeof($sunweek); $i++) {
                if($counts[$i] < $smallestValue) {
                    $smallestValue = $counts[$i];
                    $smallestValueArray = array();
                } else if($counts[$i] == $smallestValue) {
                    array_push($smallestValueArray,$sunweek[$i]);
                }
            }
            do {
                die(print_r($smallestValueArray));
                $randomIndex = rand(0,sizeof($smallestValueArray)-1);
                if(empty($days[$smallestValueArray[$randomIndex]] || $isSunday)) {
                    $selectedDay = $sunweek[$randomIndex];
                    return $selectedDay;
                }
                unset($smallestValueArray[$randomIndex]);
            } while($selectedDay == 0);
            $smallestValue++;
        } while($selectedDay == 0);
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
