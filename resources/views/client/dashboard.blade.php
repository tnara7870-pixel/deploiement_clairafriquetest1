@extends('layouts.client')
@section('title', 'Mon espace — ClaireAfrique')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-8">

    {{-- Bonjour --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-primary-dark">
            Bonjour, {{ $user->prenom }} <x-heroicon-o-hand-raised class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
        </h1>
        <p class="text-gray-400 text-sm mt-1">
            Bienvenue dans votre espace personnel ClaireAfrique
        </p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white border border-primary-pale rounded-2xl p-5">
            <div class="text-xs text-gray-400 mb-1">Total commandes</div>
            <div class="text-2xl font-bold text-primary-dark">{{ $totalCommandes }}</div>
            <a href="{{ route('client.commandes') }}"
                class="text-xs text-primary hover:underline mt-1 block">
                Voir mes commandes <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
            </a>
        </div>
        <div class="bg-white border border-primary-pale rounded-2xl p-5">
            <div class="text-xs text-gray-400 mb-1">Total dépensé</div>
            <div class="text-2xl font-bold text-primary-dark">
                {{ number_format($totalDepense, 0, ',', ' ') }} F
            </div>
            <div class="text-xs text-gray-400 mt-1">Commandes validées</div>
        </div>
        <div class="bg-white border border-primary-pale rounded-2xl p-5">
            <div class="text-xs text-gray-400 mb-1">En cours de livraison</div>
            <div class="text-2xl font-bold
                {{ $commandesEnCours > 0 ? 'text-yellow-500' : 'text-primary-dark' }}">
                {{ $commandesEnCours }}
            </div>
            <div class="text-xs text-gray-400 mt-1">
                {{ $commandesEnCours > 0 ? 'Commande(s) en transit' : 'Aucune en cours' }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">

        {{-- Dernières commandes --}}
        <div class="bg-white border border-primary-pale rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-primary-pale flex justify-between items-center">
                <h2 class="text-sm font-semibold text-primary-dark">
                    Dernières commandes
                </h2>
                <a href="{{ route('client.commandes') }}"
                    class="text-xs text-primary hover:underline">Tout voir <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
            </div>
            <div class="divide-y divide-primary-pale">
                @forelse($dernieresCommandes as $cmd)
                @php
                    $statutColors = [
                        'en_attente'   => 'bg-yellow-100 text-yellow-700',
                        'validee'      => 'bg-primary-pale text-primary-dark',
                        'en_livraison' => 'bg-blue-100 text-blue-700',
                        'livree'       => 'bg-primary-pale text-primary',
                        'annulee'      => 'bg-red-100 text-red-600',
                    ];
                    $statutLabels = [
                        'en_attente'   => 'En attente',
                        'validee'      => 'Validée',
                        'en_livraison' => 'En livraison',
                        'livree'       => 'Livrée',
                        'annulee'      => 'Annulée',
                    ];
                @endphp
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-medium text-primary-dark">
                            {{ $cmd->numeroCommande }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($cmd->dateCommande)->format('d/m/Y') }}
                            · {{ number_format($cmd->montantTotal, 0, ',', ' ') }} F
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                     {{ $statutColors[$cmd->statut] ?? '' }}">
                            {{ $statutLabels[$cmd->statut] ?? $cmd->statut }}
                        </span>
                        <a href="{{ route('client.commande.detail', $cmd->idCommande) }}"
                            class="text-xs text-primary hover:underline"><x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-xs text-gray-400">
                    Aucune commande pour l'instant.
                    <a href="{{ route('client.catalogue') }}"
                        class="text-primary block mt-1 hover:underline">
                        Découvrir le catalogue
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Favoris --}}
        <div class="bg-white border border-primary-pale rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-primary-pale flex justify-between items-center">
                <h2 class="text-sm font-semibold text-primary-dark">Mes favoris</h2>
                <a href="{{ route('client.favoris') }}"
                    class="text-xs text-primary hover:underline">Tout voir <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
            </div>
            @if($favoris->isEmpty())
            <div class="px-5 py-8 text-center text-xs text-gray-400">
                Aucun favori pour l'instant.
                <a href="{{ route('client.catalogue') }}"
                    class="text-primary block mt-1 hover:underline">
                    Parcourir le catalogue
                </a>
            </div>
            @else
            <div class="grid grid-cols-2 gap-3 p-4">
                @foreach($favoris as $favori)
                <div class="bg-primary-bg rounded-xl overflow-hidden
                            hover:border-primary border border-transparent
                            transition-colors">
                    <div class="h-24 overflow-hidden"
                         style="background: linear-gradient(135deg, #1A4731, #2E7D52)">
                        @if($favori->article->image)
                            <img src="{{ Storage::url($favori->article->image) }}"
                                 alt="{{ $favori->article->designation }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center
                                        justify-center text-2xl"><x-heroicon-o-cube class="w-6 h-6 inline-block flex-shrink-0 align-[-3px]" /></div>
                        @endif
                    </div>
                    <div class="p-2">
                        <div class="text-xs font-medium text-gray-700 truncate">
                            {{ $favori->article->designation }}
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-xs font-bold text-primary-dark">
                                {{ number_format($favori->article->prix, 0, ',', ' ') }} F
                            </span>
                            <button onclick="ajouterPanier(this, {{ $favori->idArticle }})"
                                class="text-xs bg-primary text-white px-2 py-0.5
                                       rounded-lg hover:bg-primary-light">
                                +
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Raccourcis --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
        <a href="{{ route('client.catalogue') }}"
            class="bg-white border border-primary-pale rounded-2xl p-4 text-center
                   hover:border-primary hover:shadow-sm transition-all">
            <div class="text-2xl mb-2"><x-heroicon-o-shopping-bag class="w-6 h-6 inline-block flex-shrink-0 align-[-3px]" />️</div>
            <div class="text-xs font-medium text-gray-700">Catalogue</div>
        </a>
        <a href="{{ route('client.panier') }}"
            class="bg-white border border-primary-pale rounded-2xl p-4 text-center
                   hover:border-primary hover:shadow-sm transition-all">
            <div class="text-2xl mb-2"><x-heroicon-o-shopping-cart class="w-6 h-6 inline-block flex-shrink-0 align-[-3px]" /></div>
            <div class="text-xs font-medium text-gray-700">Mon panier</div>
        </a>
        <a href="{{ route('client.favoris') }}"
            class="bg-white border border-primary-pale rounded-2xl p-4 text-center
                   hover:border-primary hover:shadow-sm transition-all">
            <div class="text-2xl mb-2"><x-heroicon-s-heart class="w-6 h-6 inline-block flex-shrink-0 align-[-3px]" /></div>
            <div class="text-xs font-medium text-gray-700">Mes favoris</div>
        </a>
        <a href="{{ route('client.compte') }}"
            class="bg-white border border-primary-pale rounded-2xl p-4 text-center
                   hover:border-primary hover:shadow-sm transition-all">
            <div class="text-2xl mb-2"><x-heroicon-o-user class="w-6 h-6 inline-block flex-shrink-0 align-[-3px]" /></div>
            <div class="text-xs font-medium text-gray-700">Mon profil</div>
        </a>
    </div>
</div>
@endsection