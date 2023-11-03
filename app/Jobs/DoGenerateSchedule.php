<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schedule;
use App\Models\Technician;

class DoGenerateSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;
    protected $year;
    protected $populationSize;
    protected $maxIterations;
    protected $alpha;
    protected $gamma;
    /**
     * Create a new job instance.
     */
    public function __construct($month, $year, $populationSize, $maxIterations, $alpha, $gamma)
    {
        // Get the data
        $this->month = $month;
        $this->year = $year;
        $this->populationSize = $populationSize;
        $this->maxIterations = $maxIterations;
        $this->alpha = $alpha;
        $this->gamma = $gamma;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Schedule-specific variables
        $month = $this->month;
        $year = $this->year;

        // Firefly-specific variables
        $populationSize = $this->populationSize;
        $maxIterations = $this->maxIterations;
        $alpha = $this->alpha;
        $gamma = $this->gamma;
        $initialSolution = array();
        // Initial Solution is an array whose size is equivalent to the populationSize
        $penalties = array();
        // Penalties is an array containing the count of Off-balance shifts, consecutive
        // shifts beyond 2, and unfilled shifts. It will be used in the objective function

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

        for($p=0; $p<$populationSize; $p++) {
            $solution = array();
            // $debugMessages .= "Solution " . $p+1 . ". ";
            // Create the days counter for balancing
            $sundaysCount = array_fill(0,sizeof($sundays),0);
            $dayOffCount = array_fill(0,sizeof($weekdays),0);
            $eveningCount = array_fill(0,sizeof($weekdays),0);
            $afternoonCount = array_fill(0,sizeof($weekdays),0);
            $morningCount = array_fill(0,sizeof($weekdays),0);
            $dawnCount = array_fill(0,sizeof($weekdays),0);
            // Counter for all the shifts per technician for balancing
            $mpt = $ept = $apt = 0;

            // Counter for penalties observed
            $offbalanceshifts = 0;
            $consecutiveshifts = 0;
            $unfilledshifts = 0;

            $lowerBound = intval(($calendarDays-9)/3);
            $upperBound = intval(ceil(($calendarDays-9)/3));
            // $debugMessages .= "Lower bound at " . $lowerBound . ". ";
            // $debugMessages .= "Upper bound at " . $upperBound . ". ";

            // For the meantime, select all technicians, get all id's
            $technicians = Technician::getTechnicians();
            
            foreach($technicians as $t) {
                // reset the counters of mpt ept apt to 0
                $mpt = $ept = $apt = 0;

                // the $days variable will now be the array fed to the $initialSolution array
                $days = array_fill(0, $calendarDays+1,"");
                // $days[0] will be the technician id
                $days[0] = $t->id;
                // $debugMessages .= "Doing technician " . $t->id . " (". (($t->isSenior)?'Senior':'Ordinary') ."). ";

                // By default, assign all Sundays to "O"
                foreach($sundays as $sun) {
                    $days[$sun] = "O";
                }

                // Assign Sundays per Constraint 8.
                // All technicians must have one extended shift on a Sunday. This only needs to run once.
                $sundayShiftDay = $this->dayBalancer($sundays, $sundaysCount, $days, "H", false);
                $days[$sundayShiftDay] = "H";
                // $debugMessages .= "Assigning ". $sundayShiftDay . " as a Sunday shift. ";
                $sundaysCount[array_search($sundayShiftDay,$sundays)]++;

                $availableWeekdays = $weekdays;
                $prevShift = "";

                // Assign off-days
                $daysOffToAssign = 9 - sizeof($sundays);
                for($i=0; $i<$daysOffToAssign; $i++) {
                    $dayOffDay = $this->dayBalancer($weekdays, $dayOffCount, $days, "O", true);
                    $days[$dayOffDay] = "O";
                    // $debugMessages .= "Assigning ". $dayOffDay . " as a day off. ";
                    $dayOffCount[array_search($dayOffDay,$weekdays)]++;
                    array_splice($availableWeekdays,array_search($dayOffDay,$availableWeekdays),1);
                }


                // Assign based on available weekdays
                foreach($weekdays as $wki => $wkv ) {
                    if($days[$wkv]=="O") {
                        continue;
                    } else {
                        $candidateShift = $this->getCandidate($mpt, $ept, $apt);
                        $finalShift = $this->getFinal($dawnCount, $morningCount, $eveningCount, $afternoonCount, $wki, $candidateShift, $prevShift, $t);
                        $days[$wkv] = ($t->isSenior&&$finalShift=="M")?"D":$finalShift;
                        if($finalShift == "M") {
                            if($t->isSenior) {
                                $dawnCount[$wki]++;
                            }
                            $morningCount[$wki]++;
                            $mpt++;
                            $prevShift = "M";
                        } else if($finalShift == "E") {
                            $eveningCount[$wki]++;
                            $ept++;
                            $prevShift = "E";
                        } else {
                            $afternoonCount[$wki]++;
                            $apt++;
                            $prevShift = "A";
                        }
                    }
                // Finished assigning weekdays
                }

                // off-balance count: check mpt, ept, and apt
                if(!($mpt >= $lowerBound && $mpt <= $upperBound)) {
                    $offbalanceshifts += abs($mpt - ($upperBound-($upperBound-$lowerBound)));
                }
                if(!($ept >= $lowerBound && $ept <= $upperBound)) {
                    $offbalanceshifts += abs($ept - ($upperBound-($upperBound-$lowerBound)));
                }
                if(!($apt >= $lowerBound && $apt <= $upperBound)) {
                    $offbalanceshifts += abs($apt - ($upperBound-($upperBound-$lowerBound)));
                }
                // $debugMessages .= "Off Balance Shifts now at: " . $offbalanceshifts . ". ";
                // Rather than saving the entries to the database right away,
                // we will create a "solution" array to contain the entries.
                // Data: date, tech_id, shift
                // Perform consecutive count here
                $consec_prevShift = "";
                $consec_localCount = 1;
                for($j=1; $j<=$calendarDays; $j++) {
                    array_push($solution, [$year."-".$month."-".$j, $days[0], $days[$j]]);
                    if($days[$j] == $consec_prevShift) {
                        $consec_localCount++;
                    } else {
                        $consec_localCount = 1;
                    }
                    if($consec_localCount > 2) {
                        // $debugMessages .= "Consec local is already : " . $consec_localCount . " for ". $consec_prevShift .". ";
                        $consecutiveshifts++;
                    }
                    $consec_prevShift = $days[$j];
                }

                // Save the entries to the database
                // for($j=1; $j<=$calendarDays; $j++) {
                //     $sched = new Schedule();
                //     $sched->schedule = $year."-".$month."-".$j;
                //     $sched->technician_id = $days[0];
                //     $sched->shift = $days[$j];
                //     $sched->save();
                // }
            // End for loop per technician
            }

            // Apply a fix to correct the morning count
            foreach($weekdays as $wki => $wkv ) {
                $morningCount[$wki] -= $dawnCount[$wki];
            }

            // unfilled shift count: check dawn,morning,afternoon,eveningCount arrays
            //dd(print_r(array_count_values($morningCount)));
            if(in_array(0,$dawnCount)) {
                $unfilledshifts += array_count_values($dawnCount)[0];
                // $debugMessages .= "Dawn has this many 0s: " . array_count_values($dawnCount)[0] . ". ";
            }
            if(in_array(0,$morningCount)) {
                $unfilledshifts += array_count_values($morningCount)[0];
                // $debugMessages .= "Morning has this many 0s: " . array_count_values($morningCount)[0] . ". ";
            }
            if(in_array(0,$afternoonCount)) {
                $unfilledshifts += array_count_values($afternoonCount)[0];
            }
            if(in_array(0,$eveningCount)) {
                $unfilledshifts += array_count_values($eveningCount)[0];
            }
            // $debugMessages .= "Off-Balance count: ". $offbalanceshifts . ". Consecutive count: ". $consecutiveshifts .". Unfilled count: ". $unfilledshifts . ". ";
            // $debugMessages .= "Objective function value: " . $offbalanceshifts + $consecutiveshifts + $unfilledshifts . ". ";
            array_push($initialSolution, $solution);
            array_push($penalties, [$offbalanceshifts, $consecutiveshifts, $unfilledshifts]);
        }
        
        // Perform Firefly algorithm here
        /**
         *   1. For each solution in $initialSolutions, read $penalties, assign to new array the value of initialsolution at index
         *      with objective function value - OK
         *   2. Sort the new array in ascending order. Higher value currently means more penalties incurred. - OK
         *   For i to maximum iterations, do
         *   3. Compute distance
         *      - How can we quantify the distance between solutions?
         *          - What if x is the solution and f(x) is the objective function?
         *      - If the solutions are already sorted with the most optimal at the start, how can latter solutions be better?
         *   4. Perform movement
         *      - What makes a movement?
         *          - Does it involve changing shifts?
         *          - Will solutions copy other solutions?
         *      - Will every solution make a movement?
         *   5. Get objective function values
         *   6. Sort the solutions again.
         *   7. Repeat
         */

        $objectiveFunctionValues = array();
        for($p=0; $p<$populationSize; $p++) {
            array_push($objectiveFunctionValues, $this->getObjectiveFunctionValue($penalties[$p]));
        }

        array_multisort($objectiveFunctionValues, $initialSolution);
        // $debugMessages .= "Solutions sorted. Best solution has objValue of " . $objectiveFunctionValues[0] . ". ";

        // Write only the best solution to the database
        // For the meantime, put first solution in.
        for($k=0; $k<sizeof($initialSolution[0]); $k++) {
            $sched = new Schedule();
            $sched->schedule = $initialSolution[0][$k][0];
            $sched->technician_id = $initialSolution[0][$k][1];
            $sched->shift = $initialSolution[0][$k][2];
            $sched->save();
        }
    }

    /**
     * Support functions of "handle"
     */
    private function getCandidate($mpt, $ept, $apt) {
        $a = min($mpt, $ept, $apt);
        $candidate = array();
        while(sizeof($candidate)<3) {
            if($mpt == $a) {
                array_push($candidate,"M");
            }
            if($ept == $a) {
                array_push($candidate,"E");
            }
            if($apt == $a) {
                array_push($candidate,"A");
            }
            $a++;
        }
        return $candidate;
    }

    private function getFinal($dawnCount, $morningCount, $eveningCount, $afternoonCount, $i, $candidate, $prevShift, $t) {
        $a = min($morningCount[$i], $eveningCount[$i], $afternoonCount[$i]);
        if($a == $morningCount[$i] && $candidate[0] == "M"){
            if($prevShift == "E") {
                return $candidate[1];
            }
            return "M";
        }
        if($a == $eveningCount[$i] && $candidate[0] == "E"){
            return "E";
        }
        if($a == $afternoonCount[$i] && $candidate[0] == "A"){
            return "A";
        }
        // if($a == $dawnCount[$i] && $t->isSenior)
        //     return "D";
        if($a == $morningCount[$i])
            return "M";
        if($a == $eveningCount[$i])
            return "E";
        if($a == $afternoonCount[$i])
            return "A";
    }
    
    /**
     * This function takes in arrays of days (weekdays or sundays) and counts. It randomly looks for days that 
     * do not yet have a lot of assigned technicians based on $counts and returns the selected day as an integer.
     * 
     * It refers to $days to see if the day is already occupied.
     * $sunweek and $counts have the same size
     */
    private function dayBalancer($sunweek, $counts, $days, $targetShift, $avoidThreeConsecutive) {
        // global $debugMessages;
        
        $selectedDay = 0;
        $smallestValue = 100;
        do {
            $smallestValueArray = array();
            for($i=0; $i<sizeof($sunweek); $i++) {
                if($counts[$i] < $smallestValue) {
                    $smallestValue = $counts[$i];
                    $smallestValueArray = array();
                    array_push($smallestValueArray,$sunweek[$i]);
                } else if($counts[$i] == $smallestValue) {
                    array_push($smallestValueArray,$sunweek[$i]);
                }
            }
            // $debugMessages .= "Smallest value array contains " . print_r($smallestValueArray, true) . ". ";
            // $debugMessages .= "Counts array contains " . print_r($counts, true) . ". ";
            $randomIndex = rand(0,sizeof($smallestValueArray)-1);
            if($targetShift == "H" || $targetShift == "O") {
                // On Sundays, no need to check if days[index] is occupied.
                $selectedDay = $smallestValueArray[$randomIndex];
                return $selectedDay;
            } else {
                // On Weekdays, need to check if days[index] is occupied.
                do {
                    $randomIndex = rand(0,sizeof($smallestValueArray)-1);
                    // dd(print_r($smallestValueArray, true));
                    // dd(empty($days[$smallestValueArray[$randomIndex]]));
                    if(empty($days[$smallestValueArray[$randomIndex]])) {
                        $selectedDay = $smallestValueArray[$randomIndex];
                        return $selectedDay;
                    }
                    unset($smallestValueArray[$randomIndex]);
                } while(sizeof($smallestValueArray) > 0);
            }
            $smallestValue++;
        } while($selectedDay == 0);
    }

    /**
     * This function computes the "brightness" of a firefly
     */
    private function getObjectiveFunctionValue($penalties) {
        /**
         * Inputs : penaties array to read:
         *          - off-balance (too much or too little from threhsold)
         *          - consecutive (more than 3 at a time)
         *          - unfilled (unfilled shifts when there should be at least 1)
         * Process: so far all multipliers at 1 for now.
         * Outputs: integer of score
         */
        $offbalanceshifts = $penalties[0];
        $consecutiveshifts = $penalties[1];
        $unfilledshifts = $penalties[2];
        return $offbalanceshifts + $consecutiveshifts + $unfilledshifts;
    }
}