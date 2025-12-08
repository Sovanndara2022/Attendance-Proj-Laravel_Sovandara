<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;



class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $groups  = Group::orderBy('name')->get();
        $groupId = (int) $request->get('group', $groups->first()->id ?? 0);
        $mode    = $request->get('mode', 'sessions'); // 'sessions' | 'students'

        $anchor  = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        $from    = (clone $anchor)->startOfWeek();
        $to      = (clone $anchor)->endOfWeek();

        $sessions = ClassSession::with('group')
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at', 'asc')
            ->get();

        $rows = Attendance::with('student')
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->get();

        // ---------- agreed logic helper ----------
        $calc = function(int $p, int $o): string {
            if ($p===0 && $o===0) return 'A';
            if (($p===1 && $o===0) || ($p===0 && $o===1) || ($p===1 && $o===1)) return 'L';
            return 'P';
        };

        if ($mode === 'students') {
            // group by student across the week
            $byStudent = $rows->groupBy('student_id');
            $list = [];

            foreach ($byStudent as $studentId => $items) {
                $present = $late = $absent = 0;

                foreach ($items as $r) {
                    $letter = $calc((int)$r->present_status, (int)$r->online_status);
                    if ($letter==='P') $present++; elseif ($letter==='L') $late++; else $absent++;
                }

                // build a readable comments string: "Mon 1 (18:00): comment • Tue 2 (19:30): comment"
                $comments = $items->filter(fn($r) => filled($r->comment))
                    ->map(function($r){
                        $s = $r->classSession ?? null;
                        $t = $s ? Carbon::parse($s->starts_at)->isoFormat('ddd H:mm') : '';
                        $name = trim($r->student->full_name ?? '');
                        return ($name ? "$name – " : '') . ($t ? "($t) " : '') . trim($r->comment);
                    })
                    ->implode(' • ');

                $list[] = [
                    'name'          => optional($items->first()->student)->full_name ?? '—',
                    'present'       => $present,
                    'late'          => $late,
                    'absent'        => $absent,
                    'comments_text' => $comments,
                ];
            }
        } else {
            // by sessions (row per class)
            $bySession = $rows->groupBy('class_session_id');
            $list = [];

            foreach ($sessions as $s) {
                $items   = $bySession[$s->id] ?? collect();
                $present = $late = $absent = 0;

                foreach ($items as $r) {
                    $letter = $calc((int)$r->present_status, (int)$r->online_status);
                    if ($letter==='P') $present++; elseif ($letter==='L') $late++; else $absent++;
                }

                // comments like "Student: comment"
                $comments = $items->filter(fn($r)=>filled($r->comment))
                    ->map(fn($r)=> (trim($r->student->full_name ?? '') ? trim($r->student->full_name).': ' : '').trim($r->comment))
                    ->implode(' • ');

                $start = Carbon::parse($s->starts_at);
                $list[] = [
                    'date'          => $start->toDateString(),
                    'time'          => $start->format('H:i') . '–' . Carbon::parse($s->ends_at)->format('H:i'),
                    'topic'         => $s->topic,
                    'present'       => $present,
                    'late'          => $late,
                    'absent'        => $absent,
                    'comments_text' => $comments,
                ];
            }
        }

        return view('teacher.report', compact('groups','groupId','from','to','mode','list'));
    }

    public function csv(Request $request)
{
    $mode    = $request->query('mode', 'students');     // 'students' | 'sessions'
    $groupId = (int) $request->query('group', 0);
    $date    = Carbon::parse($request->query('date', now()));
    $from    = (clone $date)->startOfWeek();
    $to      = (clone $date)->endOfWeek();

    // Sessions for the selected group + week
    $sessions = ClassSession::query()
        ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
        ->whereBetween('starts_at', [$from, $to])
        ->orderBy('starts_at', 'asc')
        ->get();

    // All attendance rows for those sessions
    $rows = Attendance::with('student')
        ->whereIn('class_session_id', $sessions->pluck('id'))
        ->orderBy('id')
        ->get();

    // P/L/A logic
    $calc = function (int $p, int $o): string {
        if ($p===0 && $o===0) return 'A';                                         // R/R → absent
        if (($p===1&&$o===0) || ($p===0&&$o===1) || ($p===1&&$o===1)) return 'L'; // Y/R, R/Y, Y/Y → late
        return 'P';                                                               // otherwise → present
    };

    $filename      = 'reports_'.$from->toDateString().'_'.$to->toDateString().'.csv';
    $sessionsById  = $sessions->keyBy('id');

    return response()->streamDownload(function () use ($mode, $sessions, $rows, $calc, $sessionsById) {
        $out = fopen('php://output', 'w');

        if ($mode === 'sessions') {
            // One row per session; include full comments joined as "Name – text"
            fputcsv($out, ['Date','Time','Topic','Present','Late','Absent','Comments']);
            foreach ($sessions as $s) {
                $start    = Carbon::parse($s->starts_at);
                $bucket   = $rows->where('class_session_id', $s->id);
                $p = $l = $a = 0;
                $comments = [];

                foreach ($bucket as $att) {
                    $letter = $calc((int)$att->present_status, (int)$att->online_status);
                    if ($letter === 'P') $p++; elseif ($letter === 'L') $l++; else $a++;

                    $c = trim((string)$att->comment);
                    if ($c !== '') {
                        $name = optional($att->student)->full_name ?? 'Unknown';
                        $comments[] = $name.' – '.$c;
                    }
                }

                fputcsv($out, [
                    $start->toDateString(),
                    $start->format('H:i').'–'.Carbon::parse($s->ends_at)->format('H:i'),
                    $s->topic,
                    $p, $l, $a,
                    $comments ? implode(' | ', $comments) : '',
                ]);
            }
        } else {
            // One row per student aggregated over the week; include all comment texts
            fputcsv($out, ['Student','Present','Late','Absent','Comments']);
            $byStudent = $rows->groupBy('student_id');

            foreach ($byStudent as $studentId => $items) {
                $name     = optional($items->first()->student)->full_name ?? 'Unknown';
                $p = $l = $a = 0;
                $comments = [];

                foreach ($items as $att) {
                    $letter = $calc((int)$att->present_status, (int)$att->online_status);
                    if ($letter === 'P') $p++; elseif ($letter === 'L') $l++; else $a++;

                    $c = trim((string)$att->comment);
                    if ($c !== '') {
                        $s = $sessionsById[$att->class_session_id] ?? null;
                        $when = $s ? Carbon::parse($s->starts_at)->isoFormat('ddd, MMM D') : '';
                        $comments[] = ($when ? $when.': ' : '').$c;
                    }
                }

                fputcsv($out, [$name, $p, $l, $a, implode(' | ', $comments)]);
            }
        }

        fclose($out);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
}
}

