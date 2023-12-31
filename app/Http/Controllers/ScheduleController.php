<?php

namespace App\Http\Controllers;

use App\Jobs\DoGenerateSchedule;
use App\Models\RunParameters;
use App\Models\Schedule;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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
        $extShift = array_fill(0, $calendarDays+1, 0);
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
                case 'H':
                    $extShift[$index]++;
                    break;
                default :
                    $offShift[$index]++;
                    break;
            }
            if($sm->shift!='O') {
                $staffDay[$index]++;
            }
        }
        $staffPerShift = array($dawShift,$morShift,$aftShift,$eveShift,$extShift,$offShift);

        
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
        $runParam = RunParameters::getMonth($generatedMonth);
        
        // Show the views
        return view('schedules.index')
            ->with('generatedMonth',$generatedMonth)
            ->with('scheduleMonth', $scheduleMonth)
            ->with('staffPerShift', $staffPerShift)
            ->with('staffDay',$staffDay)
            ->with('previousDate', $previousDate)
            ->with('nextDate', $nextDate)
            ->with('calendarDays', $calendarDays)
            ->with('runParam',$runParam);
    }

    /**
     * Generate the schedule for a month using given parameters
     */

    static $debugMessages = "";
    public function generate(Request $request)
    {
        // $timeStart = microtime(true);
        // debug Messages for debugging
        // global $debugMessages;

        // Validation of needed data
        $this->validate($request, [
            'month'=>'required',
            'year'=>'required',
            'populationSize'=>'required|numeric|max:100',
            'maxIterations'=>'required|numeric|max:100',
            'alpha'=>'required|numeric',
            'gamma'=>'required|numeric'
        ]);

        $month = $request->month;
        $year = $request->year;
        $populationSize = $request->populationSize;
        $maxIterations = $request->maxIterations;
        $alpha = $request->alpha;
        $gamma = $request->gamma;

        // Check the database if data for that month and year exists.
        $scheduleMonth = Schedule::getMonth($year,$month);
        // if it exists, return redirect('schedules/'.$year."/".$month)
        if(count($scheduleMonth)>0) {
            return redirect('schedules/'.$year."/".$month);
        } else {
            // else, generate it by dispatching, return home with a loading ring shown.
            $generatedMonths = Schedule::getGeneratedMonths();
            DoGenerateSchedule::dispatch($month, $year, $populationSize, $maxIterations, $alpha, $gamma);
            return view('welcome')
                ->with('month', $month)
                ->with('year', $year)
                ->with('populationSize', $populationSize)
                ->with('maxIterations', $maxIterations)
                ->with('alpha', $alpha)
                ->with('gamma', $gamma)
                ->with('generatedMonths', $generatedMonths);
        }
        
        // do nothing else after the if-else block

        /** OLD CODE - migrated to job handle */
        // Firefly-specific variables
        // $populationSize = 1000;   // Run time at 1000 is 1.76 seconds only
        // $alpha = 1;
        // $gamma = 1;
        // $initialSolution = array();
        // // Initial Solution is an array whose size is equivalent to the populationSize
        // $penalties = array();
        // // Penalties is an array containing the count of Off-balance shifts, consecutive
        // // shifts beyond 2, and unfilled shifts. It will be used in the objective function

        // // Identify all calendar days
        // $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);

        // // Identify Sundays and Weekdays
        // $sundays = array();
        // $weekdays = array();
        // for($i=1; $i<=$calendarDays; $i++) {
        //     if(substr(date_format(date_create(($year)."-".($month)."-".$i),"D"),0,2)=="Su")
        //         array_push($sundays,$i);
        //     else
        //         array_push($weekdays,$i);
        // }

        // for($p=0; $p<$populationSize; $p++) {
        //     $solution = array();
        //     $debugMessages .= "Solution " . $p+1 . ". ";
        //     // Create the days counter for balancing
        //     $sundaysCount = array_fill(0,sizeof($sundays),0);
        //     $dayOffCount = array_fill(0,sizeof($weekdays),0);
        //     $eveningCount = array_fill(0,sizeof($weekdays),0);
        //     $afternoonCount = array_fill(0,sizeof($weekdays),0);
        //     $morningCount = array_fill(0,sizeof($weekdays),0);
        //     $dawnCount = array_fill(0,sizeof($weekdays),0);
        //     // Counter for all the shifts per technician for balancing
        //     $mpt = $ept = $apt = 0;

        //     // Counter for penalties observed
        //     $offbalanceshifts = 0;
        //     $consecutiveshifts = 0;
        //     $unfilledshifts = 0;

        //     $lowerBound = intval(($calendarDays-9)/3);
        //     $upperBound = intval(ceil(($calendarDays-9)/3));
        //     $debugMessages .= "Lower bound at " . $lowerBound . ". ";
        //     $debugMessages .= "Upper bound at " . $upperBound . ". ";

        //     // For the meantime, select all technicians, get all id's
        //     $technicians = Technician::getTechnicians();
            
        //     foreach($technicians as $t) {
        //         // reset the counters of mpt ept apt to 0
        //         $mpt = $ept = $apt = 0;

        //         // the $days variable will now be the array fed to the $initialSolution array
        //         $days = array_fill(0, $calendarDays+1,"");
        //         // $days[0] will be the technician id
        //         $days[0] = $t->id;
        //         // $debugMessages .= "Doing technician " . $t->id . " (". (($t->isSenior)?'Senior':'Ordinary') ."). ";

        //         // By default, assign all Sundays to "O"
        //         foreach($sundays as $sun) {
        //             $days[$sun] = "O";
        //         }

        //         // Assign Sundays per Constraint 8.
        //         // All technicians must have one extended shift on a Sunday. This only needs to run once.
        //         $sundayShiftDay = $this->dayBalancer($sundays, $sundaysCount, $days, "H", false);
        //         $days[$sundayShiftDay] = "H";
        //         // $debugMessages .= "Assigning ". $sundayShiftDay . " as a Sunday shift. ";
        //         $sundaysCount[array_search($sundayShiftDay,$sundays)]++;

        //         $availableWeekdays = $weekdays;
        //         $prevShift = "";

        //         // Assign off-days
        //         $daysOffToAssign = 9 - sizeof($sundays);
        //         for($i=0; $i<$daysOffToAssign; $i++) {
        //             $dayOffDay = $this->dayBalancer($weekdays, $dayOffCount, $days, "O", true);
        //             $days[$dayOffDay] = "O";
        //             // $debugMessages .= "Assigning ". $dayOffDay . " as a day off. ";
        //             $dayOffCount[array_search($dayOffDay,$weekdays)]++;
        //             array_splice($availableWeekdays,array_search($dayOffDay,$availableWeekdays),1);
        //         }


        //         // Assign based on available weekdays
        //         foreach($weekdays as $wki => $wkv ) {
        //             if($days[$wkv]=="O") {
        //                 continue;
        //             } else {
        //                 $candidateShift = $this->getCandidate($mpt, $ept, $apt);
        //                 $finalShift = $this->getFinal($dawnCount, $morningCount, $eveningCount, $afternoonCount, $wki, $candidateShift, $prevShift, $t);
        //                 $days[$wkv] = ($t->isSenior&&$finalShift=="M")?"D":$finalShift;
        //                 if($finalShift == "M") {
        //                     if($t->isSenior) {
        //                         $dawnCount[$wki]++;
        //                     }
        //                     $morningCount[$wki]++;
        //                     $mpt++;
        //                     $prevShift = "M";
        //                 } else if($finalShift == "E") {
        //                     $eveningCount[$wki]++;
        //                     $ept++;
        //                     $prevShift = "E";
        //                 } else {
        //                     $afternoonCount[$wki]++;
        //                     $apt++;
        //                     $prevShift = "A";
        //                 }
        //             }
        //         // Finished assigning weekdays
        //         }

        //         // off-balance count: check mpt, ept, and apt
        //         if(!($mpt >= $lowerBound && $mpt <= $upperBound)) {
        //             $offbalanceshifts += abs($mpt - ($upperBound-($upperBound-$lowerBound)));
        //         }
        //         if(!($ept >= $lowerBound && $ept <= $upperBound)) {
        //             $offbalanceshifts += abs($ept - ($upperBound-($upperBound-$lowerBound)));
        //         }
        //         if(!($apt >= $lowerBound && $apt <= $upperBound)) {
        //             $offbalanceshifts += abs($apt - ($upperBound-($upperBound-$lowerBound)));
        //         }
        //         // $debugMessages .= "Off Balance Shifts now at: " . $offbalanceshifts . ". ";
        //         // Rather than saving the entries to the database right away,
        //         // we will create a "solution" array to contain the entries.
        //         // Data: date, tech_id, shift
        //         // Perform consecutive count here
        //         $consec_prevShift = "";
        //         $consec_localCount = 1;
        //         for($j=1; $j<=$calendarDays; $j++) {
        //             array_push($solution, [$year."-".$month."-".$j, $days[0], $days[$j]]);
        //             if($days[$j] == $consec_prevShift) {
        //                 $consec_localCount++;
        //             } else {
        //                 $consec_localCount = 1;
        //             }
        //             if($consec_localCount > 2) {
        //                 // $debugMessages .= "Consec local is already : " . $consec_localCount . " for ". $consec_prevShift .". ";
        //                 $consecutiveshifts++;
        //             }
        //             $consec_prevShift = $days[$j];
        //         }

        //         // Save the entries to the database
        //         // for($j=1; $j<=$calendarDays; $j++) {
        //         //     $sched = new Schedule();
        //         //     $sched->schedule = $year."-".$month."-".$j;
        //         //     $sched->technician_id = $days[0];
        //         //     $sched->shift = $days[$j];
        //         //     $sched->save();
        //         // }
        //     // End for loop per technician
        //     }

        //     // Apply a fix to correct the morning count
        //     foreach($weekdays as $wki => $wkv ) {
        //         $morningCount[$wki] -= $dawnCount[$wki];
        //     }

        //     // unfilled shift count: check dawn,morning,afternoon,eveningCount arrays
        //     //dd(print_r(array_count_values($morningCount)));
        //     if(in_array(0,$dawnCount)) {
        //         $unfilledshifts += array_count_values($dawnCount)[0];
        //         // $debugMessages .= "Dawn has this many 0s: " . array_count_values($dawnCount)[0] . ". ";
        //     }
        //     if(in_array(0,$morningCount)) {
        //         $unfilledshifts += array_count_values($morningCount)[0];
        //         // $debugMessages .= "Morning has this many 0s: " . array_count_values($morningCount)[0] . ". ";
        //     }
        //     if(in_array(0,$afternoonCount)) {
        //         $unfilledshifts += array_count_values($afternoonCount)[0];
        //     }
        //     if(in_array(0,$eveningCount)) {
        //         $unfilledshifts += array_count_values($eveningCount)[0];
        //     }
        //     // $debugMessages .= "Off-Balance count: ". $offbalanceshifts . ". Consecutive count: ". $consecutiveshifts .". Unfilled count: ". $unfilledshifts . ". ";
        //     $debugMessages .= "Objective function value: " . $offbalanceshifts + $consecutiveshifts + $unfilledshifts . ". ";
        //     array_push($initialSolution, $solution);
        //     array_push($penalties, [$offbalanceshifts, $consecutiveshifts, $unfilledshifts]);
        // }
        
        // // Perform Firefly algorithm here
        // /**
        //  *   1. For each solution in $initialSolutions, read $penalties, assign to new array the value of initialsolution at index
        //  *      with objective function value - OK
        //  *   2. Sort the new array in ascending order. Higher value currently means more penalties incurred. - OK
        //  *   For i to maximum iterations, do
        //  *   3. Compute distance
        //  *      - How can we quantify the distance between solutions?
        //  *          - What if x is the solution and f(x) is the objective function?
        //  *      - If the solutions are already sorted with the most optimal at the start, how can latter solutions be better?
        //  *   4. Perform movement
        //  *      - What makes a movement?
        //  *          - Does it involve changing shifts?
        //  *          - Will solutions copy other solutions?
        //  *      - Will every solution make a movement?
        //  *   5. Get objective function values
        //  *   6. Sort the solutions again.
        //  *   7. Repeat
        //  */

        // $objectiveFunctionValues = array();
        // for($p=0; $p<$populationSize; $p++) {
        //     array_push($objectiveFunctionValues, $this->getObjectiveFunctionValue($penalties[$p]));
        // }

        // array_multisort($objectiveFunctionValues, $initialSolution);
        // $debugMessages .= "Solutions sorted. Best solution has objValue of " . $objectiveFunctionValues[0] . ". ";

        // // Write only the best solution to the database
        // // For the meantime, put first solution in.
        // for($k=0; $k<sizeof($initialSolution[0]); $k++) {
        //     $sched = new Schedule();
        //     $sched->schedule = $initialSolution[0][$k][0];
        //     $sched->technician_id = $initialSolution[0][$k][1];
        //     $sched->shift = $initialSolution[0][$k][2];
        //     $sched->save();
        // }

        // $timeEnd = microtime(true);
        // $debugMessages .= "Time elapsed: " . number_format(($timeEnd - $timeStart), 2) . ". ";
        return redirect('schedules/'.$year."/".$month)
            // ->with('debugMessages', $debugMessages)
            ->with('success','Schedule Generated.');
    }

    /** SUPPORT FUNCTIONS MIGRATED TO DoGenerateSchedule Job Class */
    // private function getCandidate($mpt, $ept, $apt) {
    //     $a = min($mpt, $ept, $apt);
    //     $candidate = array();
    //     while(sizeof($candidate)<3) {
    //         if($mpt == $a) {
    //             array_push($candidate,"M");
    //         }
    //         if($ept == $a) {
    //             array_push($candidate,"E");
    //         }
    //         if($apt == $a) {
    //             array_push($candidate,"A");
    //         }
    //         $a++;
    //     }
    //     return $candidate;
    // }

    // private function getFinal($dawnCount, $morningCount, $eveningCount, $afternoonCount, $i, $candidate, $prevShift, $t) {
    //     $a = min($morningCount[$i], $eveningCount[$i], $afternoonCount[$i]);
    //     if($a == $morningCount[$i] && $candidate[0] == "M"){
    //         if($prevShift == "E") {
    //             return $candidate[1];
    //         }
    //         return "M";
    //     }
    //     if($a == $eveningCount[$i] && $candidate[0] == "E"){
    //         return "E";
    //     }
    //     if($a == $afternoonCount[$i] && $candidate[0] == "A"){
    //         return "A";
    //     }
    //     // if($a == $dawnCount[$i] && $t->isSenior)
    //     //     return "D";
    //     if($a == $morningCount[$i])
    //         return "M";
    //     if($a == $eveningCount[$i])
    //         return "E";
    //     if($a == $afternoonCount[$i])
    //         return "A";
    // }
    
    // /**
    //  * This function takes in arrays of days (weekdays or sundays) and counts. It randomly looks for days that 
    //  * do not yet have a lot of assigned technicians based on $counts and returns the selected day as an integer.
    //  * 
    //  * It refers to $days to see if the day is already occupied.
    //  * $sunweek and $counts have the same size
    //  */
    // private function dayBalancer($sunweek, $counts, $days, $targetShift, $avoidThreeConsecutive) {
    //     global $debugMessages;
        
    //     $selectedDay = 0;
    //     $smallestValue = 100;
    //     do {
    //         $smallestValueArray = array();
    //         for($i=0; $i<sizeof($sunweek); $i++) {
    //             if($counts[$i] < $smallestValue) {
    //                 $smallestValue = $counts[$i];
    //                 $smallestValueArray = array();
    //                 array_push($smallestValueArray,$sunweek[$i]);
    //             } else if($counts[$i] == $smallestValue) {
    //                 array_push($smallestValueArray,$sunweek[$i]);
    //             }
    //         }
    //         // $debugMessages .= "Smallest value array contains " . print_r($smallestValueArray, true) . ". ";
    //         // $debugMessages .= "Counts array contains " . print_r($counts, true) . ". ";
    //         $randomIndex = rand(0,sizeof($smallestValueArray)-1);
    //         if($targetShift == "H" || $targetShift == "O") {
    //             // On Sundays, no need to check if days[index] is occupied.
    //             $selectedDay = $smallestValueArray[$randomIndex];
    //             return $selectedDay;
    //         } else {
    //             // On Weekdays, need to check if days[index] is occupied.
    //             do {
    //                 $randomIndex = rand(0,sizeof($smallestValueArray)-1);
    //                 // dd(print_r($smallestValueArray, true));
    //                 // dd(empty($days[$smallestValueArray[$randomIndex]]));
    //                 if(empty($days[$smallestValueArray[$randomIndex]])) {
    //                     $selectedDay = $smallestValueArray[$randomIndex];
    //                     return $selectedDay;
    //                 }
    //                 unset($smallestValueArray[$randomIndex]);
    //             } while(sizeof($smallestValueArray) > 0);
    //         }
    //         $smallestValue++;
    //     } while($selectedDay == 0);
    // }

    // /**
    //  * This function computes the "brightness" of a firefly
    //  */
    // private function getObjectiveFunctionValue($penalties) {
    //     /**
    //      * Inputs : penaties array to read:
    //      *          - off-balance (too much or too little from threhsold)
    //      *          - consecutive (more than 3 at a time)
    //      *          - unfilled (unfilled shifts when there should be at least 1)
    //      * Process: so far all multipliers at 1 for now.
    //      * Outputs: integer of score
    //      */
    //     $offbalanceshifts = $penalties[0];
    //     $consecutiveshifts = $penalties[1];
    //     $unfilledshifts = $penalties[2];
    //     return $offbalanceshifts + $consecutiveshifts + $unfilledshifts;
    // }

    /**
     * Display the specified schedule of the user for a given month and year.
     */
    public function individual(string $id)
    {
        //
    }

    public function checkGenerated(Request $request) {
        $month = $request->month;
        $year = $request->year;
        $countGeneratedSchedule = 0;

        if(!empty($month) && !empty($year)) {
            $countGeneratedSchedule = count(Schedule::getMonth($year, $month));
        }
        return $countGeneratedSchedule;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $year, string $month)
    {
        //
        $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        Schedule::whereBetween('schedule',[$year."-".$month."-01",$year."-".$month."-".$calendarDays])->delete();
        RunParameters::where('runForDate',$year."-".$month."-01")->delete();
        return redirect('schedules/'.$year."/".$month)->with('success','Schedule Deleted.');
    }

    /**
     * Download the month as CSV
     */
    public function downloadCSV(string $year, string $month) {
        // Data-specific functions
        $scheduleMonth = Schedule::getMonth($year,$month);
        $calendarDays = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $generatedMonth = date_create(($year)."-".($month)."-01");
        $runParam = RunParameters::getMonth($generatedMonth);

        // Create an array for the number of staff per shift
        $dawShift = array_fill(0, $calendarDays+1, 0);
        $morShift = array_fill(0, $calendarDays+1, 0);
        $aftShift = array_fill(0, $calendarDays+1, 0);
        $eveShift = array_fill(0, $calendarDays+1, 0);
        $offShift = array_fill(0, $calendarDays+1, 0);
        $extShift = array_fill(0, $calendarDays+1, 0);
        $staffDay = array_fill(0, $calendarDays+1, 0);

        // CSV-specific preparations
        $csvFileName = 'ScheduleCSV '.$year."-".$month.".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];
        $handle = fopen('php://output','w');

        // Adding the data to the open stream
        // LINE 1
        $lineToInsert = array();
        array_push($lineToInsert, "ID");
        array_push($lineToInsert, "Designation");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $i);
        }
        array_push($lineToInsert, "Tally");
        fputcsv($handle, $lineToInsert);
        
        // LINE 2
        $lineToInsert = array();
        array_push($lineToInsert, ""); array_push($lineToInsert, "");
        array_push($lineToInsert, substr(date_format($generatedMonth,"D"),0,2));
        for($i=0;$i<$calendarDays-1;$i++) {
            array_push($lineToInsert, substr(date_format((date_add($generatedMonth, date_interval_create_from_date_string("1 days"))),"D"),0,2));
        }
        array_push($lineToInsert, "D/M", "A", "E", "H", "O");
        fputcsv($handle, $lineToInsert);

        // LINE 3 TO NTECHNICIANS
        $pertechDM = $pertechA = $pertechE = $pertechH = $pertechO = 0;
        $lineToInsert = array();
        // Read the scheduleMonth array to tally
        foreach($scheduleMonth as $sm) {
            // Reading of dates for output
            $smdate = date_create($sm->schedule); 
            $smdate = date_format($smdate,"d")."";
            if($smdate == "01") {
                $lineToInsert = array();
                array_push($lineToInsert, $sm->technician_id, ($sm->isSenior=='1')?'Senior':'Ordinary');
            }
            array_push($lineToInsert, $sm->shift);
            // Assembly of tally
            $index = intval(date_format(date_create($sm->schedule),"j"));
            switch($sm->shift) {
                case 'D':
                    $dawShift[$index]++;
                    $pertechDM++;
                    break;
                case 'M':
                    $morShift[$index]++;
                    $pertechDM++;
                    break;
                case 'A':
                    $aftShift[$index]++;
                    $pertechA++;
                    break;
                case 'E':
                    $eveShift[$index]++;
                    $pertechE++;
                    break;
                case 'H':
                    $extShift[$index]++;
                    $pertechH++;
                    break;
                default :
                    $offShift[$index]++;
                    $pertechO++;
                    break;
            }
            if($sm->shift!='O') {
                $staffDay[$index]++;
            }
            if($smdate == $calendarDays) {
                array_push($lineToInsert, $pertechDM, $pertechA, $pertechE, $pertechH, $pertechO);
                $pertechDM = $pertechA = $pertechE = $pertechH = $pertechO = 0;
                fputcsv($handle, $lineToInsert);
            }
        }

        // LINES FOR TOTAL STAFF PER SHIFT
        $lineToInsert = array();
        array_push($lineToInsert, "Total Staff Per Shift");
        fputcsv($handle, $lineToInsert);

        $lineToInsert = array("","Dawn");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $dawShift[$i]);
        }
        fputcsv($handle, $lineToInsert);
        $lineToInsert = array("","Morning");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $morShift[$i]);
        }
        fputcsv($handle, $lineToInsert);
        $lineToInsert = array("","Afternoon");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $aftShift[$i]);
        }
        fputcsv($handle, $lineToInsert);
        $lineToInsert = array("","Evening");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $eveShift[$i]);
        }
        fputcsv($handle, $lineToInsert);
        $lineToInsert = array("","Extended");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $extShift[$i]);
        }
        fputcsv($handle, $lineToInsert);
        $lineToInsert = array("","Off");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $offShift[$i]);
        }
        fputcsv($handle, $lineToInsert);

        // LINE FOR TOTAL STAFF PER DAY
        $lineToInsert = array("Total Staff Per Day");
        fputcsv($handle, $lineToInsert);
        $lineToInsert = array("", "");
        for($i=1; $i<=$calendarDays; $i++) {
            array_push($lineToInsert, $staffDay[$i]);
        }
        fputcsv($handle, $lineToInsert);

        // RUN PARAMETERS
        if(!empty($runParam)) {
            $lineToInsert = array("Generated on",$runParam->created_at);
            fputcsv($handle, $lineToInsert);
            $lineToInsert = array("Generated for",number_format($runParam->runTime, 2) . " sec");
            fputcsv($handle, $lineToInsert);
            $lineToInsert = array("Movements",$runParam->movements);
            fputcsv($handle, $lineToInsert);
            $lineToInsert = array("Population",$runParam->populationSize);
            fputcsv($handle, $lineToInsert);
            $lineToInsert = array("Max Iterations",$runParam->maxIterations);
            fputcsv($handle, $lineToInsert);
            $lineToInsert = array("Alpha",$runParam->alpha);
            fputcsv($handle, $lineToInsert);
            $lineToInsert = array("Gamma",$runParam->gamma);
            fputcsv($handle, $lineToInsert);
        }
        
        // Try to output the file for testing
        fclose($handle);
        return Response::make('', 200, $headers);
    }
}
