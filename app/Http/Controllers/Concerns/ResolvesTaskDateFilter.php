<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait ResolvesTaskDateFilter
{
    /**
     * @return array{from: string, to: string}
     */
    protected function taskDateRangeFromRequest(Request $request): array
    {
        $today = now()->toDateString();

        if ($request->filled('date') && ! $request->filled('date_from') && ! $request->filled('date_to')) {
            $date = $this->parseTaskFilterDate($request->input('date'), $today);

            return ['from' => $date, 'to' => $date];
        }

        $from = $this->parseTaskFilterDate($request->input('date_from', $today), $today);
        $to = $this->parseTaskFilterDate($request->input('date_to', $today), $today);

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array{date_from: string, date_to: string}
     */
    protected function taskDateFilterQueryFromRequest(Request $request): array
    {
        $range = $this->taskDateRangeFromRequest($request);

        return [
            'date_from' => $range['from'],
            'date_to' => $range['to'],
        ];
    }

    protected function parseTaskFilterDate(mixed $value, string $fallback): string
    {
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception) {
            return $fallback;
        }
    }
}
