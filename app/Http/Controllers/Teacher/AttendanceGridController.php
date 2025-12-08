<?php

namespace App\Http\Controllers\Teacher;


use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
class AttendanceGridController extends Controller
{
    private function classify(?string $p, ?string $o): string
    {
        // WHY: implement logic attendance
        if (($p === 'g' && $o === 'r') || ($p === 'r' && $o === 'g')) return 'present';
        if ($p === 'r' && $o === 'r') return 'absent';
        if (($p === 'y' && $o === 'r') || ($p === 'r' && $o === 'y') || ($p === 'y' && $o === 'y')) return 'late';
        return 'none';
    }

    public function index(Request $request, Group $group)
    {
        $picked = $request->input('date');
        $date = $picked ? Carbon::parse($picked) : now();
        $from = $date->copy()->startOfWeek();
        $to   = $date->copy()->endOfWeek();

        $sessions = ClassSession::query()
            ->where('group_id', $group->id)
            ->whereBetween('start', [$from, $to])
            ->orderBy('start')
            ->get()
            ->keyBy('id');

        // attendance keyed by student and session
        $rows = Attendance::query()
            ->with('student')
            ->whereIn('class_session_id', $sessions->keys())
            ->get()
            ->groupBy('student_id');

        // per-student totals over the week
        $totals = [];
        foreach ($rows as $studentId => $atts) {
            $p = $l = $a = 0;
            foreach ($atts as $att) {
                $cls = $this->classify($att->present, $att->online);
                if ($cls === 'present') $p++;
                elseif ($cls === 'late') $l++;
                elseif ($cls === 'absent') $a++;
            }
            $totals[$studentId] = compact('p','l','a');
        }

        return view('teacher.group_attendance', [
            'group'     => $group,
            'sessions'  => $sessions,
            'rows'      => $rows,
            'totals'    => $totals,
            'from'      => $from,
            'to'        => $to,
            'date'      => $date,
        ]);
    }

    public function csv(Request $request, Group $group): StreamedResponse
    {
        $picked = $request->input('date');
        $date = $picked ? Carbon::parse($picked) : now();
        $from = $date->copy()->startOfWeek();
        $to   = $date->copy()->endOfWeek();

        $sessions = ClassSession::query()
            ->where('group_id', $group->id)
            ->whereBetween('start', [$from, $to])
            ->orderBy('start')
            ->get();

        $rows = Attendance::query()
            ->with('student')
            ->whereIn('class_session_id', $sessions->pluck('id'))
            ->get()
            ->groupBy('class_session_id');

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=group_attendance_' . $from->toDateString() . '_' . $to->toDateString() . '.csv',
        ];

        return response()->stream(function () use ($sessions, $rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Student', 'Date', 'Start', 'End', 'Present', 'Online', 'Class', 'Topic', 'Result']);
            foreach ($sessions as $s) {
                foreach (($rows->get($s->id) ?? collect()) as $att) {
                    $present = $att->present ?? '';
                    $online  = $att->online ?? '';
                    $result  = $this->classify($present, $online);
                    fputcsv($out, [
                        optional($att->student)->full_name,
                        $s->start->toDateString(),
                        $s->start->format('H:i'),
                        $s->end->format('H:i'),
                        $present,
                        $online,
                        optional($s->group)->code,
                        $s->topic,
                        $result,
                    ]);
                }
            }
            fclose($out);
        }, 200, $headers);
    }
}
