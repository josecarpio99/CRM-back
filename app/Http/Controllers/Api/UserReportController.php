<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Enums\DealStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;

class UserReportController extends ApiController
{
    public function report(Request $request, User $user)
    {
        $startDate = $request->date('start_date') ?? now()->startOfWeek();
        $endDate = $request->date('end_date') ? $request->date('end_date')->endOfDay() : now()->endOfWeek();

        return $this->responseSuccess($this->generateReportData($startDate, $endDate, $user));
    }

    // public function pdf(Request $request)
    // {
    //     // return view('pdf.user_report');
    //     $pdf = Pdf::loadView('pdf.user_report');
    //     return $pdf->stream('radiografia_asesor.pdf');
    // }

    private function generateReportData(Carbon $startDate, Carbon $endDate, User $user): array
    {
        $customersCount = $user->customers()
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $customerTasksCount = $user->tasks()
            ->where('taskable_type', Customer::class)
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->where('done', false)
            ->count();

        $leadsCount = $user->leads()
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $leadsTasksCount = $user->tasks()
            ->where('taskable_type', Lead::class)
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->where('done', false)
            ->count();

        $totalDealsLast90Days = $user->deals()
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->whereBetween('created_at', [now()->subDays(90), now()])
            ->count();

        $top5Deals = $user->deals()
            ->with(['customer'])
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('value', 'desc')
            ->limit(5)
            ->get();

        $last6Months = now()->subMonths(6)->startOfMonth()->format('Y-m-d');

        $last6MonthsDeals = Deal::query()
            ->select(
                DB::raw('MONTH(stage_moved_at) as month'),
                DB::raw('AVG(value) as value_avg')
            )
            ->where('owner_id', $user->id)
            ->where('status', DealStatusEnum::Won->value)
            ->whereBetween('stage_moved_at', [$last6Months, now()])
            ->groupByRaw('MONTH(stage_moved_at)')
            ->get();

        $wonDeals = $user->wonDeals()
            // ->whereBetween('stage_moved_at', [$startDate, $endDate])
            ->get();

        $wonDealsCount = $wonDeals->count();
        $wonDealsValue = $wonDeals->sum('value');

        $activeDeals = $user->activeDeals()
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $warningDeals = $user->warningDeals()
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $warningDealsCount = $warningDeals->count();

        $wonDealsLastWeek = $user->wonDeals()
            ->whereBetween('stage_moved_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->get();

        $wonDealsLast30Days = $user->wonDeals()
            ->whereBetween('stage_moved_at', [now()->subDays(30), now()])
            ->get();

        $wonDealsLast90Days = $user->wonDeals()
            ->whereBetween('stage_moved_at', [now()->subDays(90), now()])
            ->get();

        $wonDealsLast90DaysCount = $wonDealsLast90Days->count();
        $wonDealsLast90DaysValue = $wonDealsLast90Days->sum('value');

        $activeDealsWithQuoteLastWeek = $user->activeDeals()
            ->has('quotes')
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->get();

        $activeDealsWithQuoteLast30Days = $user->activeDeals()
            ->has('quotes')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->get();

        $activeDealsWithQuoteLast90Days = $user->activeDeals()
            ->has('quotes')
            ->whereBetween('created_at', [now()->subDays(90), now()])
            ->get();

        $dealsGrouppBySource = $user->deals()
            ->select('source_id', DB::raw('SUM(value) as value'), DB::raw('COUNT(id) as count'))
            // ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('source_id')
            ->get();

        $manager = ($user->hasRole(RoleEnum::Advisor->value) || $user->hasRole(RoleEnum::TeamLeader->value)) ?
            $user->managers()->first() :
            null;

        return [
            'deal_sources' => [
                'prospeccion' => $dealsGrouppBySource->where('source_id', 1)->first()?->toArray() ?? [
                    'value' => 0,
                    'count' => 0
                ],
                'publicidad' => $dealsGrouppBySource->where('source_id', 2)->first()?->toArray() ?? [
                    'value' => 0,
                    'count' => 0
                ],
                'recompra' => $dealsGrouppBySource->where('source_id', 3)->first()?->toArray() ?? [
                    'value' => 0,
                    'count' => 0
                ],
            ],
            'sales' => [
                [
                    'period' => 'Semana pasada',
                    'count' => $wonDealsLastWeek->count(),
                    'value' => $wonDealsLastWeek->sum('value')
                ],
                [
                    'period' => 'Últimos 30 días',
                    'count' => $wonDealsLast30Days->count(),
                    'value' => $wonDealsLast30Days->sum('value')
                ],
                [
                    'period' => 'Últimos 90 días',
                    'count' => $wonDealsLast90Days->count(),
                    'value' => $wonDealsLast90Days->sum('value')
                ],
            ],
            'deals_quoted' => [
                [
                    'period' => 'Semana pasada',
                    'count' => $activeDealsWithQuoteLastWeek->count(),
                    'value' => $activeDealsWithQuoteLastWeek->sum('value')
                ],
                [
                    'period' => 'Últimos 30 días',
                    'count' => $activeDealsWithQuoteLast30Days->count(),
                    'value' => $activeDealsWithQuoteLast30Days->sum('value')
                ],
                [
                    'period' => 'Últimos 90 días',
                    'count' => $activeDealsWithQuoteLast90Days->count(),
                    'value' => $activeDealsWithQuoteLast90Days->sum('value')
                ]

            ],
            'customers' => [
                'count' => $customersCount,
                'task_count' => $customerTasksCount
            ],
            'leads' => [
                'count' => $leadsCount,
                'task_count' => $leadsTasksCount
            ],
            'warning_deals' => [
                'count' => $warningDealsCount,
            ],
            'active_deals' => [
                'count' => $activeDeals->count(),
                'value' => $activeDeals->sum('value')
            ],
            'hit_rate' =>  ($wonDealsLast90DaysCount > 0 && $totalDealsLast90Days > 0) ? $wonDealsLast90DaysCount * 100 / $totalDealsLast90Days : 0,
            'roa' => ($wonDealsLast90DaysValue > 0 && $totalDealsLast90Days > 0) ? $wonDealsLast90DaysValue / ($totalDealsLast90Days * 450) : 0,
            'top_deals' => $top5Deals,
            'average_monthly_sales' => $last6MonthsDeals->sum('value_avg') / 6,

            'total_deals' => $totalDealsLast90Days,
            'won_deals' => [
                'count' => $wonDealsCount,
                'value' => $wonDealsValue
            ],
            'user' => new UserResource($user),
            'manager' => $manager ? [
                    'name' => $manager->name ?? null
                ] : null
        ];
    }
}
