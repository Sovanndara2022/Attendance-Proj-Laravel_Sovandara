<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSession;
use Carbon\Carbon;

class GenerateFutureSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $max = ClassSession::max('starts_at');
        if (!$max) return;

        $last     = Carbon::parse($max);
        $weekFrom = $last->copy()->startOfWeek();
        $weekTo   = $last->copy()->endOfWeek();

        $template = ClassSession::whereBetween('starts_at', [$weekFrom, $weekTo])->get();
        $weeks    = 12; // how many future weeks to add

        foreach (range(1, $weeks) as $k) {
            foreach ($template as $s) {
                $newStart = Carbon::parse($s->starts_at)->addWeeks($k);
                $newEnd   = Carbon::parse($s->ends_at)->addWeeks($k);

                ClassSession::firstOrCreate(
                    ['group_id' => $s->group_id, 'starts_at' => $newStart],
                    ['ends_at' => $newEnd, 'topic' => $s->topic]
                );
            }
        }
    }
}
