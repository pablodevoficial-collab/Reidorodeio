<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function showResetForm(Request $request, $token = null)
    {
        $pageTitle = 'Redefinir Senha';
        return view('frontend.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email, 'pageTitle' => $pageTitle]
        );
    }
    
    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }

    public function resetWithCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required|confirmed|min:6',
        ], [
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Usuário não encontrado']], 404);
        }

        if ($user->ver_code != $request->code) {
            return response()->json(['success' => false, 'errors' => ['Código inválido ou expirado']], 422);
        }

        // Reset Password
        $user->password = \Hash::make($request->password);
        $user->ver_code = null; // Clear code
        $user->save();

        return response()->json(['success' => true, 'message' => 'Senha redefinida com sucesso!']);
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'password.confirmed' => 'A confirmação de senha não confere',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email deve ser válido',
        ];
    }
}
