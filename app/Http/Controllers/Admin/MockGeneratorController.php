<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Endpoint;
use Illuminate\Http\Request;
use Faker\Factory as Faker;
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
     * Opcionalmente, envie o parâmetro `count` na query string (padrão 10).
     */
    public function generate(Request $request, $projectId, $id)
    {
        $endpoint = Endpoint::whereHas('project', function($q) use ($request, $projectId) {
            $q->where('user_id', $request->user()->id)->where('id', $projectId);
        })->find($id);

        if (!$endpoint) return response()->json(['error' => 'Endpoint not found'], 404);

        $schema = $endpoint->resource_schema;
        if (!$schema || !is_array($schema)) {
            return response()->json(['error' => 'Endpoint does not have a valid resourceSchema'], 400);
        }

        $count = (int) $request->query('count', 10);
        
        $mockDataService = new \App\Services\MockDataService();
        $generatedData = $mockDataService->generateForEndpoint($endpoint, $count);

        return response()->json([
            'message' => "Generated {$count} records successfully.",
            'data' => $generatedData
        ], 201);
    }
}
