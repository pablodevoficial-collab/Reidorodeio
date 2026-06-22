<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\GatewayCurrency;
use Illuminate\Http\Request;

class PaymentController extends Controller {
    public function deposit() {
        abort(404);
    }

    public function depositInsert(Request $request) {
        abort(404);
    }

    public function appDepositConfirm($hash) {
        abort(404);
    }

    public function depositConfirm() {
        abort(404);
    }

    public static function userDataUpdate($deposit, $isManual = null) {
        // Sem carteira/saldo no projeto: não realizar atualizações financeiras.
        return;
    }

    public function manualDepositConfirm() {
        abort(404);
    }

    public function manualDepositUpdate(Request $request) {
        abort(404);
    }

}
