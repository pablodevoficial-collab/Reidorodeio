@php
    $title = trim((string) (($rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? null) ?: 'Próximo rodeio'));
    $formattedDate = $startAt?->timezone(config('app.timezone'))->format('d/m/Y \à\s H:i');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta ativado</title>
</head>
<body style="margin:0;padding:0;background:#070b16;font-family:Arial,Helvetica,sans-serif;color:#ecf4ff;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b16;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:linear-gradient(180deg,#10182c 0%,#0a1020 100%);border:1px solid rgba(250,174,33,.35);border-radius:26px;overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px;background:linear-gradient(135deg,#f7a600 0%,#f97316 100%);color:#1d1302;font-size:12px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;">
                            Rei do Rodeio • Alerta ativado
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 18px;">
                            <div style="font-size:13px;line-height:1.5;color:#f7b955;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Seu lembrete está confirmado</div>
                            <h1 style="margin:8px 0 10px;font-size:28px;line-height:1.15;color:#ffffff;">{{ $title }}</h1>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#c4d3ee;">
                                Assim que o rodeio começar, a gente te envia outro e-mail com o botão direto para voltar à arena.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 10px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0d1629;border:1px solid rgba(129,153,194,.22);border-radius:20px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#8ca2c8;font-weight:700;margin-bottom:10px;">Resumo do alerta</div>
                                        <div style="font-size:15px;color:#ffffff;font-weight:700;margin-bottom:8px;">{{ $title }}</div>
                                        @if($formattedDate)
                                            <div style="font-size:14px;color:#c7d5ee;margin-bottom:6px;">Previsão: {{ $formattedDate }}</div>
                                        @endif
                                        <div style="font-size:14px;color:#8cf2ae;font-weight:700;">E-mail cadastrado: {{ $reminder->email }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px 30px;">
                            <a href="{{ $ctaUrl }}" style="display:inline-block;padding:14px 22px;border-radius:14px;background:linear-gradient(135deg,#f8b000 0%,#ff6a00 100%);color:#1a1204;text-decoration:none;font-size:14px;font-weight:800;letter-spacing:.03em;">
                                Ir para o site
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
