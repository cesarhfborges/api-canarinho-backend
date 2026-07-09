<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Endpoint;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group 4. Gerador de Mock
 *
 * APIs para geração automática de dados falsos para endpoints baseados em schema.
 * @authenticated
 */
class MockGeneratorController extends Controller
{
    /**
     * Gerar Dados Mock
     *
     * Gera e armazena registros falsos baseados no `resourceSchema` do Endpoint.
     * Opcionalmente, envie o parâmetro `count` no body (padrão 10).
     */
    public function generate(Request $request, $projectId, $id)
    {
        $this->validate($request, [
            'count' => 'required|int|min:0|max:100'
        ]);

        $endpoint = Endpoint::whereHas('project', function ($q) use ($request, $projectId) {
            $q->where('user_id', $request->user()->id)->where('id', $projectId);
        })->find($id);

        if (!$endpoint)
            return response()->json(['error' => 'Endpoint not found'], 404);

        $schema = $endpoint->resource_schema;
        if (!$schema || !is_array($schema)) {
            return response()->json(['error' => 'Endpoint does not have a valid resourceSchema'], 400);
        }

        $count = (int) $request->post('count', 10);

        $totalToGenerate = $count;
        if ($endpoint->parent_id) {
            $parentCount = \App\Models\MockData::where('endpoint_id', $endpoint->parent_id)->count();
            if ($parentCount === 0) {
                return response()->json(['error' => 'O endpoint pai não possui dados gerados. Gere os dados do pai primeiro.'], 400);
            }
            $totalToGenerate = $parentCount * $count;
        }

        if ($totalToGenerate > 5000) {
            return response()->json([
                'error' => "Limite máximo de 5000 registros atingido. A operação tentaria gerar {$totalToGenerate} registros."
            ], 422);
        }

        $endpoint->mockData()->delete();

        $mockDataService = new \App\Services\MockDataService();
        $result = $mockDataService->generateForEndpoint($endpoint, $count);
        $totalInserted = $result['inserted_count'] ?? 0;

        return response()->json([
            'message' => "Gerados {$totalInserted} registros com sucesso."
        ], 201);
    }
}
