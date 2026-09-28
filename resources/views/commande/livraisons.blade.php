@extends('layouts.commande')
@section('title', 'Livraisons')
@section('page_title', 'Suivi des livraisons')

@section('content')

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3 flex-wrap">
    <select name="statut"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous les statuts</option>
        <option value="preparee"     {{ request('statut') == 'preparee'     ? 'selected' : '' }}>Préparée</option>
        <option value="en_livraison" {{ request('statut') == 'en_livraison' ? 'selected' : '' }}>En livraison</option>
        <option value="livree"       {{ request('statut') == 'livree'       ? 'selected' : '' }}>Livrée</option>
        <option value="annulee"   {{ request('statut') == 'annulee'   ? 'selected' : '' }}>Annulée</option>
    </select>
    <select name="mode"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous modes</option>
        <option value="domicile" {{ request('mode') == 'domicile' ? 'selected' : '' }}>Domicile</option>
        <option value="boutique" {{ request('mode') == 'boutique' ? 'selected' : '' }}>Boutique</option>
    </select>
    <x-filtre-date />
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('commande.livraisons') }}"
        class="border border-gray-200 text-gray-500 px-4 h-9 flex items-center
               rounded-lg text-sm hover:bg-gray-50 shrink-0">
        Réinitialiser
    </a>
</form>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">N° Commande</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Commandée le</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Mode</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Adresse / Point de vente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Mettre à jour</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($livraisons as $livraison)
            @php
                $lColors = [
                    'preparee'     => 'bg-gray-100 text-gray-600',
                    'en_livraison' => 'bg-blue-100 text-blue-700',
                    'livree'       => 'bg-primary-pale text-primary-dark',
                    'annulee'      => 'bg-red-100 text-red-600',
                ];
                $lLabels = [
                    'preparee'     => 'Préparée',
                    'en_livraison' => 'En livraison',
                    'livree'       => 'Livrée',
                    'annulee'      => 'Annulée',
                ];
            @endphp
            <tr class="hover:bg-primary-bg {{ $livraison->statutLivraison === 'preparee' ? 'bg-yellow-50' : '' }}">
                <td class="px-4 py-3 text-xs font-medium text-primary-dark">
                    <a href="{{ route('commande.detail', $livraison->commande->idCommande) }}" class="hover:underline">
                        {{ $livraison->commande->numeroCommande }}
                    </a>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($livraison->commande->dateCommande)->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">
                    {{ $livraison->commande->utilisateur->prenom }}
                    {{ $livraison->commande->utilisateur->nom }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    @if($livraison->modeLivraison === 'domicile')<x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Domicile @else<x-heroicon-o-building-storefront class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Boutique @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">
                    @if($livraison->modeLivraison === 'boutique')
                        {{ $livraison->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                    @else
                        {{ $livraison->adresseLivraison ?? '—' }}
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                 {{ $lColors[$livraison->statutLivraison] ?? '' }}">
                        {{ $lLabels[$livraison->statutLivraison] ?? '' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @if(!in_array($livraison->statutLivraison, ['livree','annulee']))
                    <form method="POST"
                        action="{{ route('commande.livraison.update', $livraison->idLivraison) }}"
                        class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="statutLivraison"
                            class="border border-gray-200 rounded px-2 py-1 text-xs bg-white">
                            <option value="preparee"     {{ $livraison->statutLivraison == 'preparee'     ? 'selected' : '' }}>Préparée</option>
                            @if($livraison->modeLivraison !== 'boutique')
                            <option value="en_livraison" {{ $livraison->statutLivraison == 'en_livraison' ? 'selected' : '' }}>En livraison</option>
                            @endif
                            <option value="livree"       {{ $livraison->statutLivraison == 'livree'       ? 'selected' : '' }}>Livrée</option>
                        </select>
                        <button type="submit"
                            class="bg-primary text-white px-3 py-1 rounded text-xs
                                   hover:bg-primary-light shrink-0">
                            OK
                        </button>
                    </form>
                    @else
                        <span class="text-xs text-gray-300 italic">Finalisée</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucune livraison trouvée.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $livraisons->withQueryString()->links() }}</div>

@endsection