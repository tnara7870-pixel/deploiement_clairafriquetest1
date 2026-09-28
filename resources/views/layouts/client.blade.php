<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClaireAfrique') — Librairie Papeterie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-primary-bg font-sans text-gray-800 min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <nav class="bg-primary-dark sticky top-0 z-40 relative">
        <div class="max-w-7xl mx-auto px-6 h-13 flex items-center justify-between py-3">
            <a href="{{ route('home') }}"
                class="font-serif text-xl tracking-tight {{ request()->routeIs('home') ? 'text-white' : 'text-primary-pale hover:text-white' }}">
                Claire<span class="text-amber-ca">Afrique</span>
            </a>

            {{-- Liens nav (masqués sur mobile, voir menu hamburger) --}}
            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('client.catalogue') }}"
                    class="text-xs {{ request()->routeIs('client.catalogue') ? 'text-white' : 'text-primary-pale hover:text-white' }}">
                    Catalogue
                </a>
                <div class="relative group">
                    <button type="button" class="text-xs text-primary-pale hover:text-white flex items-center gap-1">
                        Catégories
                        <svg class="w-3 h-3 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="absolute left-0 top-full pt-2 hidden group-hover:block z-50">
                        <div class="bg-white rounded-lg shadow-lg border border-primary-pale py-1 min-w-[200px] max-h-80 overflow-y-auto">
                            @forelse($categoriesMenu ?? [] as $categorie)
                            <a href="{{ route('client.catalogue', ['categorie' => $categorie->idCategorie]) }}"
                                class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-primary-bg hover:text-primary-dark">
                                <x-icone-categorie :nom="$categorie->nomCategorie" class="w-4 h-4 text-primary flex-shrink-0" />
                                {{ $categorie->nomCategorie }}
                            </a>
                            @empty
                            <span class="block px-4 py-2 text-xs text-gray-400">Aucune catégorie</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Barre de recherche (masquée sur mobile, reprise dans le panneau hamburger) --}}
            <form action="{{ route('client.catalogue') }}" method="GET"
                class="hidden md:flex items-center gap-2">
                <label for="recherche-nav-desktop" class="sr-only">Rechercher un article</label>
                <input id="recherche-nav-desktop" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Rechercher un article…"
                    class="border border-white/20 rounded-lg px-3 py-1.5 text-xs
                           bg-white/10 text-white placeholder-white/50
                           focus:outline-none focus:border-white/50 w-56">
                <button type="submit"
                    class="bg-primary text-white px-3 py-1.5 rounded-lg text-xs
                           hover:bg-primary-light">
                    Rechercher
                </button>
            </form>

            
            {{-- Actions (masquées sur mobile, reprises dans le panneau hamburger) --}}
            <div class="hidden md:flex items-center gap-4">
                @auth
                    @include('shared._notifications-bell', ['espaceNotif' => 'client', 'classeDeclencheur' => 'text-primary-pale hover:text-white'])
                @endauth
                @auth
                    {{-- Favoris --}}
                    <a href="{{ route('client.favoris') }}"
                        class="text-primary-pale hover:text-white text-xs flex items-center gap-1">
                        <x-heroicon-o-heart class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Favoris
                    </a>

                    {{-- Panier --}}
                    @php
                        $nbPanier = Auth::user()->panier?->lignePaniers()->count() ?? 0;
                    @endphp
                   <a href="{{ route('client.panier') }}"
                    class="text-primary-pale hover:text-white text-xs flex items-center gap-1 relative">
                    <x-heroicon-o-shopping-cart class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Panier
                    @if($nbPanier > 0)
                        <span id="badge-panier"
                            class="absolute -top-2 -right-3 bg-amber-ca text-white text-xs
                                w-4 h-4 rounded-full flex items-center justify-center font-semibold">
                            {{ $nbPanier }}
                        </span>
                    @else
                        <span id="badge-panier"
                            class="hidden absolute -top-2 -right-3 bg-amber-ca text-white text-xs
                                w-4 h-4 rounded-full flex items-center justify-center font-semibold">
                            0
                        </span>
                    @endif
                </a>
                
                   {{-- Menu compte --}}
                    <div class="relative" id="menu-compte-wrapper">
                        <button type="button" id="btn-menu-compte" onclick="toggleMenuCompte()"
                                aria-haspopup="true" aria-expanded="false" aria-controls="menu-compte-dropdown"
                                class="text-xs text-white bg-primary px-3 py-1.5
                                       rounded-lg hover:bg-primary-light flex items-center gap-1">
                            {{ Auth::user()->prenom }} ▾
                        </button>
                        
                        <div id="menu-compte-dropdown" class="absolute right-0 top-full mt-1 w-44 bg-white border
                                    border-primary-pale rounded-xl shadow-lg hidden z-50">
                            <a href="{{ route('client.dashboard') }}"
                                class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-primary-bg
                                    font-medium">
                                <x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Mon espace
                            </a>
                            <a href="{{ route('client.commandes') }}"
                                class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-primary-bg">
                                Mes commandes
                            </a>
                            <a href="{{ route('client.favoris') }}"
                                class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-primary-bg">
                                Mes favoris
                            </a>
                            <a href="{{ route('client.compte') }}"
                                class="block px-4 py-2.5 text-xs text-gray-700 hover:bg-primary-bg">
                                Mon compte
                            </a>
                            <div class="border-t border-primary-pale">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2.5 text-xs
                                               text-red-500 hover:bg-red-50">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="text-xs text-primary-pale hover:text-white">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                        class="text-xs bg-white text-primary-dark px-3 py-1.5
                               rounded-lg hover:bg-primary-pale font-medium">
                        Créer un compte
                    </a>
                @endauth
            </div>

            {{-- Bouton hamburger (mobile uniquement) --}}
            <button type="button" id="btn-menu-mobile" onclick="toggleMenuMobile()"
                aria-haspopup="true" aria-expanded="false" aria-controls="menu-mobile-panel"
                aria-label="Ouvrir le menu"
                class="md:hidden text-primary-pale hover:text-white leading-none px-1">
                <x-heroicon-o-bars-3 class="w-6 h-6" />
            </button>
        </div>

        {{-- Panneau mobile : reprend recherche + navigation + actions,
             masqué par défaut, affiché uniquement sur petit écran.
             Positionné en superposition (absolute) pour ne jamais
             pousser le contenu de la page — sinon l'ouverture du menu
             agrandit visuellement le header. --}}
        <div id="menu-mobile-panel"
            class="hidden md:hidden absolute top-full left-0 right-0 z-50
                   bg-primary-dark border-t border-white/10 px-6 py-4 space-y-4
                   max-h-[calc(100vh-3.25rem)] overflow-y-auto shadow-lg">
            <form action="{{ route('client.catalogue') }}" method="GET" class="flex items-center gap-2">
                <label for="recherche-nav-mobile" class="sr-only">Rechercher un article</label>
                <input id="recherche-nav-mobile" type="text" name="search" value="{{ request('search') }}"
                    placeholder="Rechercher un article…"
                    class="flex-1 border border-white/20 rounded-lg px-3 py-2 text-xs
                           bg-white/10 text-white placeholder-white/50
                           focus:outline-none focus:border-white/50">
                <button type="submit" class="bg-primary text-white px-3 py-2 rounded-lg text-xs hover:bg-primary-light">
                    Rechercher
                </button>
            </form>

            <div class="flex flex-col gap-1">
                <a href="{{ route('client.catalogue') }}" class="text-sm text-primary-pale hover:text-white py-1.5">Catalogue</a>
                <span class="text-sm text-primary-pale/70 py-1.5 mt-1">Catégories</span>
                <div class="flex flex-col gap-0.5 pl-3 border-l border-white/10">
                    @forelse($categoriesMenu ?? [] as $categorie)
                    <a href="{{ route('client.catalogue', ['categorie' => $categorie->idCategorie]) }}"
                        class="text-sm text-primary-pale hover:text-white py-1">
                        {{ $categorie->nomCategorie }}
                    </a>
                    @empty
                    <span class="text-sm text-primary-pale/50 py-1">Aucune catégorie</span>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-col gap-1 border-t border-white/10 pt-3">
                @auth
                    <a href="{{ route('client.dashboard') }}" class="text-sm text-primary-pale hover:text-white py-1.5"><x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Mon espace</a>
                    <a href="{{ route('client.commandes') }}" class="text-sm text-primary-pale hover:text-white py-1.5">Mes commandes</a>
                    <a href="{{ route('client.favoris') }}" class="text-sm text-primary-pale hover:text-white py-1.5"><x-heroicon-o-heart class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Mes favoris</a>
                    <a href="{{ route('client.panier') }}" class="text-sm text-primary-pale hover:text-white py-1.5"><x-heroicon-o-shopping-cart class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Panier</a>
                    <a href="{{ route('client.compte') }}" class="text-sm text-primary-pale hover:text-white py-1.5">Mon compte</a>
                    <a href="{{ route('client.notifications') }}" class="text-sm text-primary-pale hover:text-white py-1.5"><x-heroicon-o-bell class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Notifications</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-300 hover:text-red-100 py-1.5">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-primary-pale hover:text-white py-1.5">Connexion</a>
                    <a href="{{ route('register') }}" class="text-sm text-primary-pale hover:text-white py-1.5">Créer un compte</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-primary-pale text-primary-dark text-sm px-6 py-3 text-center" role="status">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 text-red-700 text-sm px-6 py-3 text-center" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- CONTENU --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-primary-dark mt-12">
        <div class="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
            <span class="text-primary-pale text-xs">
                © {{ date('Y') }} ClaireAfrique — Dakar, Sénégal
            </span>
            <div class="flex gap-6">
                <a href="{{ route('legal.cgu') }}" class="text-primary-pale text-xs hover:text-white">Conditions d'utilisation</a>
                <span class="text-primary-pale text-xs">Contact</span>
                <a href="{{ route('legal.confidentialite') }}" class="text-primary-pale text-xs hover:text-white">Confidentialité</a>
            </div>
        </div>
    </footer>
    {{-- TOAST NOTIFICATION --}}
