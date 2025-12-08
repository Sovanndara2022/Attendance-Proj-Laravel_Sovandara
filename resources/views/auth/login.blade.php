<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign in — LOGBOOK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background:#f6f7fb }
    .login-card{ max-width:520px; margin:6rem auto; border:1px solid #e9ecef; border-radius:16px; box-shadow:0 10px 30px rgba(2,6,23,.08) }
    .brand{ display:flex; align-items:center; gap:.6rem; }
    .brand-logo{ width:36px; height:36px; border-radius:9px; background:#3b82f6; box-shadow:inset 0 1px 0 rgba(255,255,255,.6); }
  </style>
</head>
<body>
  <div class="container">
    <div class="card login-card">
      <div class="card-body p-4 p-md-5">
        <div class="brand mb-3">
          <div class="brand-logo"></div>
          <h4 class="mb-0">LOGBOOK</h4>
        </div>
        <h5 class="mb-3">Sign in</h5>
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Use teacher credentials</small>
            <button class="btn btn-primary">Sign in</button>
          </div>
        </form>
      </div>
    </div>
    <p class="text-center small text-muted">© LOGBOOK</p>
  </div>
</body>
</html>
