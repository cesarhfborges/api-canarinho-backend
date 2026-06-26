<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Jobs\SendBrevoEmailJob;
use App\Mail\ResetPassword;
use App\Models\PasswordReset;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        if (\App\Models\Config::getValue('allow_register', false) !== true) {
            return response()->json([
                'success' => false,
                'error' => 'Registration is currently disabled.'
            ], 403);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users|not_in:admin,api,mock,system,health',
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

        return response()->json([
            'success' => true,
            'message' => 'Usuário registrado com sucesso.',
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email
            ]
        ], 201);
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
            'theme_color_scheme' => 'sometimes|nullable|string|in:light,dark,auto',
        ]);

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('theme_color_scheme')) $user->theme_color_scheme = $request->theme_color_scheme;

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

    /**
     * Solicitar Redefinição de Senha
     */
    public function resetPasswordRequest(Request $request)
    {
        $this->validate($request, [
            'username_or_email' => 'required|string'
        ]);

        $login = $request->input('username_or_email');
        $user = User::where('email', $login)->orWhere('username', $login)->first();

        if ($user) {
            $token = bin2hex(random_bytes(69));

            // Soft-delete any existing reset tokens for this user
            PasswordReset::where('user_id', $user->id)->delete();

            PasswordReset::create([
                'user_id' => $user->id,
                'token' => $token,
                'used' => false,
                'expires_at' => Carbon::now()->addHours(2)
            ]);

            $email = new ResetPassword($user, $token);
            dispatch(new SendBrevoEmailJob($email));
            
            return response()->json(['message' => 'Se o usuário existir, um e-mail de recuperação será enviado.'], 200);
        }

        return response()->json(['message' => 'Se o usuário existir, um e-mail de recuperação será enviado.'], 200);
    }

    /**
     * Alterar Senha com Token
     */
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $resetRecord = PasswordReset::where('token', $request->token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Token inválido ou expirado.'], 400);
        }

        $user = $resetRecord->user;
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $resetRecord->used = true;
        $resetRecord->save();
        $resetRecord->delete();

        return response()->json(['message' => 'Senha alterada com sucesso.'], 200);
    }
}
