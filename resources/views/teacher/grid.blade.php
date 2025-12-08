@extends('layouts.teacher')

@section('content')
  <style>.dot{display:inline-block;width:14px;height:14px;border-radius:50%}.dot-green{background:#16a34a}.dot-amber{background:#f59e0b}.dot-red{background:#ef4444}</style>

  <div class="d-flex align-items-baseline gap-3 mb-2">
    <div class="h4 mb-0">Group attendance</div>
    <span class="badge text-bg-secondary">{{ $group->name }}</span>
    <small class="text-muted">{{ $from->format('M j, Y') }} — {{ $to->format('M j, Y') }}</small>
  </div>

  <form method="GET" action="{{ route('teacher.grid', $group) }}" class="row g-2 align-items-end mb-3">
    <div class="col-sm-4 col-md-3">
      <label class="form-label">Group</label>
      <select name="group" class="form-select" onchange="location=this.options[this.selectedIndex].dataset.url">
        @foreach($groups as $g)
          <option data-url="{{ route('teacher.grid',$g->id) }}" value="{{ $g->id }}" {{ $g->id===$group->id?'selected':'' }}>
            {{ $g->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-sm-4 col-md-3">
      <label class="form-label">Specialization</label>
      <select name="spec" class="form-select">
        <option value="All" {{ $spec==='All'?'selected':'' }}>All</option>
        @foreach($specs as $s)
          <option value="{{ $s }}" {{ $s===$spec?'selected':'' }}>{{ $s }}</option>
        @endforeach
      </select>
    </div>

    <input type="hidden" name="from" value="{{ $from->toDateString() }}">

    <div class="col-md-auto">
      <button class="btn btn-outline-secondary">Go</button>
    </div>

    <div class="col-md-auto">
      <div class="btn-group" role="group" aria-label="Navigate">
        <a class="btn btn-outline-secondary"
           href="{{ route('teacher.grid', ['group'=>$group->id,'spec'=>$spec,'from'=>$from->copy()->subWeeks(2)->toDateString()]) }}">Prev</a>
        <a class="btn btn-outline-secondary"
           href="{{ route('teacher.grid', ['group'=>$group->id,'spec'=>$spec,'from'=>now()->startOfWeek()->subWeeks(3)->toDateString()]) }}">Today</a>
        <a class="btn btn-outline-secondary"
           href="{{ route('teacher.grid', ['group'=>$group->id,'spec'=>$spec,'from'=>$from->copy()->addWeeks(2)->toDateString()]) }}">Next</a>
      </div>
    </div>

    <div class="col-md-auto ms-auto">
      <a class="btn btn-primary"
         href="{{ route('teacher.grid', ['group'=>$group->id,'spec'=>$spec,'from'=>$from->toDateString(),'export'=>'csv']) }}">Download CSV</a>
    </div>
  </form>

  @php
    $sessByDay = $sessions->groupBy(fn($s)=>$s->starts_at->toDateString());
    $days = collect(); $d=$from->copy(); while($d<=$to){ $days->push($d->copy()); $d->addDay(); }
    $attByKey = $attendance->keyBy(fn($a)=>$a->student_id.'-'.$a->class_session_id);
  @endphp

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light sticky-top">
          <tr>
            <th class="text-start" style="min-width:240px">Student</th>
            @foreach($days as $day)
              @php $sess = ($sessByDay[$day->toDateString()] ?? collect()); @endphp
              <th class="text-center" colspan="{{ max(1,$sess->count()) }}">
                <div class="fw-semibold">{{ $day->format('n/j') }}</div>
                @if($sess->isEmpty())
                  <div class="text-muted small">—</div>
                @else
                  <div class="small text-muted">
                    @foreach($sess as $s)
                      <span class="badge text-bg-light border">{{ $s->starts_at->format('H:i') }}–{{ $s->ends_at->format('H:i') }}</span>
                    @endforeach
                  </div>
                @endif
              </th>
            @endforeach
            <th class="text-center">P</th><th class="text-center">L</th><th class="text-center">A</th>
          </tr>
        </thead>
        <tbody>
          @foreach($students as $stu)
            @php $t = $rowTotals[$stu->id] ?? ['present'=>0,'late'=>0,'absent'=>0]; @endphp
            <tr>
              <td class="text-start fw-medium">{{ $stu->full_name }}</td>
              @foreach($days as $day)
                @php $sess = ($sessByDay[$day->toDateString()] ?? collect()); @endphp
                @if($sess->isEmpty())
                  <td class="text-center text-muted">—</td>
                @else
                  @foreach($sess as $s)
                    @php $a = $attByKey->get($stu->id.'-'.$s->id); @endphp
                    <td class="text-center">
                      @if($a)
                        @php
                          $color = match($a->outcome()) {'present'=>'dot-green','late'=>'dot-amber','absent'=>'dot-red',default=>'dot-amber'};
                        @endphp
                        <span class="dot {{ $color }}" title="{{ ucfirst($a->outcome()) }}"></span>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                  @endforeach
                @endif
              @endforeach
              <td class="text-center"><span class="badge text-bg-success">{{ $t['present'] }}</span></td>
              <td class="text-center"><span class="badge text-bg-warning">{{ $t['late'] }}</span></td>
              <td class="text-center"><span class="badge text-bg-danger">{{ $t['absent'] }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
