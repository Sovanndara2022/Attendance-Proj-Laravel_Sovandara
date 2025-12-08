@extends('layouts.teacher')

@section('content')
  <div class="d-flex align-items-baseline gap-3 mb-2">
    <div class="h4 mb-0">Reports</div>
    <small class="text-muted">{{ $from->format('m/d/Y') }} — {{ $to->format('m/d/Y') }}</small>
  </div>

  <form method="GET" action="{{ route('teacher.reports') }}" class="row g-2 align-items-end mb-3">
    <div class="col-sm-4 col-md-3">
      <label class="form-label">Group</label>
      <select name="group" class="form-select">
        @foreach($groups as $g)
          <option value="{{ $g->id }}" {{ $g->id===$groupId?'selected':'' }}>{{ $g->name }}</option>
        @endforeach
      </select>
    </div>
    <input type="hidden" name="from" value="{{ $from->toDateString() }}">
    <div class="col-md-auto"><button class="btn btn-outline-secondary">Go</button></div>
    <div class="col-md-auto">
      <a class="btn btn-outline-secondary" href="{{ route('teacher.reports',['group'=>$groupId,'from'=>$from->subWeek()->toDateString()]) }}">Prev</a>
      <a class="btn btn-outline-secondary" href="{{ route('teacher.reports') }}">This week</a>
      <a class="btn btn-outline-secondary" href="{{ route('teacher.reports',['group'=>$groupId,'from'=>$from->addWeek()->toDateString()]) }}">Next</a>
    </div>
    <div class="col-md-auto ms-auto">
      <a class="btn btn-primary" href="{{ route('teacher.reports',['group'=>$groupId,'from'=>$from->toDateString(),'export'=>'csv']) }}">Download CSV</a>
    </div>
  </form>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>Date</th><th>Time</th><th>Topic</th>
            <th class="text-center">Present</th>
            <th class="text-center">Late</th>
            <th class="text-center">Absent</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sessions as $s)
            @php $c = $tally[$s->id] ?? ['present'=>0,'late'=>0,'absent'=>0]; @endphp
            <tr>
              <td>{{ $s->starts_at->format('D, M j') }}</td>
              <td>{{ $s->starts_at->format('H:i') }}–{{ $s->ends_at->format('H:i') }}</td>
              <td>{{ $s->topic }}</td>
              <td class="text-center"><span class="badge text-bg-success">{{ $c['present'] }}</span></td>
              <td class="text-center"><span class="badge text-bg-warning">{{ $c['late'] }}</span></td>
              <td class="text-center"><span class="badge text-bg-danger">{{ $c['absent'] }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-muted">No sessions in range.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

