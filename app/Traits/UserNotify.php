<?php
namespace App\Traits;

use App\Constants\Status;

trait UserNotify {
    public static function notifyToUser() {
        return [
            'allUsers'          => 'Todos os usuários',
            'selectedUsers'     => 'Usuários selecionados',
            'kycUnverified'     => 'KYC não verificado',
            'kycVerified'       => 'KYC verificado',
            'kycPending'        => 'KYC pendente',
            'twoFaDisableUsers' => '2FA desativado',
            'twoFaEnableUsers'  => '2FA ativado',
            'pendingTicketUser' => 'Usuários com tickets pendentes',
            'answerTicketUser'  => 'Usuários com tickets respondidos',
            'closedTicketUser'  => 'Usuários com tickets encerrados',
            'notLoginUsers'     => 'Usuários que não logaram recentemente',
        ];
    }

    public function scopeSelectedUsers($query) {
        return $query->whereIn('id', request()->user ?? []);
    }

    public function scopeAllUsers($query) {
        return $query;
    }

    public function scopeTwoFaDisableUsers($query) {
        return $query->where('ts', Status::DISABLE);
    }

    public function scopeTwoFaEnableUsers($query) {
        return $query->where('ts', Status::ENABLE);
    }

    public function scopePendingTicketUser($query) {
        return $query->whereHas('tickets', function ($q) {
            $q->whereIn('status', [Status::TICKET_OPEN, Status::TICKET_REPLY]);
        });
    }

    public function scopeClosedTicketUser($query) {
        return $query->whereHas('tickets', function ($q) {
            $q->where('status', Status::TICKET_CLOSE);
        });
    }

    public function scopeAnswerTicketUser($query) {
        return $query->whereHas('tickets', function ($q) {

            $q->where('status', Status::TICKET_ANSWER);
        });
    }

    public function scopeNotLoginUsers($query) {
        return $query->whereDoesntHave('loginLogs', function ($q) {
            $q->whereDate('created_at', '>=', now()->subDays(request()->number_of_days ?? 10));
        });
    }

    public function scopeKycVerified($query) {
        return $query->where('kv', Status::KYC_VERIFIED);
    }

    public function scopeKycPending($query) {
        return $query->where('kv', Status::KYC_PENDING);
    }

    public function scopeKycUnverified($query) {
        return $query->where('kv', Status::KYC_UNVERIFIED);
    }

}
