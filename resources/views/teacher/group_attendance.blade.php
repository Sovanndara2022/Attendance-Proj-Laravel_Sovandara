@extends('layouts.teacher')

@section('title','Group attendance')

@section('content')
<style>
 
  .ga-table thead th { position: sticky; top: 0; background: #fff; z-index: 2; padding-bottom: .5rem; }
  .ga-table tbody tr:first-child td, .ga-table tbody tr:first-child th { padding-top: .9rem; }
  .ga-table th, .ga-table td { vertical-align: middle; }

  .pill { display:inline-block; padding: .125rem .5rem; border-radius: 10rem; font-size:.75rem; line-height:1; }
  .pill-p { background:#e7f4ea; color:#1e7e34; }
  .pill-l { background:#fff7e0; color:#a07905; }
  .pill-a { background:#fde7ea; color:#c82333; }

</style>

<div class="d-flex align-items-end mb-3">
  <div>
    <div class="h5 mb-1">Group attendance</div>
    <small class="text-muted">{{ $from->toDateString() }} — {{ $to->toDateString() }} · {{ $group->name ?? $group->code }}</small>
  </div>
</div>

<form method="get" class="row g-2 align-items-end mb-3">
  <div class="col-md-3">
    <label class="form-label">Group</label>
    <select class="form-select"
            onchange="location.href='{{ route('teacher.group.grid',['group'=>$group->id]) }}?'+new URLSearchParams(Object.fromEntries(new FormData(this.form)))">
      @foreach($groups as $g)
        <option value="{{ $g->id }}" @selected($g->id==$group->id)>
          {{ $g->name ?? $g->code }}
        </option>
      @endforeach
    </select>
    <input type="hidden" name="group" value="{{ $group->id }}">
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
    <input type="date" name="date" value="{{ $from->toDateString() }}" class="form-control">
  </div>

  <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-outline-primary mt-auto">Go</button>
    <a class="btn btn-outline-secondary mt-auto" href="{{ request()->fullUrlWithQuery(['date'=>$from->copy()->subWeek()->toDateString()]) }}">Prev</a>
    <a class="btn btn-outline-secondary mt-auto" href="{{ request()->fullUrlWithQuery(['date'=>now()->toDateString()]) }}">This week</a>
    <a class="btn btn-outline-secondary mt-auto" href="{{ request()->fullUrlWithQuery(['date'=>$from->copy()->addWeek()->toDateString()]) }}">Next</a>
  </div>
</form>

<div class="table-responsive">
  <table class="table ga-table">
   <thead>
  <tr>
    <th>Student</th>

    @foreach($sessions as $s)
      @php
        $start = \Carbon\Carbon::parse($s->starts_at);
        $end   = \Carbon\Carbon::parse($s->ends_at);
      @endphp

      <th class="text-center">
        <div class="fw-semibold">{{ $start->isoFormat('ddd') }}</div>
        <div class="text-muted">{{ $start->isoFormat('Do') }}</div>
        <div class="small">{{ $start->format('H:i') }} – {{ $end->format('H:i') }}</div>
      </th>
    @endforeach

    <th class="text-center">P</th>
    <th class="text-center">L</th>
    <th class="text-center">A</th>
  </tr>
</thead>
    <tbody>
      @foreach($students as $st)
        <tr>
          <th>{{ $st->full_name }}</th>
          @foreach($sessions as $s)
            @php
              $cell = optional($attendanceByStu[$st->id] ?? collect())->get($s->id);
              $ps = (int)($cell->present_status ?? -1);
              $os = (int)($cell->online_status  ?? -1);
              $mark = ''; // P/L/A/-
              if ($ps===0 && $os===0) $mark='A';
              elseif (($ps===1&&$os===0)||($ps===0&&$os===1)||($ps===1&&$os===1)) $mark='L';
              elseif ($ps===2 || $os===2) $mark='P';
            @endphp
            <td class="text-center">
              @if($mark==='P') <span class="pill pill-p">P</span>
              @elseif($mark==='L') <span class="pill pill-l">L</span>
              @elseif($mark==='A') <span class="pill pill-a">A</span>
              @endif
            </td>
          @endforeach
         @php $t = $totals[$st->id] ?? ['p'=>0,'l'=>0,'a'=>0]; @endphp
<td class="text-center"><span class="pill pill-p">{{ $t['p'] ?? 0 }}</span></td>
<td class="text-center"><span class="pill pill-l">{{ $t['l'] ?? 0 }}</span></td>
<td class="text-center"><span class="pill pill-a">{{ $t['a'] ?? 0 }}</span></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
