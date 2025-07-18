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
}
