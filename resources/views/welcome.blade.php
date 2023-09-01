@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Home</h1>
        <div class="row">
            <div class="col-sm-4">
                <form action="{{route('schedules.generate')}}" method="post" class="bg-white p-4 rounded-3">
                    <h3 class="">Make a Schedule</h3>
                    <div class="input-group my-3">
                        @csrf
                        <select name="month" id="selMonth" class="form-select form-select-lg rounded-start" required>
                            <option disabled selected>Month</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <input class="form-control form-select-lg" type="number" name="year" id="txtYear" placeholder="Year" value="{{date('Y')}}" required>
                    </div>
                    <div class="row my-2">
                        <div class="col-6">
                            <label for="txtSenior">Senior</label>
                            <input type="number" name="seniorCount" id="txtSenior" class="form-control" value="{{$seniorCount}}" min="0" max="{{$seniorCount}}" required>
                        </div>
                        <div class="col-6">
                            <label for="txtOrdinary">Ordinary</label>
                            <input type="number" name="ordinaryCount" id="txtOrdinary" class="form-control" value="{{$ordinaryCount}}" min="0" max="{{$ordinaryCount}}" required>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <label for="txtWorkingDays">Working days</label>
                            <input type="number" name="workingdays" id="txtWorkingDays" class="form-control">
                        </div>
                        <div class="col-6 d-flex justify-content-end align-items-end">
                            <input type="submit" class="btn btn-lg btn-primary" value="Generate">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-sm-8 bg-white p-4 rounded-3">
                <h3 class="mb-3">Generated Schedules</h3>
                @if(count($generatedMonths)<1)
                <p><em>There are currently no generated schedules.</em></p><p>Select a month and year on the left then click the Generate button to create schedules.</p>
                @else
                <div class="row row-cols-4 g-4">
                    @foreach ($generatedMonths as $gM)
                        <?php $gmDate = date_create($gM->schedule."-01") ?>
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">{{date_format($gmDate,"F Y")}}</h5>
                                    <a href="schedules/{{date_format($gmDate,"Y/m")}}" class="btn btn-sm btn-outline-secondary stretched-link">Open Schedule</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
                
            </div>
        </div>
    </div>
@endsection