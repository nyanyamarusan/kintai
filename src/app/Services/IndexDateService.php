<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class IndexDateService
{
    public function getDaysOfMonth(int $year, int $month): Collection
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $days = collect();
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $days->push($date->copy());
        }

        return $days;
    }

    public function getPreviousCurrentNextDate(int $year, int $month, int $day): Collection
    {
        $current = Carbon::createFromDate($year, $month, $day);

        return collect([
            'previous' => $current->copy()->subDay(),
            'current' => $current,
            'next' => $current->copy()->addDay(),
        ]);
    }
}
