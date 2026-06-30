<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FantasyLeague;
use App\Models\Rodeio;
use App\Models\Sponsor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArenaController extends Controller
{
    public function home(): View
    {
        $loaderSponsor = $this->primaryLoaderSponsor();

        return view('frontend.home', [
            'pageTitle' => 'Rei do Rodeio',
            'loaderSponsor' => $loaderSponsor,
            'loaderSponsorLogoUrl' => $this->loaderSponsorLogoUrl($loaderSponsor),
        ]);
    }

    public function show(): View
    {
        $arena = $this->resolveArenaState();

        return view('frontend.arena', [
            'pageTitle' => 'Arena Rei do Rodeio',
            'arenaEvent' => $arena['event'],
            'hasArenaEvent' => $arena['has_event'],
            'arenaLeagueBrand' => $this->resolveLeagueBrand($arena['event']['id'] ?? null),
            'supportUrl' => $this->supportUrl(),
        ]);
    }

    public function status(): JsonResponse
    {
        $arena = $this->resolveArenaState();

        return response()->json([
            'success' => true,
            'has_event' => $arena['has_event'],
            'event' => $arena['event'],
        ]);
    }

    private function resolveArenaState(): array
    {
        $rodeio = Rodeio::query()
            ->orderByRaw($this->prioritySql())
            ->orderByRaw('CASE WHEN start IS NULL THEN 1 ELSE 0 END')
            ->orderBy('start')
            ->first();

        if (!$rodeio || $this->isInactive($rodeio)) {
            return ['has_event' => false, 'event' => null];
        }

        $label = $this->rodeioLabel($rodeio);
        $status = $this->normalizeStatus((string) ($rodeio->status_transmissao ?? $rodeio->status ?? 'programado'));

        return [
            'has_event' => true,
            'event' => [
                'id' => (int) $rodeio->id,
                'label' => $label,
                'status' => $status,
                'status_label' => $this->statusLabel($status),
                'is_live' => $status === 'ao_vivo',
                'start_label' => $this->formatDate($rodeio->start),
                'end_label' => $this->formatDate($rodeio->end),
            ],
        ];
    }

    private function prioritySql(): string
    {
        return "CASE
            WHEN LOWER(COALESCE(status_transmissao, '')) IN ('ao_vivo', 'ao vivo', 'ativo') THEN 0
            WHEN LOWER(COALESCE(status, '')) IN ('active', 'ativo') THEN 1
            WHEN LOWER(COALESCE(status_transmissao, '')) = 'programado' THEN 2
            ELSE 3
        END";
    }

    private function isInactive(Rodeio $rodeio): bool
    {
        $status = strtolower((string) ($rodeio->status ?? ''));
        $transmission = strtolower((string) ($rodeio->status_transmissao ?? ''));

        if (in_array($status, ['inactive', 'inativo', 'finalizado', 'cancelado'], true)) {
            return true;
        }

        if (in_array($transmission, ['encerrado', 'finalizado', 'cancelado'], true)) {
            return true;
        }

        return false;
    }

    private function normalizeStatus(string $status): string
    {
        $value = strtolower(trim($status));

        if (in_array($value, ['ao_vivo', 'ao vivo'], true)) {
            return 'ao_vivo';
        }

        if (in_array($value, ['ativo', 'active'], true)) {
            return 'ativo';
        }

        return $value !== '' ? $value : 'programado';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'ao_vivo' => 'Ao vivo',
            'ativo' => 'Arena aberta',
            'programado' => 'Programado',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    private function formatDate(mixed $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function supportUrl(): string
    {
        $phone = '5547997953323';
        $text = urlencode('Ola! Preciso de ajuda na arena oficial do bolao.');

        return "https://api.whatsapp.com/send?phone={$phone}&text={$text}";
    }

    private function primaryLoaderSponsor(): ?Sponsor
    {
        try {
            if (!Schema::hasTable('sponsors')) {
                return null;
            }

            return Sponsor::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->first(['id', 'name', 'logo']);
        } catch (\Throwable) {
            return null;
        }
    }

    private function loaderSponsorLogoUrl(?Sponsor $sponsor): ?string
    {
        if (!$sponsor) {
            return null;
        }

        $version = (string) (($sponsor->updated_at?->timestamp) ?: $sponsor->id ?: time());

        return route('sponsors.logo', $sponsor) . '?v=' . $version;
    }

    private function resolveLeagueBrand(?int $rodeioId): array
    {
        $fallback = [
            'name' => 'Rei do Rodeio',
            'meta' => 'Nenhum bolão oficial no momento.',
            'logo_url' => asset('assets/images/logo/logorei.png'),
        ];

        if (!Schema::hasTable('fantasy_leagues')) {
            return $fallback;
        }

        $league = FantasyLeague::query()
            ->with([
                'rodeio',
                'organizerSponsor:id,name,logo',
            ])
            ->when($rodeioId, function ($query, $rodeioId) {
                $query->where('rodeio_id', (int) $rodeioId);
            })
            ->where(function ($query) {
                $query->where('is_active', true);

                if (Schema::hasColumn('fantasy_leagues', 'status')) {
                    $query->orWhere('status', 'finalized');
                }
            })
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (!$league) {
            return $fallback;
        }

        $organizerName = trim((string) ($league->organizerSponsor->name ?? ''));
        $rodeioName = trim($this->rodeioLabel($league->rodeio));

        return [
            'name' => $organizerName !== '' ? $organizerName : ($rodeioName !== '' ? $rodeioName : 'Bolão oficial'),
            'meta' => trim((string) ($league->name ?? 'Bolão oficial')),
            'logo_url' => $this->resolveLeagueBrandLogo($league) ?: $fallback['logo_url'],
        ];
    }

    private function resolveLeagueBrandLogo(FantasyLeague $league): ?string
    {
        if ($league->organizerSponsor && trim((string) ($league->organizerSponsor->logo ?? '')) !== '') {
            $version = (string) (($league->organizerSponsor->updated_at?->timestamp) ?: $league->updated_at?->timestamp ?: time());

            return route('sponsors.logo', $league->organizerSponsor) . '?v=' . $version;
        }

        $leagueImage = trim((string) ($league->image ?? ''));
        if ($leagueImage !== '') {
            return $this->publicMediaUrl($leagueImage, $league->updated_at?->timestamp);
        }

        $rodeioLogo = trim((string) ($league->rodeio->logo ?? ''));
        if ($rodeioLogo !== '') {
            return $this->publicMediaUrl($rodeioLogo, $league->updated_at?->timestamp);
        }

        return null;
    }

    private function publicMediaUrl(?string $path, ?int $version = null): ?string
    {
        $value = trim((string) ($path ?? ''));
        if ($value === '') {
            return null;
        }

        if (preg_match('~^(https?:)?//~i', $value)) {
            $url = $value;
        } else {
            $value = str_replace('\\', '/', $value);
            $value = ltrim($value, '/');
            $lower = strtolower($value);

            if (str_starts_with($lower, 'public/')) {
                $value = substr($value, 7);
                $lower = strtolower($value);
            }

            if (str_starts_with($lower, 'storage/')) {
                $value = substr($value, 8);
                $lower = strtolower($value);
            }

            if (str_starts_with($lower, 'assets/')) {
                $url = asset($value);
            } else {
                $url = Storage::disk('public')->url($value);
            }
        }

        if (!$version) {
            return $url;
        }

        $joiner = str_contains($url, '?') ? '&' : '?';

        return $url . $joiner . 'v=' . $version;
    }

    private function rodeioLabel(?Rodeio $rodeio): string
    {
        if (!$rodeio) {
            return '';
        }

        foreach (['nome', 'titulo', 'name'] as $column) {
            if (Schema::hasColumn('rodeios', $column)) {
                $value = trim((string) ($rodeio->{$column} ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return 'Rodeio #' . $rodeio->id;
    }
}
