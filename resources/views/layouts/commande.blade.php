<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Commandes') — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-primary-bg font-sans text-gray-800">

<div class="flex min-h-screen">

    <aside id="staff-sidebar" class="w-64 bg-primary-dark flex flex-col flex-shrink-0 fixed top-0 left-0 h-screen z-40 -translate-x-full transition-transform duration-200 md:w-48 md:translate-x-0">
        <div class="px-4 py-4 border-b border-white/10">
            <div class="font-serif text-white text-base tracking-tight mb-3">Claire<span class="text-amber-ca">Afrique</span></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center
                            text-white text-xs font-semibold flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-white text-xs font-medium truncate">{{ Auth::user()->prenom }}</div>
                    <div class="text-primary-pale text-[11px]">
                        {{ Auth::user()->hasRole('administrateur') ? 'Administrateur' : 'Responsable commande' }}
                    </div>
                </div>
            </div>
        </div>
        @if(Auth::user()->hasRole('administrateur'))
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-1.5 px-4 py-2 text-xs text-primary-pale hover:text-white
                  bg-white/5 hover:bg-white/10 border-b border-white/10 transition-colors">
            <x-heroicon-o-arrow-left class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retour à l'espace admin
        </a>
        @endif
        <nav class="flex-1 py-2 overflow-y-auto">
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase">
                Principal
            </div>
            <a href="{{ route('commande.dashboard') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('commande.dashboard') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Tableau de bord
            </a>
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase mt-2">
                Commandes
            </div>
            <a href="{{ route('commande.liste') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('commande.liste') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Toutes les commandes
            </a>
            {{-- Dans la sidebar, lien "En attente" --}}
                <a href="{{ route('commande.liste') }}?statut=en_attente"
                class="flex items-center justify-between gap-2 px-4 py-2 text-xs text-primary-pale hover:bg-white/5 hover:text-white">
                    <span>En attente</span>
                    <span id="badge-attente"
                        class="hidden bg-yellow-400 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        0
                    </span>
                </a>
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase mt-2">
                Livraisons
            </div>
            <a href="{{ route('commande.livraisons') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('commande.livraisons') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Suivi livraisons
            </a>
            <div class="px-4 py-2 text-xs font-semibold text-primary-light tracking-widest uppercase mt-2">
                Paiements
            </div>
            <a href="{{ route('commande.paiements') }}"
               class="flex items-center gap-2 px-4 py-2 text-xs
                      {{ request()->routeIs('commande.paiements') ? 'bg-white/10 text-white border-l-2 border-primary-light' : 'text-primary-pale hover:bg-white/5 hover:text-white' }}">
                Vérification paiements
            </a>
        </nav>
        <div class="px-4 py-3 border-t border-white/10 space-y-2">
            <a href="{{ route('commande.compte') }}"
               class="block text-xs {{ request()->routeIs('commande.compte') ? 'text-white font-medium' : 'text-primary-pale hover:text-white' }}">
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
        <button type="button" data-staff-menu-backdrop aria-label="Fermer le menu"
            class="hidden fixed inset-0 bg-black/40 z-30 md:hidden"></button>

    <div class="w-full min-w-0 flex-1 flex flex-col min-h-screen md:ml-48">
        <header class="bg-white border-b border-primary-pale min-h-11 flex items-center
                       justify-between gap-2 px-3 sm:px-5 py-2 flex-shrink-0 sticky top-0 z-30">
            <button type="button" data-staff-menu-toggle aria-expanded="false" aria-label="Ouvrir le menu"
                    class="md:hidden text-primary-dark p-1 flex-shrink-0">
                <x-heroicon-o-bars-3 class="w-6 h-6" />
            </button>
            <h1 class="min-w-0 flex-1 truncate text-sm sm:text-lg font-medium text-primary-dark tracking-tight">@yield('page_title')</h1>
            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                @include('shared._notifications-bell', ['espaceNotif' => 'commande'])
                <span class="hidden sm:inline text-xs text-gray-400">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
            </div>
        </header>
        <main class="min-w-0 flex-1 p-3 sm:p-5">
            @if(auth()->user()->estScopePointVente())
                <div class="bg-primary/10 text-primary-dark text-xs font-medium px-4 py-2 rounded-lg mb-4 inline-flex items-center gap-2">
                    <x-heroicon-o-map-pin class="w-4 h-4 inline-block flex-shrink-0" />
                    Vous gérez les commandes du point de vente :
                    {{ auth()->user()->pointVenteAssigne === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                </div>
            @endif
            @if(session('success'))
                <div class="bg-primary-pale text-primary-dark text-sm px-4 py-3 rounded-lg mb-4" role="status">
                    {{ session('success') }}
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
<script>
// Polling toutes les 30 secondes
function verifierNotifications() {
    fetch('{{ route("commande.notifications.count") }}')
    .then(r => r.json())
    .then(data => {
        // Badge dans la sidebar
        const badgeAttente = document.getElementById('badge-attente');
        const badgePaiement = document.getElementById('badge-paiement');
        const badgeTotal = document.getElementById('badge-total-notif');

        if (badgeAttente) {
            badgeAttente.textContent = data.enAttente;
            badgeAttente.classList.toggle('hidden', data.enAttente === 0);
        }
        if (badgePaiement) {
            badgePaiement.textContent = data.paiements;
            badgePaiement.classList.toggle('hidden', data.paiements === 0);
        }
        if (badgeTotal) {
            badgeTotal.textContent = data.total;
            badgeTotal.classList.toggle('hidden', data.total === 0);
        }

        // Toast si nouvelles commandes
        if (data.enAttente > 0) {
            document.title = '(' + data.enAttente + ') Commandes — ClaireAfrique';
        } else {
            document.title = 'Commandes — ClaireAfrique';
        }
    })
    .catch(() => {}); // Ignorer les erreurs réseau
}

// Lancer le polling
verifierNotifications();
setInterval(verifierNotifications, 30000);
</script>

</body>
</html>