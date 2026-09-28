@extends('layouts.admin')
@section('title', 'Mouvements stock')
@section('page_title', 'Historique des mouvements de stock')

@section('content')

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3 flex-wrap">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Rechercher un article…"
        class="flex-1 border border-gray-200 rounded-lg px-3 h-9 text-sm
               focus:outline-none focus:border-primary bg-primary-bg min-w-32">
    <select name="type"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous types</option>
        <option value="entree"     {{ request('type') == 'entree'     ? 'selected' : '' }}>Entrée</option>
        <option value="sortie"     {{ request('type') == 'sortie'     ? 'selected' : '' }}>Sortie</option>
        <option value="retour"     {{ request('type') == 'retour'     ? 'selected' : '' }}>Retour</option>
        <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustement</option>
    </select>
    <x-filtre-date />
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('admin.mouvements') }}"
        class="border border-gray-200 text-gray-500 px-4 h-9 flex items-center
               rounded-lg text-sm hover:bg-gray-50 shrink-0">
        Réinitialiser
    </a>
</form>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Article</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Quantité</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Motif</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Effectué par</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($mouvements as $mouv)
            <tr class="hover:bg-primary-bg">
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($mouv->dateMouvement)->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3">
                    @php
                        $typeColors = [
                            'entree'     => 'bg-primary-pale text-primary-dark',
                            'sortie'     => 'bg-red-100 text-red-600',
                            'retour'     => 'bg-blue-100 text-blue-600',
                            'ajustement' => 'bg-yellow-100 text-yellow-700',
                        ];
                        $typeLabels = [
                            'entree'     => 'Entrée',
                            'sortie'     => 'Sortie',
                            'retour'     => 'Retour',
                            'ajustement' => 'Ajustement',
                        ];
                    @endphp
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                                 {{ $typeColors[$mouv->typeMouvement] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $typeLabels[$mouv->typeMouvement] ?? $mouv->typeMouvement }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                    {{ $mouv->article->designation }}
                    @if($mouv->pointVente)
                        <span class="text-[10px] text-gray-400">({{ $mouv->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }})</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm font-semibold
                    {{ in_array($mouv->typeMouvement, ['entree','retour']) || $mouv->quantite > 0 ? 'text-primary' : 'text-red-500' }}">
                    {{-- "quantite" est toujours un delta signé (entree/retour positif,
                         sortie négatif, ajustement signé selon le sens réel constaté). --}}
                    {{ in_array($mouv->typeMouvement, ['entree','retour']) ? '+' : '' }}{{ $mouv->quantite }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $mouv->motif ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    @if($mouv->utilisateur)
                        {{ $mouv->utilisateur->prenom }} {{ $mouv->utilisateur->nom }}
                    @else
                        <span class="italic text-gray-400">Système (automatique)</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucun mouvement enregistré.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $mouvements->withQueryString()->links() }}</div>

@endsection