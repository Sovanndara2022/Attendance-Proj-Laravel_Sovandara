<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>@yield('title','LOGBOOK')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
  {{-- IMPORTANT: this file must live at public/css/teacher.css --}}
  <link href="{{ asset('css/teacher.css') }}" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-light bg-white border-bottom sticky-top">
  <div class="container-fluid">
    <a href="{{ route('teacher.timetable') }}" class="brand-glass">
      <span class="brand-logo"></span><span class="brand-text">LOGBOOK</span>
    </a>

    <div class="nav nav-glass">
      <a class="nav-link {{ request()->routeIs('teacher.timetable') ? 'active' : '' }}"
         href="{{ route('teacher.timetable') }}">TIMETABLE</a>

      <a class="nav-link {{ request()->routeIs('teacher.group.*') ? 'active' : '' }}"
         href="{{ route('teacher.group.grid', ['group' => \App\Models\Group::query()->min('id')]) }}">
        GROUP ATTENDANCE
      </a>

      <a class="nav-link {{ request()->routeIs('teacher.reports*') ? 'active' : '' }}"
         href="{{ route('teacher.reports') }}">REPORTS</a>
    </div>

    <div>
      <a href="{{ route('profile.index') }}" class="text-decoration-none">
  @if(auth()->user()->avatar_url)
    <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="avatar-img">
  @else
    <span class="avatar-circle-lg">
      {{ strtoupper(auth()->user()->name[0] ?? 'U') }}
    </span>
  @endif
</a>
    </div>
  </div>
</nav>

<main class="container my-4">
  @yield('content')
</main>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
