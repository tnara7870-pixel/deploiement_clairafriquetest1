@extends('layouts.stock')
@section('title', 'Alertes stock')
@section('page_title', 'Alertes de stock')

@section('content')

@if($alertes->count() > 0)
<div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5 flex items-center gap-3">
    <span class="text-red-500 text-lg"><x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /></span>
    <span class="text-sm text-red-700 font-medium">
        {{ $alertes->total() }} article(s) en dessous du seuil d'alerte — action requise.
    </span>
</div>
@endif

<div class="grid grid-cols-2 gap-4">

    {{-- Articles en alerte --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale">
            <span class="text-sm font-semibold text-primary-dark">
                Articles en alerte ({{ $alertes->total() }})
            </span>
        </div>
        <div class="divide-y divide-primary-pale">
            @forelse($alertes as $seuil)
            @php
                $stock = $pointVente ? $seuil->article->stockPour($pointVente) : $seuil->article->quantiteStock;
                $min   = $seuil->quantiteMinimale;
                $isCritique = $stock == 0;
            @endphp
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                    {{ $isCritique ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600' }}">
                    @if($isCritique)<x-heroicon-o-fire class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />@else<x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />@endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-800 truncate">
                        {{ $seuil->article->designation }}
                    </div>
                    <div class="text-xs text-gray-400">
                        Stock : <span class="font-semibold
                            {{ $isCritique ? 'text-red-600' : 'text-yellow-600' }}">
                            {{ $stock }}
                        </span>
                        — Seuil : {{ $min }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $isCritique ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $isCritique ? 'Rupture' : 'Stock bas' }}
                    </span>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-gray-400">
                <x-heroicon-o-check-circle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Aucun article en alerte
            </div>
            @endforelse
        </div>
        @if($alertes->hasPages())
        <div class="px-4 py-3 border-t border-primary-pale">
            {{ $alertes->links() }}
        </div>
        @endif
    </div>

    {{-- Définir des seuils --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale">
            <span class="text-sm font-semibold text-primary-dark">
                Définir un seuil d'alerte
            </span>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('stock.alerte.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="recherche-article-seuil" class="block text-xs text-gray-500 mb-1">Article *</label>
                    <input type="text" id="recherche-article-seuil" list="liste-articles-seuil"
                        placeholder="Tapez pour rechercher un article…" autocomplete="off"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-white"
                        oninput="document.getElementById('idArticleSeuilChoisi').value = (this.list.querySelector(`option[value=&quot;${CSS.escape(this.value)}&quot;]`) || {}).dataset?.id || '';">
                    <datalist id="liste-articles-seuil">
                        @foreach($tousArticles as $art)
                            <option data-id="{{ $art->idArticle }}"
                                value="{{ $art->designation }} (seuil actuel : {{ $art->seuilAlerte?->quantiteMinimale ?? 'non défini' }})">
                            </option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="idArticle" id="idArticleSeuilChoisi" required>
                    <p class="text-xs text-gray-400 mt-1">Commencez à taper le nom de l'article, puis sélectionnez-le dans la liste.</p>
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">
                        Quantité minimale *
                    </label>
                    <input type="number" name="quantiteMinimale" min="0" required
                        placeholder="Ex : 10"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                    <p class="text-xs text-gray-400 mt-1">
                        Une alerte sera déclenchée quand le stock atteint cette valeur.
                    </p>
                </div>
                <button type="submit"
                    class="w-full bg-primary text-white py-2 rounded-lg text-sm
                           font-medium hover:bg-primary-light">
                    Enregistrer le seuil
                </button>
            </form>
        </div>

        {{-- Liste des seuils définis --}}
        <div class="border-t border-primary-pale">
            <div class="px-4 py-2 text-xs font-semibold text-gray-400 bg-primary-bg">
                Seuils définis
            </div>
            <div class="divide-y divide-primary-pale">
                @forelse($seuilsDefinis as $art)
                <div class="flex items-center justify-between px-4 py-2">
                    <div>
                        <div class="text-xs font-medium text-gray-700 truncate max-w-xs">
                            {{ $art->designation }}
                        </div>
                        <div class="text-xs text-gray-400">
                            Seuil : {{ $art->seuilAlerte->quantiteMinimale }}
                        </div>
                    </div>
                    <form method="POST"
                        action="{{ route('stock.alerte.toggle', $art->seuilAlerte->idSeuil) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs {{ $art->seuilAlerte->estActif ? 'text-red-400' : 'text-primary' }} hover:underline">
                            {{ $art->seuilAlerte->estActif ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-xs text-gray-400">
                    Aucun seuil défini pour le moment.
                </div>
                @endforelse
            </div>
            @if($seuilsDefinis->hasPages())
            <div class="px-4 py-2 border-t border-primary-pale">
                {{ $seuilsDefinis->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection