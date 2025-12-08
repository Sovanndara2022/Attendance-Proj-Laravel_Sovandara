{{-- resources/views/teacher/report.blade.php --}}
@extends('layouts.teacher')

@section('title','Reports')

@section('content')
@php
  /** safe defaults so the view never crashes **/
  $mode = $mode ?? 'sessions';
  $list = $list ?? [];
@endphp

<div class="d-flex align-items-end mb-3">
  <div>
    <h4 class="mb-0">Reports</h4>
    <small class="text-muted">{{ $from->toDateString() }} — {{ $to->toDateString() }}</small>
  </div>
</div>

<form method="get" class="row g-2 align-items-end mb-3">
  <div class="col-md-3">
    <label class="form-label">Group</label>
    <select name="group" class="form-select">
      @foreach($groups as $g)
        <option value="{{ $g->id }}" @selected(($groupId ?? 0)==$g->id)>{{ $g->name ?? $g->code }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-3">
    <label class="form-label">Pick any date</label>
    <input type="date" name="date" value="{{ $from->toDateString() }}" class="form-control">
  </div>

  <div class="col-md-3">
    <label class="form-label">View</label>
    <select name="mode" class="form-select">
      <option value="students"  @selected($mode==='students')>By students</option>
      <option value="sessions"  @selected($mode==='sessions')>By sessions</option>
    </select>
  </div>

  <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-outline-primary mt-auto">Go</button>
    <a class="btn btn-outline-secondary mt-auto" href="{{ request()->fullUrlWithQuery(['date'=>$from->copy()->subWeek()->toDateString()]) }}">Prev</a>
    <a class="btn btn-outline-secondary mt-auto" href="{{ request()->fullUrlWithQuery(['date'=>now()->toDateString()]) }}">This week</a>
    <a class="btn btn-outline-secondary mt-auto" href="{{ request()->fullUrlWithQuery(['date'=>$from->copy()->addWeek()->toDateString()]) }}">Next</a>
  </div>
</form>

<div class="mb-3">
  <a href="{{ route('teacher.reports.csv', request()->all()) }}" class="btn btn-primary">Download CSV</a>
</div>

@if($mode === 'students')
  {{-- =================== BY STUDENTS =================== --}}
  <table class="table align-middle">
    <thead>
      <tr>
        <th>Student</th>
        <th class="text-center">Present</th>
        <th class="text-center">Late</th>
        <th class="text-center">Absent</th>
        <th>Comments</th>
      </tr>
    </thead>
    <tbody>
      @foreach($list as $row)
        <tr>
          <td>{{ $row['name'] }}</td>
          <td class="text-center"><span class="badge bg-success">{{ $row['present'] }}</span></td>
          <td class="text-center"><span class="badge bg-warning text-dark">{{ $row['late'] }}</span></td>
          <td class="text-center"><span class="badge bg-danger">{{ $row['absent'] }}</span></td>

          {{-- comments: show short text, full in tooltip --}}
          <td>
            @php $txt = $row['comments_text'] ?? ($row['comments'] ?? ''); @endphp
            @if(!empty($txt))
              <span title="{{ $txt }}">{{ \Illuminate\Support\Str::limit($txt, 60) }}</span>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@else
  {{-- =================== BY SESSIONS =================== --}}
  <table class="table align-middle">
    <thead>
      <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Topic</th>
        <th class="text-center">Present</th>
        <th class="text-center">Late</th>
        <th class="text-center">Absent</th>
        <th>Comments</th>
      </tr>
    </thead>
    <tbody>
      @foreach($list as $row)
        <tr>
          <td>{{ $row['date'] }}</td>
          <td>{{ $row['time'] }}</td>
          <td>{{ $row['topic'] }}</td>
          <td class="text-center"><span class="badge bg-success">{{ $row['present'] }}</span></td>
          <td class="text-center"><span class="badge bg-warning text-dark">{{ $row['late'] }}</span></td>
          <td class="text-center"><span class="badge bg-danger">{{ $row['absent'] }}</span></td>

          {{-- comments: show short text, full in tooltip --}}
          <td>
            @php $txt = $row['comments_text'] ?? ($row['comments'] ?? ''); @endphp
            @if(!empty($txt))
              <span title="{{ $txt }}">{{ \Illuminate\Support\Str::limit($txt, 60) }}</span>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif
@endsection