<div id="toast"
    class="fixed bottom-6 right-6 bg-primary-dark text-white text-sm px-5 py-3
           rounded-xl shadow-lg transform translate-y-20 opacity-0 transition-all
           duration-300 z-50 flex items-center gap-2">
    <span id="toast-icon">
        <x-heroicon-o-check class="toast-icone-succes w-4 h-4" />
        <x-heroicon-o-x-mark class="toast-icone-erreur w-4 h-4 hidden" />
    </span>
    <span id="toast-msg"></span>
</div>

@includeIf('shared._cookie-banner')

<script>
// ── Menu mobile (hamburger) ──────────────────────────────────────────
function toggleMenuMobile() {
    const panel = document.getElementById('menu-mobile-panel');
    const btn   = document.getElementById('btn-menu-mobile');
    const ouvert = !panel.classList.contains('hidden');
    panel.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', ouvert ? 'false' : 'true');
}

// ── Menu "Mon compte" (accessible au clic, pas seulement au survol —
// group-hover ne fonctionne pas sur tactile) ────────────────────────
function toggleMenuCompte() {
    const menu = document.getElementById('menu-compte-dropdown');
    const btn  = document.getElementById('btn-menu-compte');
    const ouvert = !menu.classList.contains('hidden');
    menu.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', ouvert ? 'false' : 'true');
}
document.addEventListener('click', function (e) {
    const wrapper = document.getElementById('menu-compte-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('menu-compte-dropdown')?.classList.add('hidden');
        document.getElementById('btn-menu-compte')?.setAttribute('aria-expanded', 'false');
    }
});

