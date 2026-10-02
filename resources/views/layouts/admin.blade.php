<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary-bg font-sans text-gray-800">

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside id="staff-sidebar" class="w-64 bg-primary-dark flex flex-col flex-shrink-0 fixed top-0 left-0 h-screen z-40 -translate-x-full transition-transform duration-200 md:w-48 md:translate-x-0">

        <div class="px-4 py-4 border-b border-white/10">
            <div class="font-serif text-white text-base tracking-tight">Claire<span class="text-amber-ca">Afrique</span></div>
            <div class="text-primary-pale text-xs mt-0.5">Espace administrateur</div>
        </div>

        {{-- User --}}
        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/10">
            <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center
                        text-white text-xs font-semibold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}
            </div>
            <div>
                <div class="text-white text-xs font-medium">{{ Auth::user()->prenom }}</div>
                <div class="text-primary-pale text-xs">Accès complet</div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-2 overflow-y-auto">
            <div class="px-4 py-2 text-xs font-semibold text-primary-light
                        tracking-widest uppercase">
                Principal
            </div>
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('admin.dashboard')
                            ? 'bg-white/10 text-white border-l-2 border-primary-light'
                            : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Tableau de bord
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-primary-light
                        tracking-widest uppercase mt-2">
                Catalogue
            </div>
            <a href="{{ route('admin.articles') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('admin.articles')
                            ? 'bg-white/10 text-white border-l-2 border-primary-light'
                            : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Articles
            </a>
            <a href="{{ route('admin.categories') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('admin.categories')
                            ? 'bg-white/10 text-white border-l-2 border-primary-light'
                            : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Catégories
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-primary-light
                        tracking-widest uppercase mt-2">
                Gestion
            </div>
            <a href="{{ route('admin.utilisateurs') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('admin.utilisateurs')
                            ? 'bg-white/10 text-white border-l-2 border-primary-light'
                            : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Utilisateurs
            </a>
            <a href="{{ route('admin.mouvements') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('admin.mouvements')
                            ? 'bg-white/10 text-white border-l-2 border-primary-light'
                            : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Mouvements stock
            </a>

            <div class="px-4 py-2 text-xs font-semibold text-primary-light
                        tracking-widest uppercase mt-2">
                Rapports
            </div>
            <a href="{{ route('admin.rapports') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('admin.rapports')
                            ? 'bg-white/10 text-white border-l-2 border-primary-light'
                            : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Rapports complets
            </a>
        </nav>

        {{-- Compte / Logout --}}
        <div class="px-4 py-3 border-t border-white/10 space-y-2">
            <a href="{{ route('admin.compte') }}"
               class="block text-xs {{ request()->routeIs('admin.compte')
                        ? 'text-white font-medium'
                        : 'text-primary-pale hover:text-white' }}">
                Mon compte
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="text-primary-pale hover:text-white text-xs">
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>
        <button type="button" data-staff-menu-backdrop aria-label="Fermer le menu"
            class="hidden fixed inset-0 bg-black/40 z-30 md:hidden"></button>

    {{-- MAIN — décalé de la largeur de la sidebar --}}
    <div class="w-full min-w-0 flex-1 flex flex-col min-h-screen md:ml-48">

        {{-- Topbar --}}
        <header class="bg-white border-b border-primary-pale min-h-11 flex items-center
                       justify-between gap-2 px-3 sm:px-5 py-2 flex-shrink-0 sticky top-0 z-30">
            <button type="button" data-staff-menu-toggle aria-expanded="false" aria-label="Ouvrir le menu"
                    class="md:hidden text-primary-dark p-1 flex-shrink-0">
                <x-heroicon-o-bars-3 class="w-6 h-6" />
            </button>
            <h1 class="min-w-0 flex-1 truncate text-sm sm:text-lg font-medium text-primary-dark tracking-tight">@yield('page_title')</h1>
            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                @include('shared._notifications-bell', ['espaceNotif' => 'admin'])
                <span class="hidden sm:inline text-xs text-gray-400">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
            </div>
        </header>

        {{-- Content --}}
        <main class="min-w-0 flex-1 p-3 sm:p-5">
            @if(session('success'))
                <div class="bg-primary-pale text-primary-dark text-sm px-4 py-3
                            rounded-lg mb-4" role="status">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 text-red-700 text-sm px-4 py-3
                            rounded-lg mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>

</body>
</html>