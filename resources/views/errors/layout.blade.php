<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Erreur') — ClaireAfrique</title>
    {{--
        Volontairement minimal et autonome : une page d'erreur (surtout 500/503)
        peut être affichée alors que l'application est dans un état dégradé.
        Pas de requête Auth::user(), pas de compteur panier, pas de menu
        déroulant — juste du HTML/CSS statique qui ne peut pas échouer à
        son tour et transformer une 500 en une 500 en boucle.
    --}}
    @vite(['resources/css/app.css'])
</head>
<body class="bg-primary-bg font-sans text-gray-800 min-h-screen flex flex-col">

    {{-- EN-TÊTE (version statique du header du site, sans dépendance auth) --}}
    <header class="bg-primary-dark">
        <div class="max-w-7xl mx-auto px-6 h-13 flex items-center py-3">
            <a href="{{ route('home') }}" class="text-xs text-primary-pale hover:text-white">
                ClaireAfrique
            </a>
        </div>
    </header>

    {{-- CONTENU --}}
    <main class="flex-1 flex items-center justify-center px-6 py-16">
        <div class="max-w-md w-full text-center">
            <div class="text-6xl mb-4" aria-hidden="true">@yield('icon', '⚠️')</div>

            <p class="text-sm font-semibold text-amber-ca tracking-wide uppercase mb-2">
                Erreur {{ $code ?? '' }}
            </p>

            <h1 class="text-2xl font-bold text-primary-dark mb-3">
                @yield('heading', 'Une erreur est survenue')
            </h1>

            <p class="text-sm text-gray-600 mb-8 leading-relaxed">
                @yield('message', 'Merci de réessayer dans un instant.')
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @yield('actions')
            </div>
        </div>
    </main>

    {{-- PIED DE PAGE (identique au site) --}}
    <footer class="bg-primary-dark">
        <div class="max-w-7xl mx-auto px-6 py-6 text-center">
            <span class="text-primary-pale text-xs">
                © {{ date('Y') }} ClaireAfrique — Dakar, Sénégal
            </span>
        </div>
    </footer>

</body>
</html>
