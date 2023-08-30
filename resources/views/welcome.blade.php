@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Home</h1>
        <div class="row">
            <div class="col-sm-4">
                <form action="" method="post" class="bg-white p-4 rounded-3">
                    <h3 class="">Make a Schedule</h3>
                    <div class="input-group my-3">
                        <select name="month" id="selMonth" class="form-select form-select-lg">
                            <option disabled selected>Month</option>
                            <option value="1">January</option>
                        </select>
                        <input class="form-control form-select-lg" type="number" name="year" id="txtYear" placeholder="Year">
                    </div>
                    <div class="row my-2">
                        <div class="col-6">
                            <label for="txtSenior">Senior</label>
                            <input type="number" name="seniorCount" id="txtSenior" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="txtOrdinary">Ordinary</label>
                            <input type="number" name="ordinaryCount" id="txtOrdinary" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <label for="txtWorkingDays">Working days</label>
                            <input type="number" name="workingdays" id="txtWorkingDays" class="form-control">
                        </div>
                        <div class="col-6 d-flex justify-content-end align-items-end">
                            <button type="submit" class="btn btn-lg btn-primary">Generate</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-sm-8 bg-white p-4 rounded-3">
                <h3>Generated Schedules</h3>
            </div>
        </div>
    </div>
@endsection