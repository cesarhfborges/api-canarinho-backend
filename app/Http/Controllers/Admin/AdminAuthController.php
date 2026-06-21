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
}
