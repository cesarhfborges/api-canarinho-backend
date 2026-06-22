<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @group 1. Autenticação Admin
 *
 * APIs para gerenciar login e logout no painel administrativo.
 */
class AdminAuthController extends Controller
{
    /**
     * Login
     *
     * Realiza a autenticação e retorna um Cookie HttpOnly.
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'Your account is inactive. Contact the administrator.'], 403);
        }

        $plainTextToken = Str::random(40);
        $hashedToken = hash('sha256', $plainTextToken);

        PersonalAccessToken::create([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'admin_session',
            'token' => $hashedToken,
            'expires_at' => \Carbon\Carbon::now()->addDays(30)
        ]);

        $cookie = new \Symfony\Component\HttpFoundation\Cookie('admin_token', $plainTextToken, \Carbon\Carbon::now()->addDays(30), '/', null, false, true);

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'user' => $user
        ])->withCookie($cookie);
    }

    /**
     * Obter Usuário Logado
     *
     * Retorna os dados do usuário autenticado no painel.
     * @authenticated
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout
     *
     * Invalida o token de sessão e remove o cookie.
     * @authenticated
     */
    public function logout(Request $request)
    {
        $token = $request->cookie('admin_token') ?? $request->bearerToken();
        if ($token) {
            PersonalAccessToken::where('token', hash('sha256', $token))->delete();
        }

        $cookie = new \Symfony\Component\HttpFoundation\Cookie('admin_token', null, \Carbon\Carbon::now()->subYears(5), '/', null, false, true);

        return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
    }

    /**
     * Auto-Cadastro (Registro)
     *
     * Cria um novo usuário comum, caso a configuração ALLOW_REGISTRATION esteja ativa.
     */
    public function register(Request $request)
    {
        if (env('ALLOW_REGISTRATION', false) != true && env('ALLOW_REGISTRATION', 'false') !== 'true') {
            return response()->json(['error' => 'Registration is currently disabled.'], 403);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
            'is_active' => true
        ]);

        return response()->json($user, 201);
    }

    /**
     * Atualizar Perfil
     *
     * Atualiza os dados básicos do próprio usuário logado.
     * @authenticated
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;

        $user->save();

        return response()->json($user);
    }

    /**
     * Atualizar Senha do Perfil
     *
     * Atualiza a senha do próprio usuário logado.
     * @authenticated
     */
    public function updateMyPassword(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
