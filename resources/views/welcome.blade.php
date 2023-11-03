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
                        <select name="month" id="selMonth" class="form-select form-select-lg rounded-start" required {{(isset($month))?'disabled':''}}>
                            <?php $activeMonth = $month??date('m');?>
                            <option disabled>Month</option>
                            <option value="1" {{($activeMonth==1)?'selected':'';}}>January</option>
                            <option value="2" {{($activeMonth==2)?'selected':'';}}>February</option>
                            <option value="3" {{($activeMonth==3)?'selected':'';}}>March</option>
                            <option value="4" {{($activeMonth==4)?'selected':'';}}>April</option>
                            <option value="5" {{($activeMonth==5)?'selected':'';}}>May</option>
                            <option value="6" {{($activeMonth==6)?'selected':'';}}>June</option>
                            <option value="7" {{($activeMonth==7)?'selected':'';}}>July</option>
                            <option value="8" {{($activeMonth==8)?'selected':'';}}>August</option>
                            <option value="9" {{($activeMonth==9)?'selected':'';}}>September</option>
                            <option value="10" {{($activeMonth==10)?'selected':'';}}>October</option>
                            <option value="11" {{($activeMonth==11)?'selected':'';}}>November</option>
                            <option value="12" {{($activeMonth==12)?'selected':'';}}>December</option>
                        </select>
                        <input class="form-control form-select-lg" type="number" name="year" id="txtYear" placeholder="Year" value="{{$year??date('Y')}}" required {{(isset($year))?'disabled':''}}>
                    </div>
                    <div class="row my-2">
                        <div class="col-6">
                            <label for="txtPopulationSize">Population Size</label>
                            <input type="number" name="populationSize" id="txtPopulationSize" class="form-control" value="{{$populationSize??100}}" min="1" max="32767" required {{(isset($populationSize))?'disabled':''}}>
                        </div>
                        <div class="col-6">
                            <label for="txtMaxIterations">Max Iterations</label>
                            <input type="number" name="maxIterations" id="txtMaxIterations" class="form-control" value="{{$maxIterations??100}}" min="1" max="32767" required {{(isset($maxIterations))?'disabled':''}}>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <label for="txtAlpha">Alpha</label>
                            <input type="number" name="alpha" id="txtAlpha" value="{{$alpha??1}}" min="0.1" max="32767" step="0.01" class="form-control" {{(isset($alpha))?'disabled':''}}>
                        </div>
                        <div class="col-6">
                            <label for="txtGamma">Gamma</label>
                            <input type="number" name="gamma" id="txtGamma" value="{{$gamma??1}}" min="0.1" max="32767" step="0.01" class="form-control" {{(isset($gamma))?'disabled':''}}>
                        </div>
                    </div>
                    @if(!isset($month))
                    <div class="mt-3">
                        <div class="d-flex justify-content-end align-items-center">
                            <input type="submit" class="btn btn-lg btn-primary" value="Generate">
                        </div>
                    </div>
                    @else
                    <div class="mt-3 d-flex justify-content-between align-items-end">
                        <p>Please wait...</p>
                        <div class="d-flex justify-content-end align-items-end">
                            <button class="btn btn-lg btn-primary" type="button" disabled>
                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <span role="status">Generating...</span>
                            </button>
                        </div>
                    </div>
                    @endif
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
    @if(isset($month))
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function checkTaskStatus() {
            $.ajax({
                url: '/schedules/check/{{$year}}/{{$month}}',
                type: 'GET',
                success: function(response) {
                    if(response > 0) {
                        location.href = '/schedules/{{$year}}/{{$month}}';
                    } else {
                        setTimeout(checkTaskStatus, 1000);
                    }
                }
            });
        }
        $(document).ready(function() {
            checkTaskStatus();
        });
    </script>
    @endif
@endsection