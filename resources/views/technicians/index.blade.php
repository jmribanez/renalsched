@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-middle mb-2">
        <h1 class="mb-0">Technicians</h1>
        <button class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> New Technician</button>
    </div>
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Seniority</th>
                    <th>Family Name</th>
                    <th>First Name</th>
                </tr>
            </thead>
            <tbody>
                @if(count($technicians)==0)
                <tr>
                    <td colspan="4"><em>No technicians in the system.</em></td>
                </tr>
                @else
                @foreach ($technicians as $t)
                <tr>
                    <td>{{$t->id}}</td>
                    <td>{{($t->isSenior)?"Senior":"Regular"}}</td>
                    <td>{{$t->name_family}}</td>
                    <td>{{$t->name_first}}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection