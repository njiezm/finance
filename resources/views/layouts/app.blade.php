<!doctype html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F172A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Finance">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('services.webpush.public_key') }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    <title>{{ config('app.name', 'Finance Perso Elite Edition') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --fp-bg: #F8FAFC;
            --fp-card: #FFFFFF;
            --fp-text: #0F172A;
        }
        html.dark {
            --fp-bg: #020617;
            --fp-card: #0F172A;
            --fp-text: #F8FAFC;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--fp-bg);
            color: var(--fp-text);
            transition: background-color 0.3s ease;
            min-height: 100vh;
            padding-bottom: 100px;
        }
        .num { font-family: 'JetBrains Mono', monospace; }

        .fp-card {
            background: var(--fp-card);
            border: 1px solid #E2E8F0;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .fp-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px -10px rgb(0 0 0 / 0.18);
        }
        html.dark .fp-card { border-color: #1E293B; }

        .fp-glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .fp-glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 24px -12px rgba(2,6,23,.28);
        }
        html.dark .fp-glass {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .v-card {
            aspect-ratio: 1.58/1;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        .v-card:hover { transform: translateY(-8px) scale(1.02); }

        .chip {
            width: 44px;
            height: 32px;
            background: linear-gradient(135deg, #fcd34d 0%, #d97706 100%);
            border-radius: 6px;
        }

        .fp-nav {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(16px);
            padding: 8px;
            border-radius: 24px;
            display: flex;
            gap: 8px;
            z-index: 1000;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .fp-nav a {
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            border-radius: 18px;
            transition: all 0.2s;
            border: none;
            background: transparent;
            text-decoration: none;
        }
        .fp-nav a.active {
            background: #2563EB;
            color: white;
            box-shadow: 0 8px 15px rgba(37,99,235,0.4);
        }

        .mi-btn,
        .mi-tap {
            transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease;
        }
        .mi-btn:active,
        .mi-tap:active {
            transform: scale(.98);
        }

        .mi-reveal {
            opacity: 0;
            transform: translateY(10px);
            animation: miFade .35s ease forwards;
        }

        .loading-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        .fp-toast-wrap {
            position: fixed;
            left: 50%;
            bottom: 96px;
            transform: translateX(-50%);
            z-index: 2200;
            width: min(92vw, 420px);
            display: grid;
            gap: 10px;
            pointer-events: none;
        }

        .fp-toast {
            pointer-events: auto;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, .3);
            background: rgba(15, 23, 42, .94);
            color: #e2e8f0;
            padding: 12px 14px;
            box-shadow: 0 15px 24px -12px rgba(2, 6, 23, .56);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            opacity: 0;
            transform: translateY(8px) scale(.98);
            animation: toastIn .2s ease forwards;
        }
        .fp-toast[data-level="success"] { border-color: rgba(16, 185, 129, .45); }
        .fp-toast[data-level="error"] { border-color: rgba(244, 63, 94, .5); }
        .fp-toast[data-level="info"] { border-color: rgba(37, 99, 235, .45); }
        .fp-toast.closing { animation: toastOut .2s ease forwards; }

        .fp-toast button {
            border: 0;
            background: transparent;
            color: #cbd5e1;
            border-radius: 8px;
            line-height: 1;
            padding: 2px;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes miFade {
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes toastIn {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes toastOut {
            to { opacity: 0; transform: translateY(8px) scale(.98); }
        }
    </style>
</head>
<body class="p-3 md:p-6">
<header class="flex justify-between items-center mb-6 max-w-5xl mx-auto mi-reveal">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Finance Perso Elite</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $monthLabel ?? now()->translatedFormat('F Y') }}</p>
    </div>
    <div class="flex gap-2">
        <button id="install-app" class="p-2.5 rounded-full bg-blue-600 text-white transition-colors border-0 mi-btn hidden" title="Installer l'app">
            <i data-lucide="download" class="w-5 h-5"></i>
        </button>
        <button id="push-toggle" class="p-2.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors border-0 mi-btn" title="Activer notifications">
            <i data-lucide="bell" class="w-5 h-5"></i>
        </button>
        <button id="push-test" class="p-2.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors border-0 mi-btn" title="Envoyer notification test">
            <i data-lucide="send" class="w-5 h-5"></i>
        </button>
        <button id="theme-toggle" class="p-2.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors border-0 mi-btn">
            <i data-lucide="moon" class="w-5 h-5"></i>
        </button>
        <form method="POST" action="{{ route('auth.logout') }}" class="m-0">
            @csrf
            <button class="p-2.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-colors border-0 mi-btn" title="Se deconnecter" type="submit">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</header>

<main class="max-w-5xl mx-auto">
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm mi-reveal">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mi-reveal">
            <strong>Validation:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<div id="fp-toast-wrap" class="fp-toast-wrap" aria-live="polite" aria-atomic="true"></div>

<nav class="fp-nav" aria-label="Navigation principale">
    <a class="{{ request()->routeIs('overview') ? 'active' : '' }}" href="{{ route('overview') }}"><i data-lucide="layout-grid"></i></a>
    <a class="{{ request()->routeIs('accounts.page') ? 'active' : '' }}" href="{{ route('accounts.page') }}"><i data-lucide="credit-card"></i></a>
    <a class="{{ request()->routeIs('transactions.page') ? 'active' : '' }}" href="{{ route('transactions.page') }}"><i data-lucide="plus-circle"></i></a>
    <a class="{{ request()->routeIs('goals.page') ? 'active' : '' }}" href="{{ route('goals.page') }}"><i data-lucide="target"></i></a>
    <a class="{{ request()->routeIs('recommendations.page') ? 'active' : '' }}" href="{{ route('recommendations.page') }}"><i data-lucide="sparkles"></i></a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    const flashNotification = @json(session('fp_notification'));
    const insightNotifications = @json($insightNotifications ?? []);
    const toastWrap = document.getElementById('fp-toast-wrap');

    const root = document.documentElement;
    const key = 'fp-theme';
    const btn = document.getElementById('theme-toggle');
    const installBtn = document.getElementById('install-app');
    let deferredInstallPrompt = null;

    function vibrate(pattern = [16]) {
        if ('vibrate' in navigator) navigator.vibrate(pattern);
    }

    function toast(message, level = 'info', timeout = 3000) {
        if (!toastWrap) return;
        const node = document.createElement('div');
        node.className = 'fp-toast';
        node.dataset.level = level;
        node.innerHTML = '<span class="text-sm"></span><button type="button" aria-label="Fermer">x</button>';
        node.querySelector('span').textContent = message;
        toastWrap.prepend(node);

        const close = () => {
            node.classList.add('closing');
            setTimeout(() => node.remove(), 180);
        };

        node.querySelector('button')?.addEventListener('click', close);
        setTimeout(close, timeout);
    }

    function applyMicroInteractions() {
        document.querySelectorAll('.fp-card, .fp-glass, .v-card').forEach((el, i) => {
            if (!el.classList.contains('mi-reveal')) {
                el.classList.add('mi-reveal');
                el.style.animationDelay = `${Math.min(i * 30, 180)}ms`;
            }
        });

        document.querySelectorAll('button, .btn, a').forEach((el) => {
            if (!el.classList.contains('mi-tap')) el.classList.add('mi-tap');
            el.addEventListener('pointerdown', () => vibrate([8]), { passive: true });
        });
    }

    if (localStorage.getItem(key) === 'dark' || (!localStorage.getItem(key) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        root.classList.add('dark');
        btn?.querySelector('i')?.setAttribute('data-lucide', 'sun');
    }

    btn?.addEventListener('click', () => {
        const isDark = root.classList.toggle('dark');
        localStorage.setItem(key, isDark ? 'dark' : 'light');
        const icon = btn.querySelector('i');
        if (icon) icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
        lucide.createIcons();
        toast(isDark ? 'Mode sombre active' : 'Mode clair active', 'info');
    });

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
        return outputArray;
    }

    let swRegistration = null;

    async function ensureSw() {
        if (!('serviceWorker' in navigator)) throw new Error('Service Worker non supporte');
        swRegistration = await navigator.serviceWorker.register('/sw.js');
        await navigator.serviceWorker.ready;
        return swRegistration;
    }

    async function subscribePush() {
        if (!vapidPublicKey) throw new Error('VAPID_PUBLIC_KEY manquante');
        const reg = await ensureSw();

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') throw new Error('Permission notifications refusee');

        let subscription = await reg.pushManager.getSubscription();
        if (!subscription) {
            subscription = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            });
        }

        const payload = subscription.toJSON();
        payload.contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        const res = await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!res.ok) throw new Error('Echec abonnement serveur');

        toast('Notifications actives sur cet appareil', 'success');
        vibrate([22, 40, 22]);
        return subscription;
    }

    async function unsubscribePush() {
        const reg = await ensureSw();
        const sub = await reg.pushManager.getSubscription();
        if (!sub) return;

        await fetch('/push/unsubscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ endpoint: sub.endpoint })
        });

        await sub.unsubscribe();
        toast('Notifications desactivees', 'info');
    }

    async function sendTestPush() {
        const reg = await ensureSw();
        const sub = await reg.pushManager.getSubscription();
        if (!sub) throw new Error('Abonnement push inactif');

        const res = await fetch('/push/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ endpoint: sub.endpoint })
        });

        const data = await res.json();
        if (!res.ok || data.ok === false) throw new Error(data.error || 'Envoi test echoue');
        toast('Push distant envoye', 'success');
        vibrate([20]);
    }

    async function sendLocalSystemNotification(body = 'Notification', level = 'info') {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'granted') return;

        const reg = await ensureSw();
        const tag = level === 'error' ? 'fp-alert' : 'fp-info';

        await reg.showNotification('Finance Perso Elite', {
            body,
            icon: '/icons/icon.svg',
            badge: '/icons/icon.svg',
            tag,
            renotify: false,
            vibrate: level === 'error' ? [140, 60, 140] : [80],
            data: { url: '/' }
        });
    }

    const pushToggle = document.getElementById('push-toggle');
    const pushTest = document.getElementById('push-test');

    async function refreshPushButtonState() {
        if (!pushToggle) return;
        try {
            const reg = await ensureSw();
            const current = await reg.pushManager.getSubscription();
            const icon = pushToggle.querySelector('i');

            if (current) {
                pushToggle.title = 'Desactiver notifications';
                pushToggle.classList.add('bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-900/40', 'dark:text-emerald-300');
                if (icon) icon.setAttribute('data-lucide', 'bell-ring');
            } else {
                pushToggle.title = 'Activer notifications';
                pushToggle.classList.remove('bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-900/40', 'dark:text-emerald-300');
                if (icon) icon.setAttribute('data-lucide', 'bell');
            }
            lucide.createIcons();
        } catch (_) {
            // noop
        }
    }

    pushToggle?.addEventListener('click', async () => {
        try {
            const reg = await ensureSw();
            const current = await reg.pushManager.getSubscription();
            if (current) {
                await unsubscribePush();
            } else {
                await subscribePush();
            }
            await refreshPushButtonState();
        } catch (e) {
            console.error(e);
            toast('Notifications: ' + e.message, 'error', 4200);
        }
    });

    pushTest?.addEventListener('click', async () => {
        try {
            await sendLocalSystemNotification('Test notification systeme locale', 'info');
            await sendTestPush();
            toast('Test complet: systeme + push distant', 'info');
        } catch (e) {
            console.error(e);
            toast('Test notification: ' + e.message, 'error', 4200);
        }
    });

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        installBtn?.classList.remove('hidden');
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;

        deferredInstallPrompt.prompt();
        const choice = await deferredInstallPrompt.userChoice;

        if (choice.outcome === 'accepted') {
            toast('Installation lancee', 'success');
        }

        deferredInstallPrompt = null;
        installBtn.classList.add('hidden');
    });

    window.addEventListener('appinstalled', () => {
        toast('Application installee', 'success');
        installBtn?.classList.add('hidden');
        deferredInstallPrompt = null;
    });

    if ('serviceWorker' in navigator) {
        ensureSw().catch(() => {});
    }

    const queuedNotifications = [];
    if (flashNotification && flashNotification.message) queuedNotifications.push(flashNotification);
    if (Array.isArray(insightNotifications)) {
        insightNotifications.forEach((n) => {
            if (n && n.message) queuedNotifications.push(n);
        });
    }

    queuedNotifications.slice(0, 4).forEach((n, i) => {
        setTimeout(async () => {
            const level = n.level || 'info';
            toast(n.message, level, 4200);
            if (n.system) {
                try {
                    await sendLocalSystemNotification(n.message, level);
                } catch (_) {
                    // noop
                }
            }
        }, i * 420);
    });
    applyMicroInteractions();
    refreshPushButtonState();
    window.fpNotify = toast;

    lucide.createIcons();
})();
</script>
@stack('scripts')
</body>
</html>


