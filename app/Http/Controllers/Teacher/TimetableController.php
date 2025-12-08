<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $groups  = Group::orderBy('name')->get();
        $groupId = (int) $request->query('group_id', $groups->first()->id ?? 0);
        $spec    = $request->query('spec', 'All');

        $anchor  = Carbon::parse($request->query('date', now()));
        $from    = (clone $anchor)->startOfWeek()->startOfDay();
        $to      = (clone $anchor)->endOfWeek()->endOfDay();

        // course list from groups.specialization
        $specializations = Group::query()
            ->whereNotNull('specialization')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization')
            ->values();

        $sessions = ClassSession::with('group')
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->when($spec !== 'All', fn($q) => $q->whereHas('group', fn($g) => $g->where('specialization', $spec)))
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at')
            ->get();

        // group sessions by day
        $byDay = [];
        foreach ($sessions as $s) {
            $key = Carbon::parse($s->starts_at)->toDateString();
            $byDay[$key][] = $s;
        }

        return view('teacher.timetable', [
            'from' => $from,
            'to' => $to,
            'anchor' => $anchor->copy(),
            'groups' => $groups,
            'specializations' => $specializations,
            'byDay' => $byDay,
            'groupId' => $groupId,
            'spec' => $spec,
        ]);
    }
}
