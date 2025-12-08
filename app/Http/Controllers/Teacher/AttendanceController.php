<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function edit(ClassSession $session)
    {
        $attendance = Attendance::query()
            ->from('attendances as a')
            ->join('students as s', 's.id', '=', 'a.student_id')
            ->select('a.*')
            ->where('a.class_session_id', $session->id)
            ->orderBy('s.full_name')
            ->with('student')
            ->get();

        return view('teacher.session_edit', compact('session', 'attendance'));
    }

    public function update(Request $request, ClassSession $session): RedirectResponse
    {
        $items = $request->input('items', []);

        // Map radio values (either letters g/y/r or numbers 2/1/0) to 2/1/0
        $map = ['g' => 2, 'y' => 1, 'r' => 0];

        foreach ($items as $attId => $payload) {
            $att = Attendance::find((int) $attId);
            if (! $att) continue;

            // present_status
            if (array_key_exists('present_status', $payload)) {
                $p = $payload['present_status'];
                $att->present_status = array_key_exists($p, $map) ? $map[$p] : (is_numeric($p) ? (int) $p : null);
            }

            // online_status
            if (array_key_exists('online_status', $payload)) {
                $o = $payload['online_status'];
                $att->online_status = array_key_exists($o, $map) ? $map[$o] : (is_numeric($o) ? (int) $o : null);
            }

          
            if (array_key_exists('stars', $payload)) {
                $att->stars = is_numeric($payload['stars']) ? (int) $payload['stars'] : ($att->stars ?? 0);
            }

            $att->comment  = $payload['comment'] ?? $att->comment;
            $att->marked_at = now();
            $att->save();
        }

        return back()->with('status', 'Attendance saved.');
    }
}
