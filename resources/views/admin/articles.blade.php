@extends('layouts.admin')
@section('title', 'Articles')
@section('page_title', 'Consulter les articles')

@section('content')

{{-- Filtres --}}
<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Rechercher par nom ou référence…"
        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:border-primary bg-primary-bg h-9">
    <select name="categorie"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Toutes catégories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->idCategorie }}"
                {{ request('categorie') == $cat->idCategorie ? 'selected' : '' }}>
                {{ $cat->nomCategorie }}
            </option>
        @endforeach
    </select>
    <select name="statut"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous statuts</option>
        <option value="actif"   {{ request('statut') == 'actif'   ? 'selected' : '' }}>Actif</option>
        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
    </select>
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm
               hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('admin.articles') }}"
        class="border border-gray-200 text-gray-500 px-4 h-9 flex items-center
               rounded-lg text-sm hover:bg-gray-50 shrink-0">
        Réinitialiser
    </a>
</form>

<div class="mb-4">
    <span class="text-sm text-gray-500">{{ $articles->total() }} article(s) trouvé(s)</span>
</div>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Référence</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Désignation</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Catégorie</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Prix</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Stock</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($articles as $article)
            <tr class="hover:bg-primary-bg">
                <td class="px-4 py-3 text-xs font-mono text-gray-500">
                    {{ $article->reference }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if($article->image)
                            <img src="{{ Storage::url($article->image) }}"
                                class="w-8 h-8 rounded object-cover">
                        @else
                            <div class="w-8 h-8 rounded bg-primary-pale flex items-center
                                        justify-center text-xs"><x-heroicon-o-cube class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></div>
                        @endif
                        <span class="text-sm font-medium text-gray-800">
                            {{ $article->designation }}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $article->categorie->nomCategorie }}
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-primary-dark">
                    {{ number_format($article->prix, 0, ',', ' ') }} F
                </td>
                <td class="px-4 py-3">
                    @if($article->seuilAlerte &&
                        $article->quantiteStock <= $article->seuilAlerte->quantiteMinimale)
                        <span class="text-xs font-semibold text-yellow-600">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $article->quantiteStock }}
                        </span>
                    @else
                        <span class="text-sm text-gray-700">
                            {{ $article->quantiteStock }}
                        </span>
                    @endif
                    <div class="text-[10px] text-gray-400">
                        UCAD : {{ $article->stockPour('ucad') }} · Centre-ville : {{ $article->stockPour('centre_ville') }}
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $article->statut
                            ? 'bg-primary-pale text-primary-dark'
                            : 'bg-red-100 text-red-600' }}">
                        {{ $article->statut ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucun article trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $articles->withQueryString()->links() }}
</div>

@endsection