// ── Toast ──────────────────────────────────────────────────────────
function afficherToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const iconeSucces = document.querySelector('.toast-icone-succes');
    const iconeErreur = document.querySelector('.toast-icone-erreur');
    const text  = document.getElementById('toast-msg');

    text.textContent = msg;
    iconeSucces?.classList.toggle('hidden', type === 'error');
    iconeErreur?.classList.toggle('hidden', type !== 'error');
    toast.classList.toggle('bg-red-700', type === 'error');
    toast.classList.toggle('bg-primary-dark', type !== 'error');

    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
        toast.classList.remove('translate-y-0', 'opacity-100');
    }, 3000);
}

// ── Ajouter au panier AJAX ─────────────────────────────────────────
function ajouterPanier(btn, idArticle) {
    const token = document.querySelector('meta[name="csrf-token"]').content;

    btn.disabled = true;
    btn.textContent = '…';

    fetch('/catalogue/panier/ajax', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ idArticle, quantite: 1 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            afficherToast(data.message);
            // Mettre à jour le badge panier
            const badge = document.getElementById('badge-panier');
            if (badge) {
                badge.textContent = data.nbArticles;
                badge.classList.remove('hidden');
            }
        } else {
            afficherToast(data.message, 'error');
        }
    })
    .catch(() => afficherToast('Erreur réseau, réessayez.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = '+';
    });
}

