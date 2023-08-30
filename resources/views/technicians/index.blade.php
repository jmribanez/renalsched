@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-middle mb-2">
        <h1 class="mb-0">Technicians</h1>
        <div>
            <a href="{{route('technicians.create')}}" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> New Technician</a>
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
                    <td>{{($t->isSenior)?"Senior":"Ordinary"}}</td>
                    <td>{{$t->name_family}}</td>
                    <td class="d-flex justify-content-between align-items-middle"><div>{{$t->name_first}}</div><div><a href="{{route('technicians.edit',$t->id)}}" class="me-3"><i class="fa-solid fa-pencil"></i></a> <a href="#"><i class="fa-regular fa-calendar-days"></i></a></div></td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@if(isset($modalMode))
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalTitle">Add Technician</h1>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <form id="technicianForm" class="modal-body" method="POST" action="{{($modalMode=='Edit')?route('technicians.update',$technician->id):route('technicians.store')}}">
                @csrf
                @if($modalMode=='New')
                    @method('put')
                @else
                    @method('patch')
                @endif
                @if(isset($technician))
                <input type="hidden" name="id" value="{{$technician->id}}">
                @endif
                <div class="mb-3">
                    <label for="selIsSenior" class="form-label">Seniority</label>
                    <select name="isSenior" id="selIsSenior" class="form-select" value="{{$technician->isSenior??''}}">
                        <option disabled {{!isset($technician->isSenior)?'selected':''}}>Select seniority</option>
                        <option value="1" {{isset($technician->isSenior)?($technician->isSenior=="1")?'selected':'':''}}>Senior</option>
                        <option value="0" {{isset($technician->isSenior)?($technician->isSenior=="0")?'selected':'':''}}>Ordinary</option>
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
            </form>
            <div class="modal-footer">
                @if(isset($technician))
                <form id="technicianDelete" class="me-auto" action="{{route('technicians.destroy',$technician->id)}}" method="post">
                @csrf
                @method('delete')
                <input type="hidden" name="id" value="{{$technician->id}}">
                <input type="submit" value="Delete" class="btn btn-outline-danger">
                </form>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="technicianForm" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.onload = function() {
        var myModal = new Modal(document.getElementById('editModal'));
        myModal.show();
        document.getElementById('modalTitle').innerHTML = "{{$modalMode.' Technician'}}";
    }
</script>
@endif
@endsection