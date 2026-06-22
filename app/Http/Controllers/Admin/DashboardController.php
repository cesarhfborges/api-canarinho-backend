<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Endpoint;
use App\Models\EndpointCall;
use Illuminate\Support\Facades\DB;

/**
 * @group 7. Dashboard Analytics
 *
 * APIs para prover os dados consolidados do dashboard administrativo.
 */
class DashboardController extends Controller
{
    /**
     * Obter Métricas do Dashboard
     *
     * Retorna os totais, gráficos e endpoints mais acessados do usuário logado.
     * @authenticated
     */
    public function metrics(Request $request)
    {
        $user = $request->user();

        // Quantidade de Projetos do usuário
        $totalProjects = $user->projects()->count();

        // Obter os IDs de todos os projetos do usuário
        $projectIds = $user->projects()->pluck('id');

        // Total de Endpoints
        $totalEndpoints = Endpoint::whereIn('project_id', $projectIds)->count();

        // Obter os IDs de todos os endpoints do usuário para filtrar as chamadas
        $endpointIds = Endpoint::whereIn('project_id', $projectIds)->pluck('id');

        // Totais de Chamadas
        $totalCalls = EndpointCall::whereIn('endpoint_id', $endpointIds)->count();
        $callsToday = EndpointCall::whereIn('endpoint_id', $endpointIds)
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Error Rate (Taxa de requisições com status >= 400)
        $errorCalls = EndpointCall::whereIn('endpoint_id', $endpointIds)
            ->where('status_code', '>=', 400)
            ->count();
        
        $errorRatePercentage = $totalCalls > 0 ? round(($errorCalls / $totalCalls) * 100, 2) : 0;

        // Top 5 Endpoints Mais Acessados
        $topEndpoints = EndpointCall::select('endpoint_id', DB::raw('count(*) as total_hits'))
            ->whereIn('endpoint_id', $endpointIds)
            ->groupBy('endpoint_id')
            ->orderBy('total_hits', 'desc')
            ->limit(5)
            ->with(['endpoint:id,name'])
            ->get()
            ->map(function ($call) {
                return [
                    'endpoint_id' => $call->endpoint_id,
                    'endpoint_name' => $call->endpoint ? $call->endpoint->name : 'Unknown',
                    'total_hits' => $call->total_hits
                ];
            });

        // Gráfico dos últimos 7 dias
        $last7Days = collect([]);
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $last7Days->push([
                'date' => $date->format('Y-m-d'),
                'hits' => 0
            ]);
        }

        $dailyHits = EndpointCall::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as hits'))
            ->whereIn('endpoint_id', $endpointIds)
            ->where('created_at', '>=', Carbon::today()->subDays(6))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartLast7Days = $last7Days->map(function ($item) use ($dailyHits) {
            if ($dailyHits->has($item['date'])) {
                $item['hits'] = $dailyHits[$item['date']]->hits;
            }
            return $item;
        });

        return response()->json([
            'total_projects' => $totalProjects,
            'total_endpoints' => $totalEndpoints,
            'total_calls' => $totalCalls,
            'calls_today' => $callsToday,
            'error_rate_percentage' => $errorRatePercentage,
            'top_endpoints' => $topEndpoints,
            'chart_last_7_days' => $chartLast7Days
        ]);
    }
}
