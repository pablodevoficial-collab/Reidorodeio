<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $pageTitle = 'Login';
        return view('frontend.auth.login', compact('pageTitle'));
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'required|string|min:6',
        ], [
            'password.required' => 'Informe sua senha',
            'password.min' => 'A senha deve ter no minimo 6 caracteres',
        ]);

        $validator->after(function ($validator) use ($request) {
            $raw = $request->input('cpf', $request->input('username', ''));
            $identifier = preg_replace('/\D+/', '', (string) $raw);

            if ($identifier === '') {
                $validator->errors()->add('cpf', 'Informe seu CPF ou WhatsApp');
                return;
            }

            if (!in_array(strlen($identifier), [11, 10, 12, 13, 14, 15], true)) {
                $validator->errors()->add('cpf', 'Informe um CPF ou WhatsApp valido');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $identifier = preg_replace('/\D+/', '', (string) $request->input('cpf', $request->input('username', '')));
        $password = (string) $request->input('password');

        $user = User::where('cpf', $identifier)
            ->orWhere('mobile', $identifier)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['CPF, WhatsApp ou senha incorretos'],
            ], 401);
        }

        if ((int) $user->status === 0) {
            return response()->json([
                'success' => false,
                'errors' => ['Sua conta foi suspensa. Entre em contato com o suporte.'],
            ], 403);
        }

        Auth::login($user, $request->filled('remember'));

        if (Schema::hasColumn($user->getTable(), 'current_session_id')) {
            $user->forceFill(['current_session_id' => session()->getId()])->save();
        }

        $this->logUserLogin($user, $request);

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'redirect_url' => route('arena'),
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'avatar' => $user->image ? asset('assets/images/user/profile/' . $user->image) : null,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso!',
        ]);
    }

    protected function logUserLogin(User $user, Request $request): void
    {
        $userLogin = new UserLogin();
        if (!Schema::hasTable($userLogin->getTable())) {
            return;
        }

        $ip = $request->ip();
        $exist = UserLogin::where('user_ip', $ip)->first();

        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country = $exist->country;
            $userLogin->country_code = $exist->country_code;
        } else {
            $info = getIpInfo();
            $userLogin->longitude = $info['lon'] ?? $info['long'] ?? null;
            $userLogin->latitude = $info['lat'] ?? null;
            $userLogin->city = $info['city'] ?? null;
            $userLogin->country = $info['country'] ?? null;
            $userLogin->country_code = $info['countryCode'] ?? $info['code'] ?? null;
        }

        $osBrowser = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = $osBrowser['browser'] ?? null;
        $userLogin->os = $osBrowser['os_platform'] ?? null;
        $userLogin->save();
    }
}
