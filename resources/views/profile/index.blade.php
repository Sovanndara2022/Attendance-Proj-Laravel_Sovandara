@extends('layouts.teacher')

@section('content')
  <div class="h4 mb-3">Profile</div>

  {{-- flash + validation --}}
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row g-3">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Profile</h5>

          {{-- Update name + avatar --}}
          <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mb-3">
            @csrf
            @method('PUT')
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Avatar</label>
              <input name="avatar" type="file" class="form-control" accept="image/*">
              <small class="text-muted">PNG/JPG, max 2MB.</small>
              @if(auth()->user()->avatar_url)
                <div class="mt-2">
                  <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
                </div>
              @endif
            </div>
            <button class="btn btn-primary">Save changes</button>
          </form>

          <hr class="my-4">

          {{-- Change password --}}
          <h5 class="mb-3">Change password</h5>
          <form method="POST" action="{{ route('profile.password') }}" class="row g-3" style="max-width:480px">
            @csrf
            <div class="col-12">
              <label class="form-label">Current password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">New password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Confirm new password</label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="col-12">
              <button class="btn btn-primary">Update password</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
@endsection
