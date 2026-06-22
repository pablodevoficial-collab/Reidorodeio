<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transmissão - Rei do Rodeio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0e1a;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .login-box {
            background: linear-gradient(145deg, #141927, #0f1322);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 16px;
            padding: 2.5rem;
            width: 380px;
            text-align: center;
            box-shadow: 0 0 40px rgba(249, 115, 22, 0.08);
        }
        .login-box img {
            width: 100px;
            height: 100px;
            margin-bottom: 1rem;
            border-radius: 12px;
            filter: drop-shadow(0 2px 12px rgba(249, 115, 22, 0.5));
        }
        .login-box h1 {
            color: #f97316;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        .login-box p {
            color: rgba(248,250,252,0.5);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }
        .login-box input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(249, 115, 22, 0.25);
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            color: #f8fafc;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .login-box input:focus {
            border-color: #f97316;
        }
        .login-box button {
            width: 100%;
            margin-top: 1rem;
            padding: 0.8rem;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .login-box button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.4);
        }
        .error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.8rem;
        }
    </style>
</head>
<body>
    <form class="login-box" method="POST" action="{{ route('broadcast.login.submit') }}">
        @csrf
        <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio">
        <h1>Painel de Transmissão</h1>
        <p>Digite a senha do admin para acessar</p>
        <input type="password" name="password" placeholder="Senha" autofocus required>
        <button type="submit">Entrar</button>
        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
    </form>
</body>
</html>
