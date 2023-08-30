<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <script src="https://kit.fontawesome.com/26ed367001.js" crossorigin="anonymous"></script>
    
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <header class="d-flex flex-wrap justify-content-center p-3 mb-4 border-bottom bg-white shadow-sm">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
          <i class="fa-regular fa-calendar-days fa-xl me-2"></i>
          <span class="fs-4">Renal Schedule</span>
        </a>
  
        <ul class="nav">
          <li class="nav-item"><a href="/" class="nav-link" aria-current="page">Home</a></li>
          <li class="nav-item"><a href="{{url('technicians')}}" class="nav-link">Technicians</a></li>
        </ul>
    </header>
    <div class="container">
      @include('inc.messages')
    </div>
    @yield('content')
</body>
</html>