<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group 4. Gestão de Usuários
 *
 * APIs para gerenciar os usuários do sistema. Apenas Administradores podem acessar.
 * @authenticated
 */
class UserController extends Controller
{
    public function __construct()
    {
        // Add middleware check directly in the constructor or we can check inside methods.
        // Lumen usually uses closures in routes, but we can do it here for safety.
    }

    private function authorizeAdmin(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Acesso restrito para administradores.');
        }
    }

    /**
     * Listar Usuários
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $query = User::query();

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

        return response()->json($query->paginate($request->get('per_page', 10)));
    }

    /**
     * Obter Usuário
     */
    public function show(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::find($id);
        if (!$user) return response()->json(['error' => 'Not found'], 404);

        return response()->json($user);
    }

    /**
     * Criar Usuário
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'is_admin' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->is_admin ?? false,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json($user, 201);
    }

    /**
     * Atualizar Usuário
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::find($id);
        if (!$user) return response()->json(['error' => 'Not found'], 404);

        $this->validate($request, [
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'is_admin' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('username')) $user->username = $request->username;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('is_admin')) $user->is_admin = $request->is_admin;
        if ($request->has('is_active')) $user->is_active = $request->is_active;

        $user->save();

        return response()->json($user);
    }

    /**
     * Atualizar Senha de um Usuário
     */
    public function updatePassword(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::find($id);
        if (!$user) return response()->json(['error' => 'Not found'], 404);

        $this->validate($request, [
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Deletar Usuário
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeAdmin($request);

        $user = User::find($id);
        if (!$user) return response()->json(['error' => 'Not found'], 404);

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return response()->json(['error' => 'You cannot delete yourself.'], 400);
        }

        $user->delete();
        return response()->json(null, 204);
    }
}
