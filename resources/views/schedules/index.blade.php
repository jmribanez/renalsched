@extends('layouts.app')

@section('content')
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>{{date_format($generatedMonth,"F Y")}} Schedule</h1>
            <div class="d-flex">
                @if(count($scheduleMonth)>0)
                <button class="btn btn-outline-secondary me-2">Download CSV</button>
                <form method="POST" action="{{route('schedules.destroy',[date_format($generatedMonth,'Y'),date_format($generatedMonth,'m')])}}">
                @csrf
                @method('delete')
                <input type="submit" value="Delete" class="btn btn-outline-danger me-4">
                </form>
                @endif
                <a href="/schedules/{{date_format($previousDate,"Y/m")}}" class="btn btn-secondary me-2"><i class="fa-solid fa-backward"></i></a>
                <a href="/schedules/{{date_format($nextDate,"Y/m")}}" class="btn btn-secondary"><i class="fa-solid fa-forward"></i></a>
            </div>
        </div>
        <table class="table table-sm table-striped-columns {{count($scheduleMonth)>0?'table-hover':'';}}">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Designation</th>
                    @for($i=1;$i<=$calendarDays;$i++)
                    <th>{{$i}}</th>
                    @endfor
                    <th colspan="5">Tally</th>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>{{substr(date_format($generatedMonth,"D"),0,2)}}</td>
                    @for($i=0;$i<$calendarDays-1;$i++)
                    <td>{{substr(date_format((date_add($generatedMonth, date_interval_create_from_date_string("1 days"))),"D"),0,2)}}</td>
                    @endfor
                    <td>D/M</td>
                    <td>A</td>
                    <td>E</td>
                    <td>H</td>
                    <td>O</td>
                </tr>
            </thead>
            <tbody>
                <?php $pertechDM = $pertechA = $pertechE = $pertechH = $pertechO = 0; ?>
                @if(count($scheduleMonth)<1)
                    <tr><td colspan="{{$calendarDays+2}}"><p class="pt-3">There are no schedules generated for this month.</p>
                    <p>Create a schedule by selecting options in the homepage of this site and clicking Generate.</p></td></tr>
                @else
                    @foreach($scheduleMonth as $sm)
                        <?php $smdate = date_create($sm->schedule); $smdate = date_format($smdate,"d").""; ?>
                        @if($smdate=="01")
                            <tr>
                                <td>{{$sm->technician_id}}</td>
                                <td>{{($sm->isSenior=='1')?'Senior':'Ordinary';}}</td>
                        @endif
                        <td>{{$sm->shift}}
                        <?php 
                            switch($sm->shift) {
                                case 'D':
                                case 'M':
                                    $pertechDM++;
                                    break;
                                case 'A':
                                    $pertechA++;
                                    break;
                                case 'E':
                                    $pertechE++;
                                    break;
                                case 'H':
                                    $pertechH++;
                                    break;
                                case 'O':
                                    $pertechO++;
                                    break;
                            }    
                        ?></td>
                        @if($smdate==$calendarDays)
                        <td>{{$pertechDM}}</td>    
                        <td>{{$pertechA}}</td>
                        <td>{{$pertechE}}</td>
                        <td>{{$pertechH}}</td>
                        <td>{{$pertechO}}</td>
                        </tr>
                        <?php $pertechDM = $pertechA = $pertechE = $pertechH = $pertechO = 0; ?>
                        @endif
                    @endforeach
                    <tr>
                        <td colspan="{{(7+$calendarDays)}}"><strong>Total Staff Per Shift</strong></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Dawn</td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffPerShift[0][$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Morning</td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffPerShift[1][$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Afternoon</td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffPerShift[2][$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Evening</td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffPerShift[3][$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Extended</td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffPerShift[4][$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Off</td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffPerShift[5][$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td colspan="{{(7+$calendarDays)}}"><strong>Total Staff Per Day</strong></td>
                    </tr>
                    <tr>
                        <td></td><td></td>
                        @for($i=1;$i<=$calendarDays;$i++)
                        <td>{{$staffDay[$i]}}</td>
                        @endfor
                        <td colspan="5"></td>
                    </tr>
                @endif
            </tbody>
        </table>
        <p>&#10033; If the table is incomplete after generating, please refresh the page.</p>
        <?php if(!empty($runParam)): ?>
        <h3>Run Parameters</h3>
        <div class="row mb-5">
            <div class="col-sm-2">
                <div class="card pt-3">
                    <div class="card-body">
                        <i class="fa-solid fa-stopwatch fa-2xl"></i>
                        <h5 class="card-title fw-bold mt-3">{{number_format($runParam->runTime, 2)}} sec</h5>
                        <p class="card-text">Generation Time</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card pt-3">
                    <div class="card-body">
                        <i class="fa-solid fa-arrows-up-down-left-right fa-2xl"></i>
                        <h5 class="card-title fw-bold mt-3">{{$runParam->movements}}</h5>
                        <p class="card-text">Movements</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card pt-3">
                    <div class="card-body">
                        <i class="fa-solid fa-people-group fa-2xl"></i>
                        <h5 class="card-title fw-bold mt-3">{{$runParam->populationSize}}</h5>
                        <p class="card-text">Population</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card pt-3">
                    <div class="card-body">
                        <i class="fa-solid fa-rotate-left fa-2xl"></i>
                        <h5 class="card-title fw-bold mt-3">{{$runParam->maxIterations}}</h5>
                        <p class="card-text">Max Iterations</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card pt-3">
                    <div class="card-body">
                        <i class="fa-solid fa-a fa-2xl"></i>
                        <h5 class="card-title fw-bold mt-3">{{$runParam->alpha}}</h5>
                        <p class="card-text">Alpha</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card pt-3">
                    <div class="card-body">
                        <i class="fa-solid fa-g fa-2xl"></i>
                        <h5 class="card-title fw-bold mt-3">{{$runParam->gamma}}</h5>
                        <p class="card-text">Gamma</p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
        <?php if(session('debugMessages')):?>
        <h3>Debug Messages</h3>
        <p>{{session('debugMessages')}}</p>
        <?php endif;?>
    </div>
@endsection