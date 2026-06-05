<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        $pageTitle = 'Esqueci minha senha';
        return view('frontend.auth.passwords.email', compact('pageTitle'));
    }

    /**
     * Send a reset code (OTP) to the given user.
     * Overrides the default link behavior for our custom modal flow.
     */
    public function sendResetCodeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpf' => ['required', 'string'],
        ], [
            'cpf.required' => 'Informe seu CPF',
        ]);

        $validator->after(function ($validator) use ($request) {
            $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf'));

            if ($cpf === '') {
                $validator->errors()->add('cpf', 'Informe seu CPF');
                return;
            }

            if (strlen($cpf) !== 11) {
                $validator->errors()->add('cpf', 'Informe um CPF válido com 11 dígitos');
            }
        });

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->all()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf'));
        $user = User::query()->where('cpf', $cpf)->first();

        if (!$user) {
             return response()->json(['success' => false, 'errors' => ['Nenhuma conta foi encontrada com esse CPF.']], 422);
        }

        if (!method_exists($user, 'hasRealEmail') || !$user->hasRealEmail()) {
            $whatsAppUrl = 'https://wa.me/5547997953323?text=' . urlencode('Olá! Preciso de ajuda para recuperar minha senha no site Rei do Rodeio.');

            return response()->json([
                'success' => false,
                'errors' => [
                    'Esse CPF ainda não tem e-mail cadastrado para recuperação. Chame no <a href="' . $whatsAppUrl . '" target="_blank" rel="noopener">WhatsApp do suporte</a>.'
                ],
            ], 422);
        }

        // Generate 6 digit code
        $code = verificationCode(6);
        $user->ver_code = $code;
        $user->ver_code_send_at = now();
        $user->save();

        // Send Email
        notify($user, 'PASSWORD_RESET', [
            'code' => $code
        ], ['email']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Código enviado para o e-mail vinculado ao seu CPF.',
                'email' => $user->email,
            ]);
        }
        return back()->with('status', 'Código enviado!');
    }
    
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Usuário não encontrado']], 404);
        }

        if ($user->ver_code != $request->code) {
            return response()->json(['success' => false, 'errors' => ['Código inválido']], 422);
        }

        // Code valid
        return response()->json(['success' => true, 'message' => 'Código verificado!']);
    }

    // Keep original method for fallback/compatibility if needed, or route it to code
    public function sendResetLinkEmail(Request $request)
    {
        return $this->sendResetCodeEmail($request);
    }
}
