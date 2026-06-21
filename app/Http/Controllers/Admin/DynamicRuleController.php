<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\DynamicRule;
use App\Models\Endpoint;
use Illuminate\Http\Request;

/**
 * @group 5. Regras Dinâmicas (Mocks)
 *
 * APIs para configurar regras de testes dinâmicos nos endpoints. 
 * Respondem com status e payload customizados caso as condições batam.
 * @authenticated
 */
class DynamicRuleController extends Controller
{
    /**
     * Listar Regras
     *
     * Retorna as regras cadastradas para o endpoint informado.
     */
    public function index(Request $request, $endpointId)
    {
        $endpoint = Endpoint::whereHas('project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($endpointId);

        if (!$endpoint) return response()->json(['error' => 'Endpoint not found'], 404);

        return response()->json($endpoint->rules);
    }

    /**
     * Criar Regra
     *
     * Adiciona uma nova condição (Header, Query ou Body) que altera a resposta do endpoint.
     */
    public function store(Request $request, $endpointId)
    {
        $endpoint = Endpoint::whereHas('project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($endpointId);

        if (!$endpoint) return response()->json(['error' => 'Endpoint not found'], 404);

        $this->validate($request, [
            'condition_type' => 'required|in:header,query,body',
            'condition_key' => 'required|string',
            'condition_operator' => 'required|in:equals,not_equals,contains',
            'condition_value' => 'required|string',
            'response_status' => 'required|integer',
            'response_body' => 'nullable|array'
        ]);

        $rule = $endpoint->rules()->create($request->all());

        return response()->json($rule, 201);
    }

    /**
     * Atualizar Regra
     *
     * Altera as propriedades de uma regra dinâmica existente.
     */
    public function update(Request $request, $id)
    {
        $rule = DynamicRule::whereHas('endpoint.project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($id);

        if (!$rule) return response()->json(['error' => 'Not found'], 404);

        $this->validate($request, [
            'condition_type' => 'sometimes|required|in:header,query,body',
            'condition_key' => 'sometimes|required|string',
            'condition_operator' => 'sometimes|required|in:equals,not_equals,contains',
            'condition_value' => 'sometimes|required|string',
            'response_status' => 'sometimes|required|integer',
            'response_body' => 'nullable|array'
        ]);

        $rule->update($request->all());

        return response()->json($rule);
    }

    /**
     * Deletar Regra
     *
     * Remove permanentemente a regra de avaliação.
     */
    public function destroy(Request $request, $id)
    {
        $rule = DynamicRule::whereHas('endpoint.project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($id);

        if (!$rule) return response()->json(['error' => 'Not found'], 404);

        $rule->delete();
        return response()->json(null, 204);
    }
}
