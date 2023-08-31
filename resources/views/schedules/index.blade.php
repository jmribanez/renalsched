@extends('layouts.app')

@section('content')
    
    <div class="container">
        <h1>{{date_format($generatedMonth,"F Y")}} Schedule</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Designation</th>
                    @for($i=1;$i<=31;$i++)
                    <th>{{$i}}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($scheduleMonth as $sm)
                <?php $smdate = date_create($sm->schedule); $smdate = date_format($smdate,"d") ?>
                @if($smdate=="01")
                <tr>
                    <td>{{$sm->technician_id}}</td>
                    <td></td>
                @endif
                <td>{{$sm->shift}}</td>
                @if($smdate=="31")
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    
@endsection