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
    /**
     * Show the login form (fallback page)
     */
    public function showLoginForm()
    {
        $pageTitle = 'Login';
        return view('frontend.auth.login', compact('pageTitle'));
    }

    /**
     * Handle login via AJAX
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'required|string|min:6',
        ], [
            'cpf.required' => 'Informe seu CPF',
            'password.required' => 'Informe sua senha',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
        ]);

        $validator->after(function ($validator) use ($request) {
            $rawCpf = $request->input('cpf', $request->input('username', ''));
            $cpf = preg_replace('/\D+/', '', (string) $rawCpf);

            if ($cpf === '') {
                $validator->errors()->add('cpf', 'Informe seu CPF');
                return;
            }

            if (strlen($cpf) !== 11) {
                $validator->errors()->add('cpf', 'Informe um CPF válido com 11 dígitos');
            }
        });

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->all()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf', $request->input('username', '')));
        $password = $request->input('password');

        $user = User::where('cpf', $cpf)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['CPF ou senha incorretos']
                ], 401);
            }
            return back()->withErrors(['cpf' => 'CPF ou senha incorretos'])->withInput();
        }

        // Check if user is banned
        if ($user->status == 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Sua conta foi suspensa. Entre em contato com o suporte.']
                ], 403);
            }
            return back()->withErrors(['cpf' => 'Sua conta foi suspensa.'])->withInput();
        }

        Auth::login($user, $request->filled('remember'));

        if (Schema::hasColumn($user->getTable(), 'current_session_id')) {
            $user->forceFill([
                'current_session_id' => session()->getId(),
            ])->save();
        }

        $this->logUserLogin($user, $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso!',
                'redirect_url' => route('home'),
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->image ? asset('assets/images/user/profile/' . $user->image) : null,
                    'has_real_email' => method_exists($user, 'hasRealEmail') ? $user->hasRealEmail() : false,
                ]
            ]);
        }

        return redirect()->intended($request->input('redirect') ?: route('home'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso!'
            ]);
        }

        return redirect()->route('home');
    }

    /**
     * Log user login
     */
    protected function logUserLogin(User $user, Request $request)
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
        $userLogin->browser = @$osBrowser['browser'] ?? null;
        $userLogin->os = @$osBrowser['os_platform'] ?? null;
        $userLogin->save();
    }
}
