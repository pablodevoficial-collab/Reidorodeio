<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualização da sua Foto de Perfil</title>
</head>
<body style="margin:0;padding:0;background:#070b16;font-family:Arial,Helvetica,sans-serif;color:#ecf4ff;line-height:1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070b16;padding:28px 12px;min-height:100vh;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:linear-gradient(180deg,#10182c 0%,#0a1020 100%);border:1px solid @if ($approved) rgba(16,185,129,.35) @else rgba(239,68,68,.35) @endif;border-radius:26px;overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px;background:linear-gradient(135deg,@if ($approved) #10b981 0%,#047857 100% @else #ef4444 0%,#b91c1c 100% @endif);color:#fff;font-size:12px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;">
                            Rei do Rodeio • Resultado da Análise
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 24px 28px;">
                            
                            @if ($approved)
                                <div style="text-align:center;margin-bottom:24px;">
                                    <div style="display:inline-block;width:80px;height:80px;border-radius:50%;background:rgba(16,185,129,0.15);border:2px solid #34d399;line-height:80px;font-size:36px;text-align:center;margin-bottom:16px;">
                                        ✅
                                    </div>
                                    <h2 style="margin:0 0 12px;color:#fff;font-size:26px;font-weight:900;letter-spacing:-0.5px;">Foto Aprovada!</h2>
                                    <p style="margin:0;color:#94a3b8;font-size:16px;">
                                        Sua foto de perfil foi verificada e já está brilhando no seu painel.
                                    </p>
                                </div>
                                
                                <div style="margin:28px 0;padding:20px;border-radius:18px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td width="36" valign="middle" align="center" style="font-size:24px;">📸</td>
                                            <td style="padding-left:16px;font-size:15px;color:#6ee7b7;font-weight:700;">
                                                Todos os usuários da plataforma Rei do Rodeio já podem ver o seu novo visual.
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @else
                                <div style="text-align:center;margin-bottom:24px;">
                                    <div style="display:inline-block;width:80px;height:80px;border-radius:50%;background:rgba(239,68,68,0.15);border:2px solid #f87171;line-height:80px;font-size:36px;text-align:center;margin-bottom:16px;">
                                        ⚠️
                                    </div>
                                    <h2 style="margin:0 0 12px;color:#fff;font-size:26px;font-weight:900;letter-spacing:-0.5px;">Atenção à Foto</h2>
                                    <p style="margin:0;color:#94a3b8;font-size:16px;">
                                        Infelizmente a sua nova foto de perfil <strong>não foi aprovada</strong> pela nossa moderação de conteúdo.
                                    </p>
                                </div>

                                @if ($photoRequest->admin_notes)
                                    <div style="margin:28px 0;padding:22px;border-radius:18px;background:rgba(239,68,68,0.1);border:1px dashed rgba(239,68,68,0.3);">
                                        <span style="display:block;font-size:12px;color:#fca5a5;text-transform:uppercase;font-weight:900;letter-spacing:1px;margin-bottom:8px;">MOTIVO DA RECUSA:</span>
                                        <p style="margin:0;color:#fee2e2;font-size:15px;font-weight:500;">
                                            "{{ $photoRequest->admin_notes }}"
                                        </p>
                                    </div>
                                @endif

                                <p style="margin:0 0 16px;text-align:center;color:#94a3b8;font-size:15px;">
                                    Se necessário, você pode logar na plataforma, corrigir a imagem e enviar uma nova foto.
                                </p>
                            @endif

                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 36px;text-align:center;">
                            <p style="margin:0;font-size:13px;color:#475569;font-weight:600;">
                                Equipe Rei do Rodeio<br>
                                &copy; {{ date('Y') }} Todos os direitos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
