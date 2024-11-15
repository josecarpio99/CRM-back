<?php

namespace App\Traits;

use Carbon\Carbon;

trait FilterableByDates
{
    public function scopeToday($query, $column = 'created_at')
    {
        return $query->whereDate($column, Carbon::today());
    }

    public function scopeYesterday($query, $column = 'created_at')
    {
        return $query->whereDate($column, Carbon::yesterday());
    }

    public function scopeMonthToDate($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()]);
    }

    public function scopeThisWeek($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeLastWeek($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek()
        ]);
    }

    public function scopeQuarterToDate($query, $column = 'created_at')
    {
        $now = Carbon::now();
        return $query->whereBetween($column, [$now->startOfQuarter(), $now]);
    }

    public function scopeYearToDate($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [Carbon::now()->startOfYear(), Carbon::now()]);
    }

    public function scopeLast7Days($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [Carbon::today()->subDays(6), Carbon::now()]);
    }

    public function scopeLast30Days($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [Carbon::today()->subDays(29), Carbon::now()]);
    }

    public function scopeLastQuarter($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->subMonths(3)->startOfQuarter(),
            Carbon::now()->subMonths(3)->endOfQuarter()
        ]);
    }

    public function scopeThisQuarter($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->startOfQuarter(),
            Carbon::now()->endOfQuarter()
        ]);
    }

    public function scopeThisMonth($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    public function scopeLastMonth($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        ]);
    }

    public function scopeLastYear($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->subYear()->startOfYear(),
            Carbon::now()->subYear()->endOfYear()
        ]);
    }

    public function scopeThisYear($query, $column = 'created_at')
    {
        return $query->whereBetween($column, [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        ]);
    }

    public function scopeFilterDateByStr($query, $value, $column = 'created_at')
    {
        return match ($value) {
            'today' => $query->today($column),
            'yesterday' => $query->yesterday($column),
            'this_week' => $query->thisWeek($column),
            'last_week' => $query->lastWeek($column),
            'this_month' => $query->thisMonth($column),
            'last_month' => $query->lastMonth($column),
            'this_quarter' => $query->thisQuarter($column),
            'last_quarter' => $query->lastquarter($column),
            'this_year' => $query->thisYear($column),
            'last_year' => $query->lastYear($column),
        };
    }
}