// ── Debounce mise à jour quantité panier ───────────────────────────
let debounceTimer = null;
function debounceUpdate(idLigne, quantite) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => mettreAJourQuantite(idLigne, quantite), 800);
}

function changerQte(idLigne, delta, btn) {
    const input = document.getElementById('qte-' + idLigne);
    const nouvelleQte = Math.max(1, parseInt(input.value) + delta);
    input.value = nouvelleQte;
    debounceUpdate(idLigne, nouvelleQte);
}

function mettreAJourQuantite(idLigne, quantite) {
    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/catalogue/panier/${idLigne}/ajax`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quantite: parseInt(quantite) })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            afficherToast('Quantité mise à jour');
            // Mettre à jour le sous-total et le total
            const sousTotal = document.getElementById('sous-total-' + idLigne);
            const total     = document.getElementById('total-panier');
            if (sousTotal) sousTotal.textContent = data.sousTotal;
            if (total)     total.textContent     = data.total;
        } else {
            afficherToast(data.message, 'error');
            // Remettre l'ancienne valeur
            const input = document.getElementById('qte-' + idLigne);
            if (input) input.value = data.quantite;
        }
    })
    .catch(() => afficherToast('Erreur réseau : la quantité n\'a peut-être pas été mise à jour.', 'error'));
}

// ── Toggle favori AJAX ─────────────────────────────────────────────
function toggleFavori(btn, idArticle) {
    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/catalogue/favoris/${idArticle}/ajax`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token }
    })
    .then(r => r.json())
    .then(data => {
        // Les deux icônes (pleine / vide) sont déjà rendues côté serveur
        // (voir carte-article.blade.php) : on bascule juste laquelle est
        // visible. On ne peut pas injecter un composant Blade via du
        // JavaScript — un composant Blade ne se compile que côté serveur,
        // jamais dans le navigateur.
        const wrap   = btn.querySelector('.favori-icone-wrap');
        const pleine = btn.querySelector('.favori-icone-pleine');
        const vide   = btn.querySelector('.favori-icone-vide');

        if (data.statut === 'ajoute') {
            if (wrap) {
                wrap.classList.remove('text-gray-300', 'text-gray-400');
                wrap.classList.add('text-red-500');
            }
            pleine?.classList.remove('hidden');
            vide?.classList.add('hidden');
            btn.setAttribute('aria-pressed', 'true');
            afficherToast('Ajouté aux favoris');
        } else {
            if (wrap) {
                wrap.classList.remove('text-red-500');
                wrap.classList.add('text-gray-300');
            }
            pleine?.classList.add('hidden');
            vide?.classList.remove('hidden');
            btn.setAttribute('aria-pressed', 'false');
            afficherToast('Retiré des favoris');
        }
    })
    .catch(() => afficherToast('Erreur réseau, réessayez.', 'error'));
}
</script>

@stack('scripts')

</body>
</html>