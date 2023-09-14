@extends('layouts.app')

@section('content')
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>{{date_format($generatedMonth,"F Y")}} Schedule</h1>
            <div class="d-flex">
                <form method="POST" action="{{route('schedules.destroy',[date_format($generatedMonth,'Y'),date_format($generatedMonth,'m')])}}">
                @csrf
                @method('delete')
                <input type="submit" value="Delete" class="btn btn-outline-danger me-5">
                </form>
                <a href="/schedules/{{date_format($previousDate,"Y/m")}}" class="btn btn-secondary me-2"><i class="fa-solid fa-backward"></i></a>
                <a href="/schedules/{{date_format($nextDate,"Y/m")}}" class="btn btn-secondary"><i class="fa-solid fa-forward"></i></a>
            </div>
        </div>
        <table class="table table-striped-columns {{count($scheduleMonth)>0?'table-hover':'';}}">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Designation</th>
                    @for($i=1;$i<=$calendarDays;$i++)
                    <th>{{$i}}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td>{{substr(date_format($generatedMonth,"D"),0,2)}}</td>
                    @for($i=0;$i<$calendarDays-1;$i++)
                    <td>{{substr(date_format((date_add($generatedMonth, date_interval_create_from_date_string("1 days"))),"D"),0,2)}}</td>
                    @endfor
                </tr>
                @if(count($scheduleMonth)<1)
                    <tr><td colspan="{{$calendarDays+2}}"><p class="pt-3">There are no schedules generated for this month.</p>
                    <p>Create a schedule by selecting options in the homepage of this site and clicking Generate.</p></td></tr>
                @else
                    @foreach($scheduleMonth as $sm)
                        <?php $smdate = date_create($sm->schedule); $smdate = date_format($smdate,"d"); ?>
                        @if($smdate=="01")
                            <tr>
                                <td>{{$sm->technician_id}}</td>
                                <td></td>
                        @endif
                        <td>{{$sm->shift}}</td>
                        @if($smdate==$calendarDays)
                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
@endsection