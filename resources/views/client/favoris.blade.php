@extends('layouts.client')
@section('title', 'Mes favoris')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-lg font-semibold text-primary-dark mb-6">Mes favoris</h1>

    @if($favoris->isEmpty())
        <div class="bg-white border border-primary-pale rounded-xl py-16 text-center">
            <div class="text-4xl mb-3"><x-heroicon-o-heart class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
            <p class="text-gray-400 text-sm mb-4">Vous n'avez pas encore de favoris.</p>
            <a href="{{ route('client.catalogue') }}"
                class="bg-primary text-white px-5 py-2 rounded-lg text-sm hover:bg-primary-light">
                Parcourir le catalogue
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($favoris as $favori)
            <div class="bg-white border border-primary-pale rounded-xl overflow-hidden
                        hover:border-primary transition-colors">
                <div class="bg-primary-bg h-28 flex items-center justify-center text-3xl">
                    <x-heroicon-o-cube class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
                </div>
                <div class="p-3">
                    <div class="text-xs text-gray-400 mb-0.5">
                        {{ $favori->article->categorie->nomCategorie }}
                    </div>
                    <div class="text-sm font-medium text-gray-800 mb-2">
                        {{ $favori->article->designation }}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-primary-dark">
                            {{ number_format($favori->article->prix, 0, ',', ' ') }} F
                        </span>
                        <div class="flex gap-2">
                            <form method="POST"
                                action="{{ route('client.panier.ajouter') }}">
                                @csrf
                                <input type="hidden" name="idArticle"
                                    value="{{ $favori->idArticle }}">
                                <input type="hidden" name="quantite" value="1">
                                <button type="submit"
                                    class="bg-primary text-white px-2 py-1 rounded text-xs
                                           hover:bg-primary-light">
                                    + Panier
                                </button>
                            </form>
                            <form method="POST"
                                action="{{ route('client.favoris.toggle', $favori->idArticle) }}">
                                @csrf
                                <button type="submit"
                                    class="text-red-400 hover:text-red-600 text-xs border
                                           border-red-200 px-2 py-1 rounded hover:bg-red-50">
                                    <x-heroicon-s-heart class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retirer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection