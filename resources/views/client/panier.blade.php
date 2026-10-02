@extends('layouts.client')
@section('title', 'Mon panier')

@section('content')
@include('client._etapes-commande', ['etape' => 1])
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-lg font-semibold text-primary-dark mb-6">Mon panier</h1>

    @if(!$panier || $panier->lignePaniers->isEmpty())
        <div class="bg-white border border-primary-pale rounded-xl py-16 text-center">
            <div class="text-4xl mb-3"><x-heroicon-o-shopping-cart class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
            <p class="text-gray-400 text-sm mb-4">Votre panier est vide.</p>
            <a href="{{ route('client.catalogue') }}"
                class="bg-primary text-white px-5 py-2 rounded-lg text-sm hover:bg-primary-light">
                Voir le catalogue
            </a>
        </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Articles --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
                @foreach($panier->lignePaniers as $ligne)
                <div class="grid grid-cols-[3rem_minmax(0,1fr)_auto] sm:flex sm:items-center gap-x-3 gap-y-2 sm:gap-4 px-4 sm:px-5 py-4 border-b border-primary-pale last:border-0">
                    <div class="w-12 h-12 bg-primary-bg rounded-lg flex items-center
                                justify-center text-xl flex-shrink-0">
                        <x-heroicon-o-cube class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
                    </div>
                    <div class="col-start-2 row-start-1 min-w-0 sm:flex-1">
                        <div class="text-sm font-medium text-gray-800 break-words">
                            {{ $ligne->article->designation }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ number_format($ligne->article->prix, 0, ',', ' ') }} F / unité
                        </div>
                        {{-- Le point de retrait n'est choisi qu'à l'étape
                             suivante : cette info évite au client de
                             découvrir seulement au moment de valider que
                             l'article n'est pas disponible au point qu'il
                             s'apprêtait à choisir (retour utilisateur du
                             01/09/2026). --}}
                        <div class="text-[11px] text-gray-400 mt-0.5 break-words">
                            Disponible en boutique — UCAD :
                            <span class="{{ $ligne->article->stockPour('ucad') < $ligne->quantite ? 'text-red-500 font-medium' : '' }}">{{ $ligne->article->stockPour('ucad') }}</span>
                            · Centre-ville :
                            <span class="{{ $ligne->article->stockPour('centre_ville') < $ligne->quantite ? 'text-red-500 font-medium' : '' }}">{{ $ligne->article->stockPour('centre_ville') }}</span>
                        </div>
                    </div>

                    {{-- Modifier quantité --}}
                    <div class="col-start-2 row-start-2 mt-1 flex items-center gap-2 sm:col-auto sm:row-auto sm:mt-0">
                        <button type="button"
                            onclick="changerQte({{ $ligne->idLignePanier }}, -1, this)"
                            class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600
                                hover:bg-gray-50 flex items-center justify-center text-lg">
                            −
                        </button>
                        <input type="number"
                            id="qte-{{ $ligne->idLignePanier }}"
                            value="{{ $ligne->quantite }}"
                            min="1"
                            max="{{ $ligne->article->quantiteStock }}"
                            onchange="debounceUpdate({{ $ligne->idLignePanier }}, this.value)"
                            class="w-14 border border-gray-200 rounded-lg px-2 py-1 text-sm
                                text-center focus:outline-none focus:border-primary">
                        <button type="button"
                            onclick="changerQte({{ $ligne->idLignePanier }}, 1, this)"
                            class="w-7 h-7 rounded-lg border border-gray-200 text-gray-600
                                hover:bg-gray-50 flex items-center justify-center text-lg">
                            +
                        </button>
                    </div>

                     {{-- Sur chaque ligne, ajoute l'id sur le sous-total --}}
                <div class="col-start-3 row-start-2 w-auto text-right text-sm font-semibold text-primary-dark sm:w-20"
                    id="sous-total-{{ $ligne->idLignePanier }}">
                    {{ number_format($ligne->sousTotal(), 0, ',', ' ') }} F
                </div>

                    {{-- Supprimer --}}
                    <form method="POST"
                        action="{{ route('client.panier.supprimer', $ligne->idLignePanier) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 text-lg">
                            ×
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Récapitulatif + commande --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-primary-pale rounded-xl p-5 lg:sticky lg:top-20">
                <h3 class="text-sm font-semibold text-primary-dark mb-4">
                    Récapitulatif
                </h3>
               

                {{-- Sur le total général, ajoute l'id --}}
                <span class="text-sm font-semibold text-primary-dark"
                    id="total-panier">
                    {{ number_format($total, 0, ',', ' ') }} F
                </span>

                {{-- Formulaire commande --}}
                <a href="{{ route('client.recapitulatif') }}"
                    class="w-full bg-primary text-white py-3 rounded-lg text-sm font-semibold
                        hover:bg-primary-light text-center block">
                    Passer la commande <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function toggleAdresse(val) {
    const champ = document.getElementById('champ-adresse');
    if (val === 'domicile') {
        champ.classList.remove('hidden');
    } else {
        champ.classList.add('hidden');
    }
}
</script>
@endsection