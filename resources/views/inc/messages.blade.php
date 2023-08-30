@if (count($errors)>0)
    <div class="toast-container position-static">
@foreach ($errors->all() as $error)
    <div class="alert alert-danger">{{$error}}</div>
@endforeach
    
@endif
@if (session('error'))
    <div class="alert alert-danger">{{session('error')}}</div>
@endif
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
@endif