<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogin;
use App\Services\ReferralAttributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __construct(
        private ReferralAttributionService $referralAttributionService
    ) {
    }

    public function showRegistrationForm()
    {
        $pageTitle = 'Cadastro';
        return view('frontend.auth.register', compact('pageTitle'));
    }

    public function register(Request $request)
    {
        $payload = $request->all();
        $payload['mobile'] = $this->normalizeMobile($payload['mobile'] ?? $payload['whatsapp'] ?? null);
        $payload['cpf'] = $this->normalizeCpf($payload['cpf'] ?? null);

        $validator = Validator::make($payload, [
            'password' => ['required', 'confirmed', Password::min(6)],
            'mobile' => 'required|string|min:10|max:15|unique:users,mobile',
            'cpf' => 'nullable|string|size:11|unique:users,cpf',
            'birthdate' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
        ], [
            'password.required' => 'Crie uma senha',
            'password.confirmed' => 'As senhas nao conferem',
            'password.min' => 'A senha deve ter no minimo 6 caracteres',
            'mobile.required' => 'Informe seu WhatsApp',
            'mobile.unique' => 'Este WhatsApp ja esta cadastrado',
            'cpf.size' => 'CPF deve ter 11 digitos',
            'cpf.unique' => 'Este CPF ja esta cadastrado',
            'birthdate.date' => 'Data de nascimento invalida',
            'birthdate.before_or_equal' => 'E necessario ter 18 anos ou mais para se cadastrar',
        ]);

        $validator->after(function ($validator) use ($payload) {
            if (!preg_match('/^\d{10,15}$/', (string) ($payload['mobile'] ?? ''))) {
                $validator->errors()->add('mobile', 'Informe um WhatsApp valido');
            }

            if (($payload['cpf'] ?? '') !== '' && !$this->isValidCpf($payload['cpf'])) {
                $validator->errors()->add('cpf', 'Informe um CPF valido');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $mobile = (string) $payload['mobile'];
        $cpf = (string) ($payload['cpf'] ?? '');

        $user = new User();
        $user->username = $this->generateUsernameFromSeed($mobile !== '' ? $mobile : $cpf);
        $user->email = $this->generateInternalEmail($mobile !== '' ? $mobile : $cpf);
        $user->password = Hash::make((string) $request->input('password'));
        $user->mobile = $mobile;
        $user->cpf = $cpf !== '' ? $cpf : null;
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->birthdate = $request->filled('birthdate') ? $request->input('birthdate') : null;
        $user->status = 1;
        $user->ev = 1;
        $user->sv = 1;
        $user->ts = 0;
        $user->tv = 1;

        $ref = $request->input('ref') ?? session('reference');
        if ($ref) {
            $refUser = User::where('username', $ref)->first();
            if ($refUser) {
                $user->ref_by = $refUser->id;
            }
        }

        $referralCode = $request->session()->get('referral_code') ?? $request->cookie('referral_code');
        $affiliateId = $request->session()->get('referral_affiliate_id') ?? $request->cookie('referral_affiliate_id');
        $referralToken = $request->session()->get('referral_token') ?? $request->cookie('referral_token');

        $affiliate = $this->referralAttributionService->resolveAffiliate(
            $referralToken,
            $referralCode,
            $affiliateId ? (int) $affiliateId : null
        );

        if ($affiliate) {
            $user->referred_by_id = $affiliate->user_id;
        }

        $user->save();

        if ($affiliate && $affiliateId) {
            \App\Models\ReferralUser::create([
                'affiliate_id' => $affiliateId,
                'referred_user_id' => $user->id,
                'status' => 'active',
            ]);

            $affiliate->increment('total_referrals');
            $affiliate->increment('active_referrals');
        }

        Auth::login($user);
        $this->logUserLogin($user, $request);

        return response()->json([
            'success' => true,
            'message' => 'Conta criada com sucesso!',
            'redirect_url' => route('arena'),
            'profile_incomplete' => !$user->isProfileComplete(),
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'mobile' => $user->mobile,
            ],
        ]);
    }

    public function checkUser(Request $request)
    {
        $field = $request->input('field', 'cpf');
        $value = (string) $request->input('value', '');

        if (!in_array($field, ['username', 'email', 'cpf', 'mobile'], true) || $value === '') {
            return response()->json(['available' => false, 'message' => 'Campo invalido']);
        }

        if ($field === 'cpf') {
            $value = $this->normalizeCpf($value);
            if (strlen($value) !== 11 || !$this->isValidCpf($value)) {
                return response()->json(['available' => false, 'message' => 'CPF invalido']);
            }
        }

        if ($field === 'mobile') {
            $value = $this->normalizeMobile($value);
            if (!preg_match('/^\d{10,15}$/', $value)) {
                return response()->json(['available' => false, 'message' => 'WhatsApp invalido']);
            }
        }

        $exists = User::where($field, $value)->exists();
        $labels = ['username' => 'Nome de usuario', 'email' => 'Email', 'cpf' => 'CPF', 'mobile' => 'WhatsApp'];
        $label = $labels[$field] ?? ucfirst($field);

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? $label . ' ja esta em uso' : $label . ' disponivel',
        ]);
    }

    private function generateUsernameFromSeed(string $seed): string
    {
        $suffix = substr($seed, -6);
        $base = 'rr_' . $suffix;
        $candidate = $base;

        while (User::where('username', $candidate)->exists()) {
            $candidate = Str::limit($base . '_' . random_int(1000, 9999), 40, '');
        }

        return $candidate;
    }

    private function generateInternalEmail(string $seed): string
    {
        $base = 'cad' . $seed . '@cadastro.local';
        $candidate = $base;

        while (User::where('email', $candidate)->exists()) {
            $candidate = 'cad' . $seed . '+' . random_int(1000, 9999) . '@cadastro.local';
        }

        return strtolower($candidate);
    }

    private function normalizeCpf(mixed $cpf): string
    {
        return preg_replace('/\D+/', '', (string) $cpf);
    }

    private function normalizeMobile(mixed $mobile): string
    {
        return preg_replace('/\D+/', '', (string) $mobile);
    }

    private function isValidCpf(?string $cpf): bool
    {
        $cpf = $this->normalizeCpf($cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
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

    protected function logUserLogin(User $user, Request $request): void
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
        $userLogin->browser = $osBrowser['browser'] ?? null;
        $userLogin->os = $osBrowser['os_platform'] ?? null;
        $userLogin->save();
    }
}
