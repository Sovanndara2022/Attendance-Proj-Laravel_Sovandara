<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Explicit is fine; Laravel would infer "attendances" anyway.
    protected $table = 'attendances';

    protected $fillable = [
        'class_session_id',
        'student_id',
        'present_status', // 2=green, 1=yellow, 0=red
        'online_status',  // 2=green, 1=yellow, 0=red
        'stars',
        'comment',
        'marked_at',
    ];

    protected $casts = [
        'class_session_id' => 'int',
        'student_id'       => 'int',
        'present_status'   => 'int',
        'online_status'    => 'int',
        'stars'            => 'int',
        'marked_at'        => 'datetime',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function session() { return $this->belongsTo(ClassSession::class, 'class_session_id'); }

    /**
     * Logic:
     * - G/R or R/G => present
     * - R/R => absent
     * - Y/R, R/Y, Y/Y => late
     * - any other combo => present
     */
    public function outcome(): string
    {
        $p = (int) $this->present_status;
        $o = (int) $this->online_status;

        if ($p === 0 && $o === 0) return 'absent';
        if (($p === 1 && $o === 0) || ($p === 0 && $o === 1) || ($p === 1 && $o === 1)) return 'late';
        if (($p === 2 && $o === 0) || ($p === 0 && $o === 2)) return 'present';
        if ($p === 2 || $o === 2) return 'present';
        return 'present';
    }
}
