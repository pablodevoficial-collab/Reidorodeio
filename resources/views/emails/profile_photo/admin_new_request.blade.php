<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Foto de Perfil na Fila</title>
</head>
<body style="margin:0;padding:0;background:#070b16;font-family:Arial,Helvetica,sans-serif;color:#ecf4ff;line-height:1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b16;padding:28px 12px;min-height:100vh;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:linear-gradient(180deg,#10182c 0%,#0a1020 100%);border:1px solid rgba(59,130,246,.35);border-radius:26px;overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px;background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:#fff;font-size:12px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;">
                            Rei do Rodeio • Moderação
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 24px 28px;">
                            <h2 style="margin:0 0 16px;color:#fff;font-size:24px;font-weight:900;letter-spacing:-0.5px;">Nova Foto na Fila</h2>
                            <p style="margin:0 0 24px;color:#94a3b8;font-size:16px;">
                                Uma nova foto de perfil foi enviada pela comunidade e aguarda sua aprovação no painel administrativo.
                            </p>
                            
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;background:rgba(2,6,23,0.6);border:1px solid rgba(255,255,255,0.05);border-radius:18px;">
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.05);">
                                        <span style="display:block;font-size:12px;color:#64748b;text-transform:uppercase;font-weight:800;letter-spacing:1px;margin-bottom:4px;">Usuário</span>
                                        <strong style="font-size:16px;color:#f8fafc;">{{ trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ($user->username ?? 'Usuário') }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.05);">
                                        <span style="display:block;font-size:12px;color:#64748b;text-transform:uppercase;font-weight:800;letter-spacing:1px;margin-bottom:4px;">Username e Email</span>
                                        <strong style="font-size:15px;color:#e2e8f0;">{{ $user->username ?? '-' }}</strong><br>
                                        <span style="font-size:14px;color:#94a3b8;">{{ $user->email ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <span style="display:block;font-size:12px;color:#64748b;text-transform:uppercase;font-weight:800;letter-spacing:1px;margin-bottom:4px;">Data do Envio</span>
                                        <strong style="font-size:15px;color:#e2e8f0;">{{ $photoRequest->created_at?->format('d/m/Y \à\s H:i') }}</strong>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:32px;text-align:center;">
                                <a href="{{ $approvalUrl }}" style="display:inline-block;padding:16px 36px;border-radius:99px;background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);color:#ffffff;font-size:15px;font-weight:900;text-decoration:none;letter-spacing:0.5px;box-shadow:0 8px 16px rgba(37,99,235,0.25);">
                                    ABRIR FILA DE APROVAÇÃO
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
