<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\CompetitorRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompetitorRegistrationRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $rules = [
            'firstname' => ['required', 'string', 'max:60'],
            'lastname' => ['required', 'string', 'max:60'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($authUser?->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($authUser?->id),
            ],
            'mobile' => ['required', 'string', 'max:30'],
            'cpf' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'cpf')->ignore($authUser?->id),
            ],
            'birthdate' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'password' => [$authUser ? 'nullable' : 'required', 'confirmed', 'min:6'],
            'biografia' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        $validator = Validator::make($request->all(), $rules, [
            'firstname.required' => 'Informe seu nome.',
            'lastname.required' => 'Informe seu sobrenome.',
            'username.required' => 'Informe um username.',
            'username.unique' => 'Esse username já está em uso.',
            'email.required' => 'Informe seu email.',
            'email.unique' => 'Esse email já está em uso.',
            'mobile.required' => 'Informe seu WhatsApp.',
            'cpf.required' => 'Informe seu CPF.',
            'cpf.unique' => 'Esse CPF já está em uso.',
            'birthdate.required' => 'Informe sua data de nascimento.',
            'birthdate.before_or_equal' => 'É necessário ter 18 anos ou mais.',
            'password.required' => 'Crie uma senha de acesso.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf'));
            $mobile = preg_replace('/\D+/', '', (string) $request->input('mobile'));

            if (strlen($cpf) !== 11) {
                $validator->errors()->add('cpf', 'O CPF deve ter 11 dígitos.');
            }

            if ($mobile === '' || !preg_match('/^\d{8,15}$/', $mobile)) {
                $validator->errors()->add('mobile', 'Informe um WhatsApp válido.');
            }
        });

        $validated = $validator->validate();

        $user = $authUser ?? new User();
        if ($user->claimedCompetitor()->exists()) {
            return response()->json([
                'success' => false,
                'errors' => ['Sua conta já está vinculada a um competidor.'],
            ], 422);
        }

        $existingRequest = CompetitorRegistrationRequest::query()
            ->where('user_id', $user->id ?: 0)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'errors' => ['Você já possui uma solicitação pendente de análise.'],
            ], 422);
        }

        $mobile = preg_replace('/\D+/', '', (string) $validated['mobile']);
        $cpf = preg_replace('/\D+/', '', (string) $validated['cpf']);

        $user->firstname = $validated['firstname'];
        $user->lastname = $validated['lastname'];
        $user->username = $validated['username'];
        $user->email = strtolower(trim((string) $validated['email']));
        $user->mobile = $mobile;
        $user->cpf = $cpf;
        $user->birthdate = $validated['birthdate'];
        $user->status = $user->status ?? 1;
        $user->ev = $user->ev ?? 0;
        $user->sv = $user->sv ?? 1;
        $user->ts = $user->ts ?? 0;
        $user->tv = $user->tv ?? 1;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('assets/images/user/profile');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            if ($user->image && file_exists($destinationPath . '/' . $user->image)) {
                @unlink($destinationPath . '/' . $user->image);
            }
            $file->move($destinationPath, $filename);
            $user->image = $filename;
        }

        $user->save();

        $pendingRequest = CompetitorRegistrationRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return response()->json([
                'success' => false,
                'errors' => ['Já existe uma solicitação pendente para esta conta.'],
            ], 422);
        }

        CompetitorRegistrationRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'biografia' => $validated['biografia'] ?? null,
        ]);

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'Nova solicitação de cadastro de competidor';
        $adminNotification->click_url = route('admin.competitors.requests.index');
        $adminNotification->save();

        if (!$authUser) {
            Auth::login($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitação enviada com sucesso. Vamos analisar seu perfil de competidor no painel administrativo.',
        ]);
    }
}
