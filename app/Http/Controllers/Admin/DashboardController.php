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

        return response()->json([
            'total_projects' => $totalProjects,
            'total_endpoints' => $totalEndpoints,
            'total_calls' => $totalCalls,
            'calls_today' => $callsToday,
            'error_rate_percentage' => $errorRatePercentage,
            'top_endpoints' => $topEndpoints
        ]);
    }

    /**
     * Obter Dados do Gráfico (Histórico)
     *
     * Retorna o histórico de acessos (hits) agrupados por hora, dia ou mês.
     * @authenticated
     * 
     * @queryParam start string Data inicial (formato YYYY-MM-DD HH:MM:SS). Opcional (Padrão: 7 dias atrás).
     * @queryParam end string Data final (formato YYYY-MM-DD HH:MM:SS). Opcional (Padrão: agora).
     * @queryParam groupby string Agrupamento dos dados (hour, day, month). Opcional (Padrão: day).
     * @queryParam endpoint_id int ID do endpoint para filtrar. Opcional.
     */
    public function chart(Request $request)
    {
        $user = $request->user();

        // Obter projetos e endpoints do usuário
        $projectIds = $user->projects()->pluck('id');
        $userEndpointIds = Endpoint::whereIn('project_id', $projectIds)->pluck('id');

        // Filtro por endpoint_id específico
        if ($request->has('endpoint_id')) {
            if (!$userEndpointIds->contains($request->endpoint_id)) {
                return response()->json(['error' => 'Endpoint not found or not owned by user.'], 403);
            }
            $endpointIds = collect([$request->endpoint_id]);
        } else {
            $endpointIds = $userEndpointIds;
        }

        $groupby = $request->input('groupby', 'day'); // hour, day, month
        $start = $request->has('start') ? Carbon::parse($request->start) : Carbon::today()->subDays(6);
        $end = $request->has('end') ? Carbon::parse($request->end) : Carbon::now();

        // Determinar o formato SQL para o agrupamento
        // MySQL DATE_FORMAT
        if ($groupby === 'hour') {
            $sqlFormat = '%Y-%m-%d %H:00:00';
            $phpFormat = 'Y-m-d H:00:00';
            $interval = 'addHour';
        } elseif ($groupby === 'month') {
            $sqlFormat = '%Y-%m';
            $phpFormat = 'Y-m';
            $interval = 'addMonth';
        } else { // day
            $sqlFormat = '%Y-%m-%d';
            $phpFormat = 'Y-m-d';
            $interval = 'addDay';
        }

        // Fazer a query no banco agrupando
        $dbHits = EndpointCall::select(DB::raw("DATE_FORMAT(created_at, '{$sqlFormat}') as date_group"), DB::raw('count(*) as hits'))
            ->whereIn('endpoint_id', $endpointIds)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('date_group')
            ->orderBy('date_group', 'asc')
            ->get()
            ->keyBy('date_group');

        // Preencher buracos vazios no intervalo de datas
        $chartData = [];
        
        $currentDate = $start->copy();
        
        // Ajuste inicial para alinhar com o formato
        if ($groupby === 'hour') {
            $currentDate->minute(0)->second(0);
        } elseif ($groupby === 'day') {
            $currentDate->startOfDay();
        } elseif ($groupby === 'month') {
            $currentDate->startOfMonth();
        }

        while ($currentDate <= $end) {
            $dateStr = $currentDate->format($phpFormat);
            
            $chartData[] = [
                'date' => $dateStr,
                'hits' => $dbHits->has($dateStr) ? $dbHits[$dateStr]->hits : 0
            ];

            $currentDate->$interval();
        }

        return response()->json($chartData);
    }
}
