<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $attendances;

    protected $user;

    public function __construct($attendances, $user)
    {
        $this->attendances = $attendances;
        $this->user = $user;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ];
    }

    public function map($attendance): array
    {
        return [
            Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD（dd）'),
            $attendance->clock_in ?? '',
            $attendance->clock_out ?? '',
            $attendance->formatted_total_rest ?? '',
            $attendance->formatted_total_work ?? '',
        ];
    }
}
