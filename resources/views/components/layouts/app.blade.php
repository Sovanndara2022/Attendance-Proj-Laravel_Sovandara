<?php
/* --------------------------------------------------------------------------
| resources/views/components/layouts/app.blade.php
| Minimal, stable shell. No global resets that break pages.
|-------------------------------------------------------------------------- */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LOGBOOK</title>
  <style>
    :root{--bg:#f7f7fb;--ink:#111827;--mut:#6b7280;--line:#e5e7eb;--brand:#111827}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--ink);font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto}
    .container{max-width:1100px;margin:0 auto;padding:18px}
    .nav{display:flex;gap:16px;align-items:center;justify-content:flex-end}
    .nav a{color:var(--ink);text-decoration:none;font-size:14px;padding:8px 10px;border-radius:10px}
    .nav a:hover{background:#f3f4f6}
    .brand{margin-right:auto;font-weight:800;color:var(--brand);letter-spacing:.3px}
    .dropdown{position:relative}
    .avatar{width:34px;height:34px;border-radius:999px;background:var(--brand);color:#fff;display:grid;place-items:center;font-weight:700;border:none;cursor:pointer}
    .menu{position:absolute;right:0;top:44px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 10px 24px rgba(0,0,0,.12);min-width:170px;display:none}
    .menu a,.menu form button{display:block;width:100%;text-align:left;padding:10px 12px;background:none;border:none;color:var(--ink);text-decoration:none;font-size:14px}
    .menu a:hover,.menu form button:hover{background:#f3f4f6}
  </style>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">LOGBOOK</div>
      <a href="{{ route('teacher.timetable') }}">Timetable</a>
      <a href="{{ route('teacher.grid', \App\Models\Group::first()?->id ?? 1) }}">Group attendance</a>
      <a href="{{ route('teacher.reports.absences') }}">Reports</a>
      @auth
        <div class="dropdown">
          <button id="avatarBtn" class="avatar" title="{{ auth()->user()->email }}">
            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
          </button>
          <div id="menu" class="menu">
            <div style="padding:8px 12px;font-size:12px;color:var(--mut)">{{ auth()->user()->email }}</div>
            <a href="{{ route('profile') }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form>
          </div>
        </div>
        <script>
          (function(){
            const btn=document.getElementById('avatarBtn'), menu=document.getElementById('menu');
            btn?.addEventListener('click',()=> menu.style.display = menu.style.display==='block'?'none':'block');
            document.addEventListener('click',(e)=>{ if(!btn.contains(e.target)&&!menu.contains(e.target)) menu.style.display='none';});
          })();
        </script>
      @else
        <a href="{{ route('login') }}" style="background:#111827;color:#fff">Login</a>
      @endauth
    </div>
    <div style="margin-top:18px">{{ $slot }}</div>
  </div>
</body>
</html>
