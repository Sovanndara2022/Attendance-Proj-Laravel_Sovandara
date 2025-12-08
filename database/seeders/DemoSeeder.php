<?php
// database/seeders/DemoSeeder.php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Group;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // WHY: multiple groups/specializations to power the filters on Timetable & Group Attendance
        $groups = [
            [
                'name'  => 'FT SD E 17',
                'spec'  => 'Web Apps Development with PHP and MsSQL',
                'students' => [
                    'Bory Vireakboth','Bunthan Putthyqong','Chan Punleur','Chhim Sophal',
                    'Hem Chanthana','Heng Thaksin','Hok Seanghuy','Phallim Sovandara',
                    'Roeun Keovisal','Sang Visal','Sao Chhormey','Seang Keopiseth',
                    'Seng Sodevit','Sovann Channdavith','Srey Lyping','Ung Muyseang',
                    'Vann Yut','Y Taily',
                ],
            ],
            [
                'name'  => 'FT SD A 7',
                'spec'  => 'Frontend Development with React',
                'students' => [
                    'Chea Rith','Sok Samnang','Vong Dara','Mean Sothy','Rin Sophea','Mak Rachana',
                    'Sak Sovann','Kea Chantha','Thou Phearak','Sann Sreyna','Phan Rithy','Nov Kanha',
                    'Roeun Socheat','Long Rith','Pov Sokha',
                ],
            ],
            [
                'name'  => 'FT SD A 5',
                'spec'  => 'Mobile Apps Development with Flutter',
                'students' => [
                    'Kim Pisey','Chhun Nita','Ouk Chenda','San Panha','Ly Borey','Houy Ra',
                    'Chum Soveth','Kong Vicheka','Chou Socheat','Sreyneang Kim','Heak Vathana','Roth Sokny',
                ],
            ],
            [
                'name'  => 'FT SD B 9',
                'spec'  => 'Data Structures & Algorithms (C++)',
                'students' => [
                    'Kun Sok','Kosal Rith','Chanthou Neang','Dalin Ith','Sovath Pov','Daro Chan',
                    'Sothea Yem','Rina Thea','Malis So','Piseth Khin','Chetra Nhem','Srey Pich',
                ],
            ],
        ];

        $start = CarbonImmutable::now()->startOfWeek()->subWeeks(1);
        $end   = CarbonImmutable::now()->startOfWeek()->addWeeks(8)->endOfWeek();
        $slots = [['18:00','19:20'], ['19:30','20:50']]; // keep consistent with your UI

        foreach ($groups as $g) {
            $group = Group::query()->firstOrCreate(
                ['name' => $g['name']],
                ['specialization' => $g['spec']]
            );

            $students = collect($g['students'])
                ->map(fn (string $n) => Student::firstOrCreate(['group_id' => $group->id, 'full_name' => $n]));

            for ($day = $start; $day <= $end; $day = $day->addDay()) {
                if (in_array($day->dayOfWeekIso, [6, 7])) continue; // skip Sat/Sun

                foreach ($slots as [$h1, $h2]) {
                    $session = ClassSession::create([
                        'group_id'  => $group->id,
                        'starts_at' => $day->setTime(...explode(':', $h1)),
                        'ends_at'   => $day->setTime(...explode(':', $h2)),
                        'topic'     => 'Lesson ' . $day->format('n/j'),
                    ]);

                    foreach ($students as $stu) {
                        $isPast = $session->starts_at->lessThan(now());
                        Attendance::create([
                            'class_session_id' => $session->id,
                            'student_id'       => $stu->id,
                            'present_status'   => $isPast ? Arr::random([0,1,2,2,2,2]) : 2,
                            'online_status'    => $isPast ? Arr::random([0,1,2,2,2]) : 2,
                            'classwork_status' => 2,
                            'control_status'   => 2,
                            'thematic_status'  => 2,
                            'stars'            => $isPast ? Arr::random([0,1,2,3,4,5]) : 0,
                            'comment'          => null,
                            'marked_at'        => $isPast ? now()->subDays(rand(0, 6)) : null,
                        ]);
                    }
                }
            }
        }
    }
}
