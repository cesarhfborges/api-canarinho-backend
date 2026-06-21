<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Endpoint;
use Illuminate\Http\Request;

/**
 * @group 6. API Mocks Dinâmicos
 *
 * O Endpoint Coringa responsável por responder aos Mocks configurados no painel.
 * Todas as requisições devem vir autenticadas pelo Header `X-Project-Token` ou Bearer Token.
 */
class DynamicApiController extends Controller
{
    public function handleRequest(Request $request, $username, $projectSlug, $path)
    {
        // 1. Authenticate Project
        $authProject = $request->attributes->get('auth_project');

        // 2. Resolve User & Project
        $user = User::where('username', $username)->first();
        if (!$user) return response()->json(['error' => 'User not found'], 404);

        $project = $user->projects()->where('slug', $projectSlug)->first();
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        if ($authProject->id !== $project->id) {
            return response()->json(['error' => 'Token does not match project.'], 403);
        }

        // 3. Resolve Endpoint Config via Regex Match (Nested Routes Support)
        $requestedMethod = strtoupper($request->method());
        $requestUrl = '/' . $path;
        
        $matchedEndpoint = null;
        $matchedConfig = null;
        $extractedParams = [];

        foreach ($project->endpoints as $ep) {
            $configs = $ep->endpoints_config ?? [];
            foreach ($configs as $config) {
                if (strtoupper($config['method']) !== $requestedMethod || empty($config['enabled'])) {
                    continue;
                }

                // Ex: /pagamentos/:idPagamento/usuarios/:id -> #^/pagamentos/([^/]+)/usuarios/([^/]+)$#
                $pattern = preg_replace('/:[a-zA-Z0-9_]+/', '([^/]+)', $config['url']);
                $pattern = '#^' . $pattern . '$#';
                
                if (preg_match($pattern, $requestUrl, $matches)) {
                    $matchedEndpoint = $ep;
                    $matchedConfig = $config;
                    array_shift($matches); // Remove o match completo da URL
                    $extractedParams = $matches;
                    break 2; // Sai dos dois loops
                }
            }
        }

        if (!$matchedEndpoint || !$matchedConfig) {
            return response()->json(['error' => 'Method or URL not configured for this project'], 405);
        }

        // 4. Determinar ID alvo
        // Se a rota cadastrada termina com parâmetro (ex: /algo/:id) e não é POST, assumimos que é operação de item único
        $isSingleItemRequest = preg_match('/:([a-zA-Z0-9_]+)$/', $matchedConfig['url']);
        $method = strtolower($request->method());
        
        $targetId = null;
        if ($isSingleItemRequest && $method !== 'post' && count($extractedParams) > 0) {
            $targetId = end($extractedParams); // O último parâmetro é o ID do recurso alvo
        }

        // 5. Check Dynamic Rules (Headers, Body, Query)
        foreach ($matchedEndpoint->rules as $rule) {
            $matched = false;
            $valueToCheck = null;

            if ($rule->condition_type === 'header') $valueToCheck = $request->header($rule->condition_key);
            elseif ($rule->condition_type === 'query') $valueToCheck = $request->query($rule->condition_key);
            elseif ($rule->condition_type === 'body') $valueToCheck = $request->input($rule->condition_key);

            if ($valueToCheck !== null) {
                switch ($rule->condition_operator) {
                    case 'equals': $matched = ($valueToCheck == $rule->condition_value); break;
                    case 'not_equals': $matched = ($valueToCheck != $rule->condition_value); break;
                    case 'contains': $matched = (strpos($valueToCheck, $rule->condition_value) !== false); break;
                }
            }

            if ($matched) {
                return response()->json($rule->response_body, $rule->response_status);
            }
        }

        // 6. Evaluate Response Type
        $responseConfig = $matchedConfig['response'] ?? null;
        
        if ($responseConfig !== '$mockData') {
            $parsed = json_decode((string)$responseConfig, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return response()->json($parsed, 200);
            }
            return response($responseConfig, 200);
        }

        // 7. Configurar a Query Base (Filtros e Ordenação via MySQL JSON)
        $query = $matchedEndpoint->mockData();
        
        $filterBy = $request->query('filterBy');
        $filterValue = $request->query('filter');
        if ($filterBy && $filterValue) {
            if ($filterBy === 'id') {
                $query->where('id', 'like', "%{$filterValue}%");
            } else {
                $query->where("json_data->{$filterBy}", 'like', "%{$filterValue}%");
            }
        }

        $sortBy = $request->query('sort_by');
        $sortOrder = $request->query('sort_order', 'asc');
        if ($sortBy) {
            if ($sortBy === 'id') {
                $query->orderBy('id', strtolower($sortOrder) === 'desc' ? 'desc' : 'asc');
            } else {
                $query->orderBy("json_data->{$sortBy}", strtolower($sortOrder) === 'desc' ? 'desc' : 'asc');
            }
        }

        // 8. Execute CRUD Operation on mock_data ($mockData)
        switch ($method) {
            case 'get':
                if ($targetId) {
                    $data = $query->find($targetId);
                    if (!$data) return response()->json(['error' => 'Mock record not found'], 404);
                    
                    $response = $data->json_data;
                    $response['id'] = $data->id;
                    return response()->json($response, 200);
                } else {
                    $shouldPaginate = !empty($matchedConfig['paginate']) && !$isSingleItemRequest;
                    
                    if ($shouldPaginate) {
                        $perPage = (int) $request->query('per_page', $matchedConfig['per_page_default'] ?? 15);
                        $paginator = $query->paginate($perPage);
                        $items = collect($paginator->items())->map(function($d) {
                            $item = $d->json_data;
                            $item['id'] = $d->id;
                            return $item;
                        });
                        return response()->json([
                            'data' => $items,
                            'current_page' => $paginator->currentPage(),
                            'last_page' => $paginator->lastPage(),
                            'per_page' => $paginator->perPage(),
                            'total' => $paginator->total()
                        ], 200);
                    } else {
                        $items = $query->get()->map(function($d) {
                            $item = $d->json_data;
                            $item['id'] = $d->id;
                            return $item;
                        });
                        return response()->json($items, 200);
                    }
                }

            case 'post':
                $data = $matchedEndpoint->mockData()->create(['json_data' => $request->all()]);
                $response = $data->json_data;
                $response['id'] = $data->id;
                return response()->json($response, 201);

            case 'put':
            case 'patch':
                if (!$targetId) return response()->json(['error' => 'ID required for update'], 400);
                $data = $matchedEndpoint->mockData()->find($targetId);
                if (!$data) return response()->json(['error' => 'Mock record not found'], 404);
                
                $newData = $method === 'patch' ? array_merge($data->json_data, $request->all()) : $request->all();
                
                $data->update(['json_data' => $newData]);
                $response = $data->json_data;
                $response['id'] = $data->id;
                return response()->json($response, 200);

            case 'delete':
                if (!$targetId) return response()->json(['error' => 'ID required for delete'], 400);
                $data = $matchedEndpoint->mockData()->find($targetId);
                if (!$data) return response()->json(['error' => 'Mock record not found'], 404);
                
                $data->delete();
                return response()->json(null, 204);
        }

        return response()->json(['error' => 'Unknown method'], 405);
    }
}
