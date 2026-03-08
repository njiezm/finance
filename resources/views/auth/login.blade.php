<!doctype html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F172A">
    <title>Connexion - Finance Perso Elite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg,#0f172a,#1e293b,#2563eb); }
        .auth-card { max-width: 420px; border-radius: 24px; backdrop-filter: blur(8px); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card auth-card border-0 shadow-lg w-100">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 fw-bold mb-1">Finance Perso Elite</h1>
            <p class="text-secondary mb-4">Entrez votre mot de passe pour continuer.</p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('auth.login.submit') }}" class="mb-3">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control form-control-lg" required autofocus>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                    <label class="form-check-label" for="remember">Rester connecte</label>
                </div>
                <button class="btn btn-primary btn-lg w-100">Se connecter</button>
            </form>

            <form method="POST" action="{{ route('auth.forgot') }}" class="mb-3">
                @csrf
                <button class="btn btn-link p-0">Mot de passe oublie ? Envoyer sur njiezamon10@gmail.com</button>
            </form>
        </div>
    </div>
</body>
</html>
