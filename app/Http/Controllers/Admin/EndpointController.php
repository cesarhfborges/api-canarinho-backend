<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Endpoint;
use Illuminate\Http\Request;

/**
 * @group 3. Gestão de Endpoints
 *
 * APIs para criar e gerenciar os endpoints de um projeto.
 * @authenticated
 */
class EndpointController extends Controller
{
    /**
     * Listar Endpoints
     *
     * Retorna todos os endpoints vinculados a um projeto.
     */
    public function index(Request $request, $projectId)
    {
        $project = $request->user()->projects()->find($projectId);
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        return response()->json($project->endpoints);
    }

    /**
     * Criar Endpoint
     *
     * Adiciona um novo endpoint ao projeto especificando as configurações de CRUD.
     */
    public function store(Request $request, $projectId)
    {
        $project = $request->user()->projects()->find($projectId);
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        $this->validate($request, [
            'name' => 'required|string',
            'generator' => 'nullable|string',
            'endpoints' => 'required|array',
            'resourceSchema' => 'required|array'
        ]);

        if ($project->endpoints()->where('name', $request->name)->exists()) {
            return response()->json(['error' => 'Endpoint name already exists in this project.'], 400);
        }

        $endpoint = $project->endpoints()->create([
            'name' => $request->name,
            'generator' => $request->generator,
            'endpoints_config' => $request->endpoints,
            'resource_schema' => $request->resourceSchema
        ]);

        return response()->json($endpoint, 201);
    }

    /**
     * Obter Endpoint
     *
     * Retorna os detalhes de um endpoint específico.
     */
    public function show(Request $request, $projectId, $id)
    {
        $endpoint = Endpoint::whereHas('project', function($q) use ($request, $projectId) {
            $q->where('user_id', $request->user()->id)->where('id', $projectId);
        })->find($id);

        if (!$endpoint) return response()->json(['error' => 'Not found'], 404);

        return response()->json($endpoint);
    }

    /**
     * Atualizar Endpoint
     *
     * Altera as configurações e permissões de um endpoint existente.
     */
    public function update(Request $request, $id)
    {
        $endpoint = Endpoint::whereHas('project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($id);

        if (!$endpoint) return response()->json(['error' => 'Not found'], 404);

        $this->validate($request, [
            'name' => 'sometimes|required|string',
            'generator' => 'nullable|string',
            'endpoints' => 'sometimes|required|array',
            'resourceSchema' => 'sometimes|required|array'
        ]);

        if ($request->has('name') && $request->name !== $endpoint->name) {
            if ($endpoint->project->endpoints()->where('name', $request->name)->exists()) {
                return response()->json(['error' => 'Endpoint name already exists in this project.'], 400);
            }
        }

        $schemaChanged = false;

        if ($request->has('name')) $endpoint->name = $request->name;
        if ($request->has('generator')) $endpoint->generator = $request->generator;
        if ($request->has('endpoints')) $endpoint->endpoints_config = $request->endpoints;
        if ($request->has('resourceSchema')) {
            $endpoint->resource_schema = $request->resourceSchema;
            $schemaChanged = true;
        }
        
        $endpoint->save();

        if ($schemaChanged) {
            $mockDataService = new \App\Services\MockDataService();
            $mockDataService->syncMockDataWithSchema($endpoint);
        }

        return response()->json($endpoint);
    }

    /**
     * Deletar Endpoint
     *
     * Remove um endpoint e todos os dados associados a ele.
     */
    public function destroy(Request $request, $id)
    {
        $endpoint = Endpoint::whereHas('project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($id);

        if (!$endpoint) return response()->json(['error' => 'Not found'], 404);

        $endpoint->delete();
        return response()->json(null, 204);
    }
}
