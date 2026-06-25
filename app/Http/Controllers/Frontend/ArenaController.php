<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Rodeio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ArenaController extends Controller
{
    public function show(): View
    {
        $arena = $this->resolveArenaState();

        return view('frontend.arena', [
            'pageTitle' => 'Arena Rei do Rodeio',
            'arenaEvent' => $arena['event'],
            'hasArenaEvent' => $arena['has_event'],
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

        $label = (string) ($rodeio->nome ?? $rodeio->name ?? $rodeio->titulo ?? ('Rodeio #' . $rodeio->id));
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

        return "https://wa.me/{$phone}?text={$text}";
    }
}
