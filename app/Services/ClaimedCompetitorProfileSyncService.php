<?php

namespace App\Services;

use App\Models\Competitor;
use App\Models\User;

class ClaimedCompetitorProfileSyncService
{
    public function syncFromUser(User $user): void
    {
        $competitor = $user->claimedCompetitor;

        if (!$competitor) {
            return;
        }

        $displayName = trim((string) $user->firstname . ' ' . (string) $user->lastname);

        if ($displayName === '') {
            $displayName = trim((string) ($user->username ?? ''));
        }

        if ($displayName !== '' && $competitor->nome !== $displayName) {
            $competitor->forceFill([
                'nome' => $displayName,
            ])->save();
        }
    }

    public function syncFromCompetitor(Competitor $competitor): void
    {
        $competitor->loadMissing('claimedUser');

        if (!$competitor->profile_claimed || !$competitor->claimedUser) {
            return;
        }

        $this->syncFromUser($competitor->claimedUser);
    }
}
