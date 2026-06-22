@extends('frontend.layouts.app')

@section('content')
<section style="padding: 40px 20px; max-width: 840px; margin: 0 auto;">
    <h1 style="font-size: 1.9rem; margin-bottom: 14px;">Exclusao de Dados (Facebook)</h1>
    <p style="color: #64748b; margin-bottom: 18px;">
        Se voce deseja excluir seus dados associados ao login com Facebook no Rei do Rodeio, siga os passos abaixo.
    </p>

    <ol style="padding-left: 20px; line-height: 1.7; color: #0f172a;">
        <li>Entre na sua conta no site/app.</li>
        <li>Acesse a aba <strong>Perfil</strong>.</li>
        <li>Clique em <strong>Excluir conta</strong> e confirme a solicitacao.</li>
        <li>Se preferir, envie uma solicitacao para o suporte oficial da plataforma.</li>
    </ol>

    <p style="margin-top: 20px; color: #334155;">
        URL de callback tecnico para o Facebook:
        <code>{{ route('facebook.data_deletion.callback') }}</code>
    </p>

    <p style="margin-top: 8px; color: #64748b;">
        URL desta pagina (instrucoes):
        <code>{{ route('facebook.data_deletion.instructions') }}</code>
    </p>
</section>
@endsection

