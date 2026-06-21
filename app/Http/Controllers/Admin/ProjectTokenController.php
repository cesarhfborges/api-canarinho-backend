<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ProjectToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group 4. Gestão de Tokens (Projetos)
 *
 * APIs para emitir e revogar tokens de acesso à Mock API de um projeto.
 * @authenticated
 */
class ProjectTokenController extends Controller
{
    /**
     * Listar Tokens do Projeto
     *
     * Retorna todos os tokens ativos para consumo da Mock API deste projeto.
     */
    public function index(Request $request, $projectId)
    {
        $project = $request->user()->projects()->find($projectId);
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        return response()->json($project->tokens);
    }

    /**
     * Emitir Novo Token
     *
     * Gera um novo token para acesso à Mock API Dinâmica.
     */
    public function store(Request $request, $projectId)
    {
        $project = $request->user()->projects()->find($projectId);
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        $this->validate($request, [
            'name' => 'required|string'
        ]);

        $token = $project->tokens()->create([
            'name' => $request->name,
            'token' => Str::random(60)
        ]);

        return response()->json($token, 201);
    }

    /**
     * Revogar Token
     *
     * Remove um token, impedindo novos acessos com ele.
     */
    public function destroy(Request $request, $id)
    {
        $token = ProjectToken::whereHas('project', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->find($id);

        if (!$token) return response()->json(['error' => 'Not found'], 404);

        $token->delete();
        return response()->json(null, 204);
    }
}
