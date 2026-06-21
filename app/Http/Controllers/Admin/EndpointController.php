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

        $query = $project->endpoints();

        if ($request->has('filterBy') && $request->has('filter')) {
            $filterBy = explode(',', $request->filterBy);
            $filter = $request->filter;
            
            $query->where(function ($q) use ($filterBy, $filter) {
                foreach ($filterBy as $field) {
                    $q->orWhere(trim($field), 'like', '%' . $filter . '%');
                }
            });
        }

        if ($request->has('orderBy')) {
            $orderDir = strtolower($request->get('orderDir', 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->orderBy, $orderDir);
        }

        return response()->json($query->get());
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
            'endpoints' => 'nullable|array',
            'resourceSchema' => 'nullable|array'
        ]);

        if ($project->endpoints()->where('name', $request->name)->exists()) {
            return response()->json(['error' => 'Endpoint name already exists in this project.'], 400);
        }

        list($endpoints, $resourceSchema) = $this->formatEndpointsAndSchema(
            $request->name,
            $request->endpoints,
            $request->resourceSchema
        );

        $endpoint = $project->endpoints()->create([
            'name' => $request->name,
            'generator' => $request->generator,
            'endpoints_config' => $endpoints,
            'resource_schema' => $resourceSchema
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
            'endpoints' => 'sometimes|nullable|array',
            'resourceSchema' => 'sometimes|nullable|array'
        ]);

        if ($request->has('name') && $request->name !== $endpoint->name) {
            if ($endpoint->project->endpoints()->where('name', $request->name)->exists()) {
                return response()->json(['error' => 'Endpoint name already exists in this project.'], 400);
            }
        }

        $schemaChanged = false;

        $newName = $request->has('name') ? $request->name : $endpoint->name;
        $newEndpoints = $request->has('endpoints') ? $request->endpoints : $endpoint->endpoints_config;
        $newSchema = $request->has('resourceSchema') ? $request->resourceSchema : $endpoint->resource_schema;

        list($formattedEndpoints, $formattedSchema) = $this->formatEndpointsAndSchema(
            $newName,
            $newEndpoints,
            $newSchema
        );

        if ($request->has('name')) $endpoint->name = $request->name;
        if ($request->has('generator')) $endpoint->generator = $request->generator;
        
        $endpoint->endpoints_config = $formattedEndpoints;
        
        // Check if schema actually changed beyond just adding ID
        if ($request->has('resourceSchema')) {
            $endpoint->resource_schema = $formattedSchema;
            $schemaChanged = true;
        } else {
            // Still update it in case ID was missing, but don't trigger full sync
            $endpoint->resource_schema = $formattedSchema;
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

    private function formatEndpointsAndSchema($name, $endpoints, $resourceSchema)
    {
        // 1. Enforce endpoints URLs
        if (!is_array($endpoints) || empty($endpoints)) {
            $endpoints = [
                ['method' => 'GET', 'url' => "/{$name}", 'enabled' => true, 'paginate' => false, 'per_page_default' => 10, 'response' => '$mockData'],
                ['method' => 'GET', 'url' => "/{$name}/:id", 'enabled' => true, 'response' => '$mockData'],
                ['method' => 'POST', 'url' => "/{$name}", 'enabled' => true, 'response' => '$mockData'],
                ['method' => 'PUT', 'url' => "/{$name}/:id", 'enabled' => true, 'response' => '$mockData'],
                ['method' => 'DELETE', 'url' => "/{$name}/:id", 'enabled' => true, 'response' => '$mockData']
            ];
        } else {
            foreach ($endpoints as &$ep) {
                $method = strtoupper($ep['method'] ?? 'GET');
                $hasId = str_contains($ep['url'] ?? '', '/:id');
                
                if ($method === 'GET' && $hasId) {
                    $ep['url'] = "/{$name}/:id";
                } elseif ($method === 'GET' || $method === 'POST') {
                    $ep['url'] = "/{$name}";
                } elseif (in_array($method, ['PUT', 'DELETE'])) {
                    $ep['url'] = "/{$name}/:id";
                }
            }
        }

        // 2. Enforce id in resourceSchema
        if (!is_array($resourceSchema)) {
            $resourceSchema = [];
        }
        
        $hasId = false;
        foreach ($resourceSchema as $field) {
            if (($field['name'] ?? '') === 'id') {
                $hasId = true;
                break;
            }
        }

        if (!$hasId) {
            array_unshift($resourceSchema, [
                'name' => 'id',
                'type' => 'Object.ID',
                'value' => ''
            ]);
        }

        return [$endpoints, $resourceSchema];
    }
}
