<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Stock') — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary-bg font-sans text-gray-800">

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside class="w-48 bg-primary-dark flex flex-col flex-shrink-0 fixed top-0 left-0 h-screen z-40">
        <div class="px-4 py-4 border-b border-white/10">
            <div class="font-serif text-white text-base tracking-tight">Claire<span class="text-amber-ca">Afrique</span></div>
            <div class="text-primary-pale text-xs mt-0.5">Espace responsable stock</div>
        </div>
        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/10">
            <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center
                        text-white text-xs font-semibold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}
            </div>
            <div>
                <div class="text-white text-xs font-medium">{{ Auth::user()->prenom }}</div>
                <div class="text-primary-pale text-xs">res.stock</div>
            </div>
        </div>
        <nav class="flex-1 py-2 overflow-y-auto">
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase">
                Principal
            </div>
            <a href="{{ route('stock.dashboard') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('stock.dashboard') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Tableau de bord
            </a>
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase mt-2">
                Catalogue
            </div>
            <a href="{{ route('stock.articles') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('stock.articles') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Articles
            </a>
            <a href="{{ route('stock.categories') }}"
            class="flex items-center gap-2 px-4 py-2 text-xs
                    {{ request()->routeIs('stock.categories') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Catégories
            </a>
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase mt-2">
                Stock
            </div>
            <a href="{{ route('stock.mouvements') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('stock.mouvements') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Mouvements
            </a>
            <a href="{{ route('stock.alertes') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('stock.alertes') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Alertes stock
            </a>
        </nav>
        <div class="px-4 py-3 border-t border-white/10 space-y-2">
            <a href="{{ route('stock.compte') }}"
               class="block text-xs {{ request()->routeIs('stock.compte') ? 'text-white font-medium' : 'text-primary-pale hover:text-white' }}">
                Mon compte
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-primary-pale hover:text-white text-xs">
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 flex flex-col min-h-screen ml-48">
        <header class="bg-white border-b border-primary-pale h-11 flex items-center
                       justify-between px-5 flex-shrink-0 sticky top-0 z-30">
            <h1 class="text-lg font-medium text-primary-dark tracking-tight">@yield('page_title')</h1>
            <div class="flex items-center gap-4">
                @include('shared._notifications-bell', ['espaceNotif' => 'stock'])
                <span class="text-xs text-gray-400">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
            </div>
        </header>
        <main class="flex-1 p-5">
            @if(auth()->user()->estScopePointVente())
                <div class="bg-primary/10 text-primary-dark text-xs font-medium px-4 py-2 rounded-lg mb-4 inline-flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-4 h-4 inline-block flex-shrink-0" />
                    Vous gérez le stock du point de vente :
                    {{ auth()->user()->pointVenteAssigne === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                </div>
            @endif
            @if(session('success'))
                <div class="bg-primary-pale text-primary-dark text-sm px-4 py-3 rounded-lg mb-4" role="status">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="bg-yellow-50 text-yellow-700 text-sm px-4 py-3 rounded-lg mb-4" role="status">
                    {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

</div>
</body>
</html>