<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LOGBOOK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f6f7fb}
    .card{max-width:560px;margin:10vh auto;border-radius:16px;box-shadow:0 10px 30px rgba(2,6,23,.08);border:1px solid #e9ecef}
    .logo{width:44px;height:44px;border-radius:10px;background:#3b82f6;box-shadow:inset 0 1px 0 rgba(255,255,255,.6)}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-body p-5 text-center">
        <div class="d-inline-flex align-items-center gap-2 mb-3">
          <div class="logo"></div><h4 class="mb-0">LOGBOOK</h4>
        </div>
        <p class="text-muted mb-4">Welcome.</p>
        <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
      </div>
    </div>
  </div>
</body>
</html>
