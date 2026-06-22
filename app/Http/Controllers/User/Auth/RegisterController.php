<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Services\ReferralAttributionService;

class RegisterController extends Controller
{
    public function __construct(
        private ReferralAttributionService $referralAttributionService
    ) {
    }

    /**
     * Show the registration form (fallback page)
     */
    public function showRegistrationForm()
    {
        $pageTitle = 'Cadastro';
        return view('frontend.auth.register', compact('pageTitle'));
    }

    /**
     * Handle registration via AJAX
     */
    public function register(Request $request)
    {
        $payload = $request->all();
        $payload['cpf'] = $this->normalizeCpf($payload['cpf'] ?? null);

        $validator = Validator::make($payload, [
            'password' => ['required', 'confirmed', Password::min(6)],
            'cpf' => 'required|string|size:11|unique:users,cpf',
            'birthdate' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
        ], [
            'password.required' => 'Crie uma senha',
            'password.confirmed' => 'As senhas não conferem',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'cpf.required' => 'Informe seu CPF',
            'cpf.size' => 'CPF deve ter 11 dígitos',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'birthdate.date' => 'Data de nascimento inválida',
            'birthdate.before_or_equal' => 'É necessário ter 18 anos ou mais para se cadastrar',
        ]);

        $validator->after(function ($validator) use ($payload) {
            if (!$this->isValidCpf($payload['cpf'] ?? '')) {
                $validator->errors()->add('cpf', 'Informe um CPF válido');
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

        // Create user
        $cpf = (string) $payload['cpf'];
        $user = new User();
        $user->username = $this->generateUsernameFromCpf($cpf);
        $user->email = $this->generateInternalEmail($cpf);
        $user->password = Hash::make($request->password);
        $user->cpf = $cpf;
        $user->birthdate = $request->filled('birthdate') ? $request->birthdate : null;
        $user->status = 1;
        $user->ev = 1; // Cadastro sem email real
        $user->sv = 1; // SMS verified (disabled by default)
        $user->ts = 0; // Two-factor disabled
        $user->tv = 1; // Two-factor verified
        
        // Handle OLD referral system (ref_by)
        $ref = $request->ref ?? session('reference');
        if ($ref) {
            $refUser = User::where('username', $ref)->first();
            if ($refUser) {
                $user->ref_by = $refUser->id;
            }
        }

        // Handle NEW AFFILIATE referral system (referred_by_id)
        // Tentar pegar código de várias fontes
        $referralCode = $request->session()->get('referral_code') 
            ?? $request->cookie('referral_code')
            ?? null;
            
        $affiliateId = $request->session()->get('referral_affiliate_id')
            ?? $request->cookie('referral_affiliate_id')
            ?? null;
        $referralToken = $request->session()->get('referral_token')
            ?? $request->cookie('referral_token')
            ?? null;
        
        \Log::info('🔍 RegisterController: Verificando código de referral', [
            'referral_code' => $referralCode,
            'affiliate_id_cookie' => $affiliateId,
            'session_code' => $request->session()->get('referral_code'),
            'cookie_code' => $request->cookie('referral_code'),
            'session_affiliate' => $request->session()->get('referral_affiliate_id'),
            'cookie_affiliate' => $request->cookie('referral_affiliate_id'),
            'has_referral_token' => !empty($referralToken),
        ]);

        $affiliate = $this->referralAttributionService->resolveAffiliate(
            $referralToken,
            $referralCode,
            $affiliateId ? (int) $affiliateId : null
        );

        if ($affiliate) {
            $affiliateId = $affiliate->id;
            \Log::info('✅ Afiliado resolvido no cadastro web', [
                'affiliate_id' => $affiliateId,
                'code' => $affiliate->referral_code,
                'via_token' => !empty($referralToken),
            ]);
        } else {
            \Log::info('❌ Nenhum afiliado válido resolvido no cadastro web');
        }
        
        // Se encontrou afiliado, vincular usuário
        if ($affiliate) {
            $user->referred_by_id = $affiliate->user_id;
        }
        
        $user->save();

        // Criar registro na tabela referral_users
        if ($affiliate && $affiliateId) {
            \App\Models\ReferralUser::create([
                'affiliate_id' => $affiliateId,
                'referred_user_id' => $user->id,
                'status' => 'active',
            ]);
            
            // Incrementar contador de referrals do afiliado
            $affiliate->increment('total_referrals');
            $affiliate->increment('active_referrals');
            
            \Log::info('🎯 Novo usuário vinculado a afiliado', [
                'user_id' => $user->id,
                'username' => $user->username,
                'affiliate_id' => $affiliateId,
                'affiliate_user_id' => $affiliate->user_id,
            ]);
        } else {
            \Log::info('ℹ️ Usuário registrado sem afiliado', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);
        }

        // Log the user in
        Auth::login($user);

        // Log the login
        $this->logUserLogin($user, $request);

        if ($user->hasRealEmail()) {
            notify($user, 'WELCOME', [
                'fullname' => $user->username,
                'url' => route('home'),
            ], ['email']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Conta criada com sucesso! Bem-vindo ao Rei do Rodeio!',
                'redirect_url' => route('home'),
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => null,
                    'has_real_email' => method_exists($user, 'hasRealEmail') ? $user->hasRealEmail() : false,
                ],
                'profile_incomplete' => false
            ]);
        }

        return redirect()->route('home');
    }

    /**
     * Check if username/email is available (AJAX)
     */
    public function checkUser(Request $request)
    {
        $field = $request->input('field', 'cpf');
        $value = $request->input('value');

        if (!$value) {
            return response()->json(['available' => false, 'message' => 'Valor não informado']);
        }

        if (!in_array($field, ['username', 'email', 'cpf'], true)) {
            return response()->json(['available' => false, 'message' => 'Campo inválido']);
        }

        // Sanitize CPF: remove formatting
        if ($field === 'cpf') {
            $value = preg_replace('/\D/', '', $value);
            if (strlen($value) !== 11) {
                return response()->json(['available' => false, 'message' => 'CPF inválido']);
            }
        }

        $exists = User::where($field, $value)->exists();

        $labels = ['username' => 'Nome de usuário', 'email' => 'Email', 'cpf' => 'CPF'];
        $label = $labels[$field] ?? ucfirst($field);

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? $label . ' já está em uso' : $label . ' disponível'
        ]);
    }

    private function generateUsernameFromCpf(string $cpf): string
    {
        $suffix = substr($cpf, -6);
        $base = 'rr_' . $suffix;
        $candidate = $base;

        while (User::where('username', $candidate)->exists()) {
            $candidate = Str::limit($base . '_' . random_int(1000, 9999), 40, '');
        }

        return $candidate;
    }

    private function generateInternalEmail(string $cpf): string
    {
        $base = 'cpf' . $cpf . '@cadastro.local';
        $candidate = $base;

        while (User::where('email', $candidate)->exists()) {
            $candidate = 'cpf' . $cpf . '+' . random_int(1000, 9999) . '@cadastro.local';
        }

        return strtolower($candidate);
    }

    private function normalizeCpf(mixed $cpf): string
    {
        return preg_replace('/\D+/', '', (string) $cpf);
    }

    private function isValidCpf(?string $cpf): bool
    {
        $cpf = $this->normalizeCpf($cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log user login
     */
    protected function logUserLogin(User $user, Request $request)
    {
        $ip = $request->ip();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

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
