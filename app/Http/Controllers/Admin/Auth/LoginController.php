<?php
namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect admins after login.
     *
     * @var string
     */
    public $redirectTo = 'admin/rodeios';

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm() {
        $pageTitle = "Admin Login";
        return view('admin.auth.login', compact('pageTitle'));
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard() {
        return auth()->guard('admin');
    }

    public function username() {
        return 'username';
    }

    public function login(Request $request) {

        $this->validateLogin($request);

        // Evitar regeneração do token antes da validação do CSRF em ambientes locais
        // $request->session()->regenerateToken();

        // During local diagnostics, bypass captcha to allow automated login
        if (!\verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

    // Removed third-party license data call

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     * Fallback: aceita hashes não-bcrypt (ex.: Argon2) usando password_verify e rehash quando necessário.
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $remember = (bool) $request->boolean('remember');

        try {
            return $this->guard()->attempt($credentials, $remember);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Bcrypt')) {
                // Fallback manual para hashes legados
                $usernameField = $this->username();
                $usernameValue = $credentials[$usernameField] ?? null;
                if (!$usernameValue) {
                    return false;
                }

                $user = \App\Models\Admin::where($usernameField, $usernameValue)->first();
                if (!$user) {
                    return false;
                }

                $plain = (string) $request->input('password', '');
                if (password_verify($plain, (string) $user->password)) {
                    $this->guard()->login($user, $remember);

                    // Rehash para o driver atual, se necessário
                    if (\Illuminate\Support\Facades\Hash::needsRehash($user->password)) {
                        $user->password = \Illuminate\Support\Facades\Hash::make($plain);
                        $user->save();
                    }
                    return true;
                }
                return false;
            }
            throw $e;
        }
    }

    /**
     * The user has been authenticated.
     * Always send admin to rodeios, ignoring any previous intended URL from user area.
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('admin.rodeios.index');
    }

    public function logout(Request $request) {
        $this->guard()->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect($this->redirectTo);
    }
}
