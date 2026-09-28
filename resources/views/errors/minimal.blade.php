<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-6 antialiased">
    <div class="max-w-lg w-full text-center space-y-6">
        
        {{-- Illustration thématique --}}
        <div class="flex justify-center mb-4">
            @yield('image')
        </div>

        {{-- Code d'erreur --}}
        <span class="inline-block px-3 py-1 bg-indigo-900/50 text-indigo-400 font-mono text-sm font-semibold rounded-full border border-indigo-800/50">
            Erreur @yield('code')
        </span>

        {{-- Titre et explication --}}
        <h1 class="text-2xl md:text-3xl font-bold text-white">
            @yield('title')
        </h1>
        <p class="text-gray-400 text-base leading-relaxed">
            @yield('message')
        </p>

        {{-- Bouton de retour --}}
        <div class="pt-4">
            @yield('button')
        </div>

    </div>
</body>
</html>