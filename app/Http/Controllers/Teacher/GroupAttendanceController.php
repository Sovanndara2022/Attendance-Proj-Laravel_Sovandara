<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Group;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GroupAttendanceController extends Controller
{
    public function grid(Request $request, Group $group)
    {
        // Filters
        $groups  = Group::orderBy('name')->get();                  // for the Group <select>
        $groupId = (int) $request->query('group', $group->id);     // allow switching group
        $spec    = $request->query('spec', 'All');                 // course name (group.specialization)
        $date    = Carbon::parse($request->query('date', now()));
        $from    = (clone $date)->startOfWeek();
        $to      = (clone $date)->endOfWeek();

        // Sessions for the week (optionally filter by group specialization = course name)
        $sessions = ClassSession::with('group')
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($spec !== 'All', fn ($q) => $q->whereHas('group', fn ($g) => $g->where('specialization', $spec)))
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at')
            ->get();

        // Distinct course names (from groups.specialization) for the Specialization dropdown
        $specializations = Group::query()
            ->whereNotNull('specialization')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization')
            ->values();

        // Pull attendance rows (grouped by session)
        $rowsBySession = Attendance::with('student')
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->get()
            ->groupBy('class_session_id');

        // Build students list: prefer students table by group_id; else union from rows
        $students = collect();
        if (Schema::hasColumn('students', 'group_id')) {
            $students = Student::where('group_id', $groupId)->orderBy('full_name')->get();
        }
        if ($students->isEmpty()) {
            $students = $rowsBySession
                ->flatMap(fn ($rows) => $rows->pluck('student'))
                ->unique('id')
                ->sortBy('full_name')
                ->values();
        }

        // --- Compute cells map and totals --------------------------------------
        // cells[student_id][session_id] = 'P' | 'L' | 'A' | null
        $cells  = [];
        $totals = []; // ['p'=>..,'l'=>..,'a'=>..] per student

        foreach ($students as $stu) {
            $p = $l = $a = 0;

            foreach ($sessions as $s) {
                $att = ($rowsBySession[$s->id] ?? collect())->firstWhere('student_id', $stu->id);
                if (!$att) {
                    $cells[$stu->id][$s->id] = null;
                    continue;
                }

                $ps = (int) $att->present_status;
                $os = (int) $att->online_status;

                // agreed logic
                if ($ps === 0 && $os === 0) {           // R/R -> absent
                    $letter = 'A'; $a++;
                } elseif (
                    ($ps === 1 && $os === 0) ||         // Y/R -> late
                    ($ps === 0 && $os === 1) ||         // R/Y -> late
                    ($ps === 1 && $os === 1)            // Y/Y -> late
                ) {
                    $letter = 'L'; $l++;
                } else {                                 // otherwise -> present
                    $letter = 'P'; $p++;
                }

                $cells[$stu->id][$s->id] = $letter;
            }

            // IMPORTANT: lower-case keys so Blade can read $t['p'], $t['l'], $t['a']
            $totals[$stu->id] = ['p' => $p, 'l' => $l, 'a' => $a];
        }
        // -----------------------------------------------------------------------

        // Also provide an index by student if the view needs raw rows
        $attendanceByStu = $rowsBySession
            ->flatMap(fn ($c) => $c)
            ->groupBy('student_id')
            ->map(fn ($c) => $c->keyBy('class_session_id'));

        // Resolve the currently selected group object
        $currentGroup = $groupId ? ($groups->firstWhere('id', $groupId) ?? $group) : $group;

        return view('teacher.group_attendance', [
            'groups'          => $groups,
            'group'           => $currentGroup,
            'groupId'         => $groupId ?: $group->id,
            'spec'            => $spec,
            'specializations' => $specializations,
            'from'            => $from,
            'to'              => $to,
            'sessions'        => $sessions,
            'students'        => $students,
            'cells'           => $cells,
            'totals'          => $totals,
            'attendanceByStu' => $attendanceByStu,
        ]);
    }

    public function csv(Request $request, Group $group)
    {
        $date = Carbon::parse($request->query('date', now()));
        $from = (clone $date)->startOfWeek();
        $to   = (clone $date)->endOfWeek();

        $sessions = ClassSession::where('group_id', $group->id)
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at')
            ->get();

        $rowsBySession = Attendance::with('student')
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->get()
            ->groupBy('class_session_id');

        $filename = 'group_grid_'.$group->code.'_'.$from->toDateString().'_'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($sessions, $rowsBySession) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Start', 'End', 'Student', 'Present', 'Online', 'Stars', 'Comment']);
            foreach ($sessions as $s) {
                $start = Carbon::parse($s->starts_at);
                foreach ($rowsBySession[$s->id] ?? [] as $att) {
                    fputcsv($out, [
                        $start->toDateString(),
                        $start->format('H:i'),
                        Carbon::parse($s->ends_at)->format('H:i'),
                        $att->student->full_name ?? '',
                        $att->present_status,
                        $att->online_status,
                        $att->stars,
                        $att->comment,
                    ]);
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
