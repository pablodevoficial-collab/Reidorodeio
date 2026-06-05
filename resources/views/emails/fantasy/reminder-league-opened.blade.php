@php
    $leagueName = trim((string) (($league->name ?? null) ?: 'Bolão'));
    $rodeioName = trim((string) (($league->rodeio->name ?? $league->rodeio->nome ?? null) ?: 'Rei do Rodeio'));
    $emailPrizeSummary = is_array($emailPrizeSummary ?? null) ? $emailPrizeSummary : [];
    $targetTeams = (int) ($emailPrizeSummary['target_teams'] ?? 0);
    $projectedPrizePool = (float) ($emailPrizeSummary['projected_prize_pool'] ?? 0);
    $displayPaidPositions = (int) ($emailPrizeSummary['display_paid_positions'] ?? 0);
    $topThree = is_array($emailPrizeSummary['top_three'] ?? null) ? $emailPrizeSummary['top_three'] : [];
    $money = static fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu bolão está liberado</title>
</head>
<body style="margin:0;padding:0;background:#070b16;font-family:Arial,Helvetica,sans-serif;color:#ecf4ff;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b16;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:linear-gradient(180deg,#10182c 0%,#0a1020 100%);border:1px solid rgba(250,174,33,.35);border-radius:26px;overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px;background:linear-gradient(135deg,#f7a600 0%,#f97316 100%);color:#1d1302;font-size:12px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;">
                            Rei do Rodeio • Seu bolão está aberto
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 18px;">
                            <div style="font-size:13px;line-height:1.5;color:#f7b955;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Atenção: vagas em movimento</div>
                            <h1 style="margin:8px 0 10px;font-size:28px;line-height:1.15;color:#ffffff;">Seu bolão {{ $slotLabel }} já está liberado para entrada</h1>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#c4d3ee;">
                                O bolão {{ $leagueName }} abriu. Agora é a hora de montar seu time, garantir sua entrada e sair na frente antes que as vagas sejam preenchidas.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 10px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0d1629;border:1px solid rgba(129,153,194,.22);border-radius:20px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#8ca2c8;font-weight:700;margin-bottom:10px;">Entrada aberta agora</div>
                                        <div style="font-size:15px;color:#ffffff;font-weight:700;margin-bottom:8px;">{{ $leagueName }}</div>
                                        <div style="font-size:14px;color:#8cf2ae;font-weight:700;">{{ $rodeioName }} • Bolão {{ $slotLabel }}</div>
                                        <div style="margin-top:10px;font-size:13px;line-height:1.6;color:#c4d3ee;">Entre agora para escalar sua equipe enquanto o bolão ainda está recebendo entradas.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($projectedPrizePool > 0)
                        <tr>
                            <td style="padding:4px 24px 10px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:linear-gradient(135deg,rgba(249,115,22,.16),rgba(59,130,246,.12));border:1px solid rgba(249,174,33,.28);border-radius:20px;">
                                    <tr>
                                        <td style="padding:18px 20px;">
                                            <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#f7b955;font-weight:700;margin-bottom:10px;">Premiação projetada ao bater a meta</div>
                                            <div style="font-size:28px;line-height:1.1;color:#ffffff;font-weight:900;">{{ $money($projectedPrizePool) }}</div>
                                            <div style="margin-top:8px;font-size:13px;line-height:1.6;color:#d8e4fb;">
                                                @if($targetTeams > 0)
                                                    Com {{ $targetTeams }} times na liga, o bolão projeta pagar até o top {{ max($displayPaidPositions, 3) }}.
                                                @else
                                                    Esta é a projeção de prêmio total do bolão no cenário cheio.
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    @if(!empty($topThree))
                        <tr>
                            <td style="padding:4px 24px 10px;">
                                <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#8ca2c8;font-weight:700;margin-bottom:10px;">Pódio Top 3 projetado</div>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        @foreach($topThree as $prizeRow)
                                            <td width="33.33%" valign="top" style="padding:0 4px;">
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0d1629;border:1px solid rgba(129,153,194,.22);border-radius:18px;">
                                                    <tr>
                                                        <td style="padding:16px 12px;text-align:center;">
                                                            <div style="font-size:22px;margin-bottom:8px;">{{ $prizeRow['position'] === 1 ? '🥇' : ($prizeRow['position'] === 2 ? '🥈' : '🥉') }}</div>
                                                            <div style="font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:#8ca2c8;font-weight:700;">{{ $prizeRow['position'] }}º lugar</div>
                                                            <div style="margin-top:8px;font-size:18px;line-height:1.2;color:#ffffff;font-weight:900;">{{ $money($prizeRow['amount'] ?? 0) }}</div>
                                                            <div style="margin-top:6px;font-size:12px;color:#f7b955;font-weight:700;">{{ number_format((float) ($prizeRow['percent'] ?? 0), 2, ',', '.') }}% da premiação</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        @endforeach
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:14px 24px 30px;">
                            <a href="{{ $ctaUrl }}" style="display:inline-block;padding:14px 22px;border-radius:14px;background:linear-gradient(135deg,#f8b000 0%,#ff6a00 100%);color:#1a1204;text-decoration:none;font-size:14px;font-weight:800;letter-spacing:.03em;">
                                Montar equipe e garantir vaga
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>