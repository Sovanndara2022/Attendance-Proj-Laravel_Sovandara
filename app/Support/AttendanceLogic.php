<?php

namespace App\Support;

final class AttendanceLogic
{
    // r=red, y=yellow, g=green. Returns: present|late|absent|unknown
    public static function classify(?string $present, ?string $online): string
    {
        $p = $present ?? '';
        $o = $online ?? '';

        if ($p === 'g' && $o === 'r') return 'present';
        if ($p === 'r' && $o === 'g') return 'present';
        if ($p === 'r' && $o === 'r') return 'absent';
        if (($p === 'y' && $o === 'r') || ($p === 'r' && $o === 'y') || ($p === 'y' && $o === 'y')) {
            return 'late';
        }
        if ($p === 'g' && $o === 'g') return 'present';
        return 'unknown';
    }
}
