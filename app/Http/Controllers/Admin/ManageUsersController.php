<?php
namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\CommissionLog;
use App\Models\NotificationLog;
use App\Lib\UserNotificationSender;
use App\Models\User;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ManageUsersController extends Controller {

    public function allUsers() {
        $pageTitle = 'Todos os Clientes';
        $users     = $this->userData();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.list', compact('pageTitle', 'users', 'countries'));
    }

    public function emailUnverifiedUsers() {
        $pageTitle = 'Clientes com E-mail Não Verificado';
        $users     = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycUnverifiedUsers() {
        $pageTitle = 'Clientes sem KYC';
        $users     = $this->userData('kycUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycPendingUsers() {
        $pageTitle = 'Clientes com KYC Pendente';
        $users     = $this->userData('kycPending');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers() {
        $pageTitle = 'Clientes com E-mail Verificado';
        $users     = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function mobileUnverifiedUsers() {
        $pageTitle = 'Clientes com Celular Não Verificado';
        $users     = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function mobileVerifiedUsers() {
        $pageTitle = 'Clientes com Celular Verificado';
        $users     = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function usersWithBalance() {
        // Método removido — funcionalidade "Com saldo" descontinuada.
        abort(404);
    }

    protected function userData($scope = null) {
        if ($scope) {
            $users = User::$scope();
        } else {
            $users = User::query();
        }
        return $users->searchable(['username', 'email', 'mobile'])->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function detail($id) {
        $user      = User::findOrFail($id);
        $pageTitle = 'Detalhes do Cliente - ' . $user->username;

        // Sem carteira/saldo no projeto: manter os campos para compatibilidade de view,
        // mas não calcular valores financeiros.
        $totalDeposit     = 0;
        $totalWithdrawals = 0;
        $totalTransaction = 0;

        $totalReferredUsers = User::where('ref_by', $user->id)->count();
        $totalReferralCom   = CommissionLog::where('to_id', $user->id)->sum('commission_amount');
    

        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.detail', compact('pageTitle', 'user', 'totalDeposit', 'totalWithdrawals', 'totalTransaction', 'countries', 'totalReferredUsers', 'totalReferralCom'));
    }

    public function kycDetails($id) {
        $pageTitle = 'KYC Details';
        $user      = User::findOrFail($id);
        return view('admin.users.kyc_detail', compact('pageTitle', 'user'));
    }

    public function kycApprove($id) {
        $user     = User::findOrFail($id);
        $user->kv = Status::KYC_VERIFIED;
        $user->save();

        notify($user, 'KYC_APPROVE', []);

        $notify[] = ['success', 'KYC approved successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function kycReject(Request $request, $id) {
        $request->validate([
            'reason' => 'required',
        ]);
        $user                       = User::findOrFail($id);
        $user->kv                   = Status::KYC_UNVERIFIED;
        $user->kyc_rejection_reason = $request->reason;
        $user->save();

        notify($user, 'KYC_REJECT', [
            'reason' => $request->reason,
        ]);

        $notify[] = ['success', 'KYC rejected successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);

        $validationRules = [
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile'    => 'required|string|max:40',
            'cpf'       => 'nullable|string|max:14',
            'birthdate' => 'nullable|date',
            'pix_key'   => 'nullable|string|max:255',
            'pix_key_type' => 'nullable|in:cpf,email,phone,random',
        ];

        // Se o país for enviado (compatibilidade), valida. Se não, usa o do usuário.
        if ($request->has('country')) {
             $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
             $countryArray = (array) $countryData;
             $countries    = implode(',', array_keys($countryArray));
             $validationRules['country'] = 'in:' . $countries;
        }

        $request->validate($validationRules);

        // Lógica de país/dial_code apenas se enviado
        if ($request->has('country')) {
            $countryCode = $request->country;
            $country     = $countryData->$countryCode->country;
            $dialCode    = $countryData->$countryCode->dial_code;

            $user->country_name = @$country;
            $user->dial_code    = $dialCode;
            $user->country_code = $countryCode;
        } else {
            $dialCode = $user->dial_code; // Mantém o atual para verificação de unicidade
        }

        $exists = User::where('mobile', $request->mobile)->where('dial_code', $dialCode)->where('id', '!=', $user->id)->exists();
        if ($exists) {
            $notify[] = ['error', 'The mobile number already exists.'];
            return back()->withNotify($notify);
        }

        $user->mobile    = $request->mobile;
        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->email     = $request->email;
        $user->cpf       = $request->cpf;
        $user->birthdate = $request->birthdate;
        $user->pix_key      = $request->pix_key;
        $user->pix_key_type = $request->pix_key_type;

        // Endereço removido da atualização obrigatória, mas mantido se enviado (opcional)
        if ($request->has('address')) $user->address = $request->address;
        if ($request->has('city')) $user->city = $request->city;
        if ($request->has('state')) $user->state = $request->state;
        if ($request->has('zip')) $user->zip = $request->zip;

        $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $user->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        $user->ts = $request->ts ? Status::ENABLE : Status::DISABLE;
        if (!$request->kv) {
            $user->kv = Status::KYC_UNVERIFIED;
            if ($user->kyc_data) {
                foreach ($user->kyc_data as $kycData) {
                    if ($kycData->type == 'file') {
                        fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
                    }
                }
            }
            $user->kyc_data = null;
        } else {
            $user->kv = Status::KYC_VERIFIED;
        }

        if ($request->password) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();
        $this->syncAffiliateStatus($user, $request->boolean('affiliate_active'));

        $notify[] = ['success', 'Detalhes do cliente atualizados com sucesso'];
        return back()->withNotify($notify);
    }

    protected function syncAffiliateStatus(User $user, bool $shouldBeActive): void
    {
        $affiliate = Affiliate::query()->firstOrNew(['user_id' => $user->id]);

        if (!$affiliate->exists) {
            $affiliate->referral_code = $this->generateUniqueAffiliateCode();
            $affiliate->tier = 'bronze';
            $affiliate->total_referrals = 0;
            $affiliate->active_referrals = 0;
            $affiliate->total_earned = 0;
            $affiliate->pending_commission = 0;
            $affiliate->paid_total = 0;
        } elseif (empty($affiliate->referral_code)) {
            $affiliate->referral_code = $this->generateUniqueAffiliateCode();
        }

        $affiliate->status = $shouldBeActive ? 'active' : 'suspended';
        $affiliate->suspended_reason = $shouldBeActive ? null : ($affiliate->suspended_reason ?: 'Desativado manualmente pelo admin.');
        $affiliate->save();
    }

    protected function generateUniqueAffiliateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Affiliate::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function sendNotificationSingle(Request $request, $id) {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        notify($user, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $notify[] = ['success', 'Notificação enviada com sucesso para ' . $user->username];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id) {
        // Sem carteira/saldo no projeto.
        abort(404);
    }

    public function login($id) {
        Auth::loginUsingId($id);
        return to_route('home');
    }

    public function status(Request $request, $id) {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);
            $user->status     = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[]         = ['success', 'Cliente banido com sucesso'];
        } else {
            $user->status     = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[]         = ['success', 'Cliente desbanido com sucesso'];
        }
        $user->save();
        return back()->withNotify($notify);

    }
    // Notification functionality removed: the admin menu item "Enviar notificação"
    // (per-user and broadcast) foi descontinuado. As rotas correspondentes foram removidas.


    public function countBySegment($methodName) {
        $allowed = [
            'emailUnverified',
            'emailVerified',
            'mobileUnverified',
            'mobileVerified',
            'kycUnverified',
            'kycPending',
        ];

        abort_unless(in_array($methodName, $allowed, true), 404);

        return User::active()->$methodName()->count();
    }

    public function list() {
        $query = User::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'users'   => $users,
            'more'    => $users->hasMorePages(),
        ]);
    }

    public function notificationLog($id) {
        $user      = User::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $user->username;
        $logs      = NotificationLog::where('user_id', $id)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'user'));
    }

    public function refereedUsers($id) {
        $user      = User::findOrFail($id);
        $pageTitle = "$user->username - Clientes Indicados";
        $users     = User::where('ref_by', $user->id)->latest()->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function referralCommissions($id) {
        // Sem carteira/saldo no projeto.
        abort(404);
    }

    public function exportInputs()
    {
        $pageTitle = 'Exportar Dados de Clientes';
        return view('admin.users.export', compact('pageTitle'));
    }

    public function exportDownload(Request $request)
    {
        $type = $request->input('type', 'full');
        $filename = 'clientes-reais-' . $type . '-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Configuração das colunas com base no tipo
        switch ($type) {
            case 'emails':
                // Apenas a coluna com os e-mails
                $fields = ['email'];
                $headerRow = ['Email'];
                break;
            case 'phones':
                // Apenas a coluna com os números, sem nomes ou outros dados
                $fields = ['mobile'];
                $headerRow = ['Celular'];
                break;
            default: // full
                $fields = ['id', 'firstname', 'lastname', 'email', 'mobile', 'username', 'created_at', 'status'];
                $headerRow = ['ID', 'Nome', 'Sobrenome', 'Email', 'Celular', 'Username', 'Data Cadastro', 'Status'];
                break;
        }

        $callback = function () use ($fields, $headerRow) {
            $file = fopen('php://output', 'w');
            
            // BOM para Excel reconhecer acentos e UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalho
            fputcsv($file, $headerRow, ';');

            User::real()->orderBy('id')->chunk(500, function ($users) use ($file, $fields) {
                foreach ($users as $user) {
                    $row = [];
                    foreach ($fields as $field) {
                        if ($field == 'status') {
                            $row[] = $user->status ? 'Ativo' : 'Inativo';
                        } else {
                            $row[] = $user->$field;
                        }
                    }
                    fputcsv($file, $row, ';');
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
