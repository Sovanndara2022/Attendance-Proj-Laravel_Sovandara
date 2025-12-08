@extends('layouts.teacher')

@section('title','Timetable')

@section('content')
<div class="d-flex align-items-end mb-3">
  <div>
    <h4 class="mb-0">Timetable</h4>
    <small class="text-muted">{{ $from->toDateString() }} — {{ $to->toDateString() }}</small>
  </div>
</div>

<form method="get" class="row g-2 align-items-end mb-3">
  <div class="col-md-3">
    <label class="form-label">Group</label>
    <select name="group_id" class="form-select">
      @foreach($groups as $g)
        <option value="{{ $g->id }}" @selected($g->id==$groupId)>{{ $g->name ?? $g->code }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">Specialization</label>
    <select name="spec" class="form-select">
      <option value="All" @selected($spec==='All')>All</option>
      @foreach($specializations as $s)
        <option value="{{ $s }}" @selected($spec===$s)>{{ $s }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">Pick any date</label>
    <input type="date" name="date" value="{{ $anchor->toDateString() }}" class="form-control">
  </div>

  <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-outline-primary mt-auto">Go</button>
    <a class="btn btn-outline-secondary mt-auto"
       href="{{ route('teacher.timetable', request()->except('date') + ['date'=>$anchor->copy()->subWeek()->toDateString()]) }}">Prev</a>
    <a class="btn btn-outline-secondary mt-auto"
       href="{{ route('teacher.timetable', request()->except('date') + ['date'=>now()->toDateString()]) }}">This week</a>
    <a class="btn btn-outline-secondary mt-auto"
       href="{{ route('teacher.timetable', request()->except('date') + ['date'=>$anchor->copy()->addWeek()->toDateString()]) }}">Next</a>
  </div>
</form>

<div class="row g-3">
  @for($i=0; $i<7; $i++)
    @php
      $d = $from->copy()->addDays($i);
      $key = $d->toDateString();
      $daySessions = $byDay[$key] ?? collect();
    @endphp

    <div class="col-lg-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="fw-semibold">{{ $d->isoFormat('dddd') }}</div>
          <div class="text-muted">{{ $d->isoFormat('MMM D') }}</div>

          <div class="mt-2 d-flex flex-column gap-2">
            @forelse($daySessions as $s)
              @php
                $start = \Carbon\Carbon::parse($s->starts_at);
                $end   = \Carbon\Carbon::parse($s->ends_at);
              @endphp

              <a href="{{ route('teacher.sessions.edit', $s) }}" class="slot-pill text-decoration-none d-block">
                <div class="small text-muted">
                  {{ $s->group->name ?? $s->group->code ?? '' }}
                  @if(!empty($s->group->specialization)) • {{ $s->group->specialization }} @endif
                </div>
                <div class="fw-semibold">
                  {{ $start->format('H:i') }} – {{ $end->format('H:i') }}
                </div>
                <div class="small text-muted">{{ $start->isoFormat('ddd, MMM D') }}</div>
                <div class="small">{{ $s->topic }}</div>
              </a>
            @empty
              <div class="text-center text-muted">No sessions</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  @endfor
</div>
@endsection
