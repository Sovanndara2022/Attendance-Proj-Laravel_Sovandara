@extends('layouts.teacher')

@section('title', $session->topic.' attendance')

@section('content')
@if(session('status'))
  <div class="alert alert-success">{{ session('status') }}</div>
@endif

<h5 class="mb-1">
  {{ $session->group->name ?? $session->group->code ?? 'Group' }}
  @if(!empty($session->group->specialization)) • {{ $session->group->specialization }} @endif
  • {{ $session->topic }}
</h5>
<div class="mb-3 text-muted">
  {{ \Carbon\Carbon::parse($session->starts_at)->format('H:i') }} –
  {{ \Carbon\Carbon::parse($session->ends_at)->format('H:i') }}
  <small class="ms-2">{{ \Carbon\Carbon::parse($session->starts_at)->isoFormat('dddd, MMM D') }}</small>
</div>

<form method="POST" action="{{ route('teacher.sessions.update', $session) }}">
  @csrf
  @method('PUT')

  <table class="table align-middle">
    <thead>
      <tr>
        <th style="width:60px">№</th>
        <th>Student</th>
        <th>Present</th>
        <th>Online</th>
        <th>(max 5)</th>
        <th>Comment</th>
      </tr>
    </thead>
    <tbody>
      @foreach($attendance as $i => $row)
        <tr>
          <td>{{ $i+1 }}</td>
          <td><a href="#" class="text-decoration-none">{{ $row->student->full_name }}</a></td>

          {{-- Present --}}
          <td>
            <div class="status">
              @foreach(['g'=>'green','y'=>'amber','r'=>'red'] as $val=>$clr)
                <label class="position-relative">
                  <input class="status-radio" type="radio"
                         name="items[{{ $row->id }}][present_status]" value="{{ $val }}"
                         @checked(($row->present_status ?? 0) === (['r'=>0,'y'=>1,'g'=>2][$val]))>
                  <span class="dot dot-{{ $clr }}"></span>
                </label>
              @endforeach
            </div>
          </td>

          {{-- Online --}}
          <td>
            <div class="status">
              @foreach(['g'=>'green','y'=>'amber','r'=>'red'] as $val=>$clr)
                <label class="position-relative">
                  <input class="status-radio" type="radio"
                         name="items[{{ $row->id }}][online_status]" value="{{ $val }}"
                         @checked(($row->online_status ?? 0) === (['r'=>0,'y'=>1,'g'=>2][$val]))>
                  <span class="dot dot-{{ $clr }}"></span>
                </label>
              @endforeach
            </div>
          </td>

          {{-- Stars (1 choice only) --}}
          <td>
            <div class="stars">
              @for($s=5;$s>=1;$s--)
                <input
                  class="star-radio"
                  id="stars-{{ $row->id }}-{{ $s }}"
                  type="radio"
                  name="items[{{ $row->id }}][stars]"
                  value="{{ $s }}"
                  @checked($row->stars==$s)
                >
                <label class="diamond" for="stars-{{ $row->id }}-{{ $s }}"></label>
              @endfor
            </div>
          </td>

          {{-- Comment --}}
          <td style="min-width:280px">
            <input name="items[{{ $row->id }}][comment]" class="form-control" value="{{ $row->comment }}" placeholder="Comment">
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="d-flex gap-2">
    <button class="btn btn-primary">Save Group attendance</button>
    <a class="btn btn-outline-secondary" href="{{ route('teacher.group.grid', ['group' => $session->group_id]) }}">Group attendance</a>
  </div>
</form>
@endsection
