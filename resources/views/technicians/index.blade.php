@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-middle mb-2">
        <h1 class="mb-0">Technicians</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-user-plus"></i> New Technician</button>
        </div>
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
                    <td class="d-flex justify-content-between align-items-middle"><div>{{$t->name_first}}</div><div><a href="{{route('technicians.edit',$t->id)}}"><i class="fa-solid fa-pencil"></i></a></div></td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{route('technicians.store')}}">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Add Technician</h1>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @csrf
                @method('POST')
                <div class="mb-3">
                    <label for="selIsSenior" class="form-label">Seniority</label>
                    <select name="isSenior" id="selIsSenior" class="form-select" value="{{$technician->isSenior??''}}">
                        <option disabled {{!isset($technician->isSenior)?selected:''}}>Select seniority</option>
                        <option value="1" {{isset($technician->isSenior)?($technician->isSenior=="1")?'selected':'':''}}>Senior</option>
                        <option value="0" {{isset($technician->isSenior)?($technician->isSenior=="0")?'selected':'':''}}>Regular</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="txtNameFamily" class="form-label">Family Name</label>
                    <input type="text" name="name_family" id="txtNameFamily" class="form-control" value="{{$technician->name_family??''}}">
                </div>
                <div class="mb-3">
                    <label for="txtNameFirst" class="form-label">First Name</label>
                    <input type="text" name="name_first" id="txtNameFirst" class="form-control" value="{{$technician->name_first??''}}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>
@if(!empty($modalMode))
<script>
    window.onload = function() {
        var myModal = new Modal(document.getElementById('editModal'));
        myModal.show();
        // CONTINUE: MODAL MODE DETERMINES TEXT IN MODAL: CREATE, EDIT
    }
</script>
@endif
@endsection