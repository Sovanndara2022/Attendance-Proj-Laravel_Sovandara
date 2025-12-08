<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'LOGBOOK' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 (no Tailwind/Vite) -->
    <style>
/* .navbar .avatar {
  width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
}

  .avatar { width: 32px; height: 32px; border-radius: 9999px; object-fit: cover; } */
/* Avatar sizes */
.avatar{ width:32px; height:32px; border-radius:50%; object-fit:cover; }
.avatar-lg{ width:48px; height:48px; }    /* navbar: nice size */
.avatar-xl{ width:64px; height:64px; }    /* even bigger if you want */

/* Large preview on Profile page */
.profile-avatar-preview{
  width:120px; height:120px; border-radius:50%;
  object-fit:cover; border:2px solid #c8d7fb;
}
</style>
    <link rel="stylesheet" href="{{ asset('css/teacher.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{ --green:#16a34a; --amber:#f59e0b; --red:#ef4444; --blue:#3b82f6; --ink:#0b2b6b; }
        /* Brand */
        .brand-glass{display:flex;align-items:center;gap:.6rem;padding:.3rem .6rem;border-radius:14px;background:linear-gradient(180deg,#eef3ff,#e6edff);border:1px solid #c8d7fb;text-decoration:none}
        .brand-logo{width:30px;height:30px;border-radius:8px;background:#3b82f6;box-shadow:inset 0 1px 0 rgba(255,255,255,.6)}
        .brand-text{font-weight:800;letter-spacing:1px;color:#0b2b6b;font-size:1.15rem}
        /* Nav glass buttons */
        .nav-glass .nav-link{text-transform:uppercase;font-weight:700;letter-spacing:.6px;padding:.45rem .8rem;margin-right:.4rem;border-radius:12px;background:rgba(240,244,255,.9);border:1px solid #d7e3ff;color:#243b78}
        .nav-glass .nav-link:hover{background:#e8f0ff;color:#0b2b6b}
        .nav-glass .nav-link.active{background:#dbe8ff;color:#0b2b6b;box-shadow:0 2px 8px rgba(2,6,23,.10) inset}
        /* Avatar */
        .avatar-circle-lg{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:50%;background:linear-gradient(180deg,#e8eefc,#d9e5fb);border:2px solid #c8d7fb;color:#1e3a8a;font-weight:700;font-size:1rem;box-shadow:0 3px 8px rgba(2,6,23,.12), inset 0 1px 0 rgba(255,255,255,.6)}
        .avatar-img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #c8d7fb;box-shadow:0 3px 8px rgba(2,6,23,.12)}
        .avatar-btn:focus-visible{outline:3px solid #93c5fd;border-radius:9999px}
        /* Timetable slot pill */
        .slot-pill{background:linear-gradient(180deg, rgba(59,130,246,.18), rgba(59,130,246,.28));border:1px solid rgba(59,130,246,.35);border-radius:18px;padding:.6rem .8rem;color:#0b2b6b;backdrop-filter:saturate(150%) blur(4px);box-shadow:0 6px 18px rgba(2,6,23,.08), inset 0 1px 0 rgba(255,255,255,.45)}
        /* Attendance dots & diamonds */
        .status{display:inline-flex;gap:.55rem;align-items:center}
        .status-radio{position:absolute;opacity:0;pointer-events:none}
        .dot{display:inline-block;width:18px;height:18px;border-radius:50%;box-shadow:inset 0 0 0 1px rgba(0,0,0,.05)}
        .dot-green{background:var(--green)} .dot-amber{background:var(--amber)} .dot-red{background:var(--red)}
        .status-radio:checked + .dot{box-shadow:0 0 0 2px #fff, 0 0 0 4px #6366f1}
        .status-radio:focus-visible + .dot{outline:2px solid #6366f1;outline-offset:2px}
        .stars{display:inline-flex;gap:.35rem;flex-direction:row-reverse;align-items:center}
        .star-radio{position:absolute;opacity:0;pointer-events:none}
        .diamond{width:18px;height:18px;display:inline-block;transform:rotate(45deg);border-radius:3px;background:#e5e7eb;border:1px solid #d1d5db}
        .star-radio:checked ~ .diamond,.diamond:hover,.diamond:hover ~ .diamond{background:#3b82f6;border-color:#3b82f6}
        .table thead th{position:sticky;top:64px;z-index:1;background-color:#fff}
    </style>
</head>
<body>
<nav class="navbar navbar-light bg-white border-bottom sticky-top">
  <div class="container-fluid">

    <a href="{{ route('teacher.timetable') }}" class="brand-glass">
      <span class="brand-logo"></span><span class="brand-text">LOGBOOK</span>
    </a>

    {{-- inline nav pills --}}
    <div class="nav nav-glass d-flex align-items-center gap-2">
      <a class="nav-link {{ request()->routeIs('teacher.timetable') ? 'active' : '' }}"
         href="{{ route('teacher.timetable') }}">TIMETABLE</a>

      <a class="nav-link {{ request()->routeIs('teacher.group.*') ? 'active' : '' }}"
         href="{{ route('teacher.group.grid', ['group' => \App\Models\Group::query()->min('id')]) }}">
        GROUP ATTENDANCE
      </a>

      <a class="nav-link {{ request()->routeIs('teacher.reports*') ? 'active' : '' }}"
         href="{{ route('teacher.reports') }}">REPORTS</a>
    </div>

    <div class="d-flex align-items-center gap-2">
 @php $u = auth()->user(); @endphp
<a href="{{ route('profile.index') }}" class="text-decoration-none">
  @if($u && $u->avatar_url)
    <img src="{{ asset($u->avatar_url) }}" alt="Avatar" class="avatar avatar-lg">
  @else
    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center avatar avatar-lg">
      {{ strtoupper(substr($u->name ?? 'U',0,1)) }}
    </div>
  @endif
</a>

  {{-- Logout (POST) --}}
  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
  <button class="btn btn-sm btn-outline-secondary"
          onclick="event.preventDefault();document.getElementById('logout-form').submit();">
    Logout
  </button>
</div>
  </div>
</nav>
<main class="container py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
