<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group 2. Gestão de Projetos
 *
 * APIs para gerenciar os projetos de Mocks.
 * @authenticated
 */
class ProjectController extends Controller
{
    /**
     * Listar Projetos
     *
     * Retorna todos os projetos do usuário logado.
     */
    public function index(Request $request)
    {
        return response()->json($request->user()->projects);
    }

    /**
     * Criar Projeto
     *
     * Cria um novo projeto e gera seu slug.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255'
        ]);

        // Check if slug is unique for this user
        if (Project::where('user_id', $request->user()->id)->where('slug', $request->slug)->exists()) {
            return response()->json(['error' => 'Project slug already exists for this user.'], 400);
        }

        $project = $request->user()->projects()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug)
        ]);

        return response()->json($project, 201);
    }

    /**
     * Obter Projeto
     *
     * Retorna os detalhes de um projeto específico.
     */
    public function show(Request $request, $id)
    {
        $project = $request->user()->projects()->find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        
        return response()->json($project);
    }

    /**
     * Atualizar Projeto
     *
     * Atualiza os dados de um projeto existente.
     */
    public function update(Request $request, $id)
    {
        $project = $request->user()->projects()->find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);

        $this->validate($request, [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255'
        ]);

        if ($request->has('slug') && $request->slug !== $project->slug) {
            if (Project::where('user_id', $request->user()->id)->where('slug', $request->slug)->exists()) {
                return response()->json(['error' => 'Project slug already exists for this user.'], 400);
            }
            $project->slug = Str::slug($request->slug);
        }

        if ($request->has('name')) $project->name = $request->name;

        $project->save();
        return response()->json($project);
    }

    /**
     * Deletar Projeto
     *
     * Remove um projeto e todos os seus endpoints e mocks vinculados.
     */
    public function destroy(Request $request, $id)
    {
        $project = $request->user()->projects()->find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);

        $project->delete();
        return response()->json(null, 204);
    }
}
