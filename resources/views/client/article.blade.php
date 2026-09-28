@extends('layouts.client')
@section('title', $article->designation . ' — Clairafrique')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Fil d'Ariane --}}
    <nav class="text-xs text-gray-500 mb-8 flex items-center gap-2 overflow-x-auto whitespace-nowrap pb-2 scrollbar-none">
        <a href="{{ route('home') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/></svg>
            Accueil
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('client.catalogue') }}" class="hover:text-primary transition-colors">Catalogue</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('client.catalogue') }}?categorie={{ $article->idCategorie }}" class="hover:text-primary transition-colors">
            {{ $article->categorie->nomCategorie }}
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-800 font-medium truncate max-w-[200px] sm:max-w-xs">{{ $article->designation }}</span>
    </nav>

    {{-- Contenu principal --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 mb-16 items-start">

        {{-- Visuel Article --}}
        <div class="md:col-span-5 sticky top-24">
            <div class="relative rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-gray-50 border border-gray-100 group aspect-[4/5]">
                @if($article->image)
                    <img src="{{ Storage::url($article->image) }}"
                         alt="{{ $article->designation }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-center p-8 bg-gradient-to-br from-emerald-900 to-emerald-700">
                        <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-4 text-white">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="text-white text-base font-semibold leading-snug max-w-xs drop-shadow-sm">
                            {{ $article->designation }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Détails Article --}}
        <div class="md:col-span-7 flex flex-col">

            {{-- Badge Catégorie --}}
            <div class="mb-3">
                <a href="{{ route('client.catalogue') }}?categorie={{ $article->idCategorie }}"
                   class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-emerald-100 transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $article->categorie->nomCategorie }}
                </a>
            </div>

            {{-- Titre & Réf --}}
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight leading-snug mb-2">
                {{ $article->designation }}
            </h1>
            
            {{-- Carte Prix & Stock --}}
            <div class="p-5 rounded-2xl bg-gray-50/80 border border-gray-100 mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-3xl sm:text-4xl font-extrabold text-emerald-950 tracking-tight">
                        {{ number_format($article->prix, 0, ',', ' ') }}
                    </span>
                    <span class="text-sm font-semibold text-gray-500 ml-1">F CFA</span>
                </div>

                {{-- Indication Stock --}}
                <div>
                    @if($article->quantiteStock > 0)
                        @if($article->seuilAlerte && $article->quantiteStock <= $article->seuilAlerte->quantiteMinimale)
                            <div class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200/60 rounded-xl px-3 py-1.5">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                <span class="text-xs font-semibold text-amber-800">
                                    Stock limité ({{ $article->quantiteStock }} restants)
                                </span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200/60 rounded-xl px-3 py-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-semibold text-emerald-800">
                                    En stock ({{ $article->quantiteStock }} disponibles)
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="inline-flex items-center gap-2 bg-rose-50 border border-rose-200/60 rounded-xl px-3 py-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="text-xs font-semibold text-rose-700">Rupture de stock</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($article->description)
            <div class="mb-8">
                <h3 class="text-xs uppercase tracking-wider font-bold text-gray-400 mb-2">Description</h3>
                <p class="text-sm text-gray-600 leading-relaxed font-normal">
                    {{ $article->description }}
                </p>
            </div>
            @endif

            {{-- Options de Livraison --}}
            <div class="border border-gray-100 rounded-2xl p-4 mb-8 bg-white shadow-sm space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0V7m0 4h4m-4 0H7"/></svg>
                    </div>
                    <div class="text-xs">
                        <span class="font-bold text-gray-800 block">Retrait en boutique</span>
                        <span class="text-emerald-600 font-medium">Gratuit à la librairie</span>
                    </div>
                </div>
                <hr class="border-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    </div>
                    <div class="text-xs">
                        <span class="font-bold text-gray-800 block">Livraison à domicile</span>
                        <span class="text-gray-500">{{ number_format(config('claireafrique.frais_livraison_domicile', 1500), 0, ',', ' ') }} F CFA</span>
                    </div>
                </div>
            </div>

            {{-- Actions d'achat --}}
            @if($article->quantiteStock > 0)
                @auth
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    {{-- Sélecteur Quantité --}}
                    <div class="flex items-center border border-gray-200 rounded-2xl p-1 bg-gray-50/50 w-full sm:w-auto">
                        <button type="button" id="btn-moins" onclick="changerQteDetail(-1)"
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white shadow-sm text-gray-600 hover:bg-gray-100 transition-colors font-bold text-lg">
                            −
                        </button>
                        <input type="number" id="qte-detail" value="{{ $quantiteAuPanier > 0 ? $quantiteAuPanier : 1 }}" min="1" max="{{ $article->quantiteStock }}"
                               class="w-12 text-center text-sm font-bold bg-transparent border-none focus:ring-0 focus:outline-none">
                        <button type="button" onclick="changerQteDetail(1)"
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white shadow-sm text-gray-600 hover:bg-gray-100 transition-colors font-bold text-lg">
                            +
                        </button>
                    </div>
                    @if($quantiteAuPanier > 0)
                    <p class="text-xs text-gray-400 -mt-2 mb-2">
                        Déjà {{ $quantiteAuPanier }} dans votre panier — ajustez puis "Mettre à jour le panier".
                    </p>
                    @endif

                    {{-- Bouton Panier --}}
                    <button id="btn-panier" onclick="ajouterPanierDetail({{ $article->idArticle }})"
                            class="flex-1 bg-emerald-800 hover:bg-emerald-900 text-white font-semibold py-3.5 px-6 rounded-2xl transition-all shadow-sm hover:shadow-md text-sm flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span id="btn-panier-label">{{ $quantiteAuPanier > 0 ? 'Mettre à jour le panier' : 'Ajouter au panier' }}</span>
                    </button>

                    {{-- Bouton Favoris --}}
                    
                    <button onclick="toggleFavori(this, {{ $article->idArticle }})"
                        aria-label="{{ $estFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                        aria-pressed="{{ $estFavori ? 'true' : 'false' }}"
                        class="w-full sm:w-12 h-12 rounded-2xl border flex items-center justify-center transition-all shrink-0 bg-white group
                            {{ $estFavori ? 'border-red-200' : 'border-gray-200 hover:border-red-200' }}">
                        <span class="favori-icone-wrap transition-transform duration-300 group-hover:scale-110
                                    {{ $estFavori ? 'text-red-500' : 'text-gray-400 group-hover:text-red-500' }}">
                            <x-heroicon-s-heart class="favori-icone-pleine w-6 h-6 {{ $estFavori ? '' : 'hidden' }}" />
                            <x-heroicon-o-heart class="favori-icone-vide w-6 h-6 {{ $estFavori ? 'hidden' : '' }}" />
                        </span>
                    </button>
                </div>
                @else
                <div class="mb-6">
                    <a href="{{ route('login') }}"
                       class="w-full block bg-emerald-800 hover:bg-emerald-900 text-white font-semibold py-3.5 px-6 rounded-2xl transition-colors text-sm text-center shadow-sm">
                        Connectez-vous pour commander
                    </a>
                </div>
                @endauth
            @else
                <button disabled class="w-full bg-gray-100 text-gray-400 font-semibold py-3.5 rounded-2xl text-sm cursor-not-allowed mb-6">
                    Article indisponible
                </button>
            @endif

            {{-- Moyen de paiements --}}
            <div class="flex items-center gap-2 pt-6 border-t border-gray-100 flex-wrap">
                <span class="text-xs font-medium text-gray-400 mr-2">Paiement sécurisé :</span>
                <span class="text-[11px] bg-sky-50 text-sky-700 px-2.5 py-1 rounded-lg font-semibold border border-sky-100">Wave</span>
                <span class="text-[11px] bg-orange-50 text-orange-700 px-2.5 py-1 rounded-lg font-semibold border border-orange-100">Orange Money</span>
                <span class="text-[11px] bg-gray-100 text-gray-700 px-2.5 py-1 rounded-lg font-semibold border border-gray-200">Espèces</span>
            </div>

        </div>
    </div>

    {{-- Articles similaires --}}
    @if($similaires->count() > 0)
    <div class="pt-8 border-t border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Articles similaires</h2>
            <a href="{{ route('client.catalogue') }}?categorie={{ $article->idCategorie }}" class="text-xs font-semibold text-primary hover:underline">Voir plus <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-6">
            @foreach($similaires as $sim)
                <x-carte-article
                    :article="$sim"
                    :estFavori="in_array($sim->idArticle, $favorisIds)" />
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
function changerQteDetail(delta) {
    const input = document.getElementById('qte-detail');
    const max = parseInt(input.max);
    const nouvelleQte = Math.max(1, Math.min(max, parseInt(input.value) + delta));
    input.value = nouvelleQte;
}

function ajouterPanierDetail(idArticle) {
    const quantite = parseInt(document.getElementById('qte-detail').value);
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const btn = document.getElementById('btn-panier');
    const label = document.getElementById('btn-panier-label');
    const labelDepart = label.textContent;

    btn.disabled = true;
    btn.classList.add('opacity-75');
    btn.innerHTML = '<span class="animate-spin">⏳</span> <span id="btn-panier-label">Mise à jour...</span>';

    // Définit la quantité totale de la ligne (pas une addition à
    // l'existant) : ce sélecteur exprime "je veux X au total", pas
    // "ajoute X de plus" (contrairement au bouton "+" rapide de la
    // grille catalogue, qui lui accumule).
    fetch('/catalogue/panier/definir/ajax', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ idArticle, quantite })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.classList.remove('opacity-75');
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg> <span id="btn-panier-label">Mettre à jour le panier</span>';

        if (data.success) {
            afficherToast(data.message);
            const badge = document.getElementById('badge-panier');
            if (badge) {
                badge.textContent = data.nbArticles;
                badge.classList.remove('hidden');
            }
        } else {
            afficherToast(data.message, 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.classList.remove('opacity-75');
        btn.innerHTML = labelDepart;
    });
}
</script>
@endsection