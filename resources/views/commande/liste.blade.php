@extends('layouts.commande')
@section('title', 'Commandes')
@section('page_title', 'Toutes les commandes')

@section('content')

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3 flex-wrap">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="N° commande, nom client…"
        class="flex-1 border border-gray-200 rounded-lg px-3 h-9 text-sm
               focus:outline-none focus:border-primary bg-primary-bg min-w-32">
    <select name="statut"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous les statuts commande</option>
        <option value="en_attente"   {{ request('statut') == 'en_attente'   ? 'selected' : '' }}>En attente</option>
        <option value="validee"      {{ request('statut') == 'validee'      ? 'selected' : '' }}>Validée</option>
        <option value="en_livraison" {{ request('statut') == 'en_livraison' ? 'selected' : '' }}>En livraison</option>
        <option value="livree"       {{ request('statut') == 'livree'       ? 'selected' : '' }}>Livrée</option>
        <option value="annulee"      {{ request('statut') == 'annulee'      ? 'selected' : '' }}>Annulée</option>
        <option value="demande_annulation" {{ request('statut') == 'demande_annulation' ? 'selected' : '' }}>Demande d'annulation</option>
    </select>
    <x-filtre-date />
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('commande.liste') }}"
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
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Montant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Mode de livraison</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Mode de paiement</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut commande</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($commandes as $cmd)
            @php
                $statutColors = [
                    'en_attente'         => 'bg-yellow-100 text-yellow-700',
                    'validee'            => 'bg-primary-pale text-primary-dark',
                    'en_livraison'       => 'bg-blue-100 text-blue-700',
                    'livree'             => 'bg-primary-pale text-primary',
                    'annulee'            => 'bg-red-100 text-red-600',
                    'demande_annulation' => 'bg-orange-100 text-orange-700',
                ];
                $statutLabels = \App\Models\Commande::libellesStatuts();
            @endphp
            <tr class="hover:bg-primary-bg
                {{ $cmd->statut === 'en_attente' ? 'bg-yellow-50' : '' }}
                {{ $cmd->statut === 'demande_annulation' ? 'bg-orange-50' : '' }}">
                <td class="px-4 py-3 text-xs font-medium text-primary-dark">
                    {{ $cmd->numeroCommande }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($cmd->dateCommande)->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">
                    {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-primary-dark">
                    {{ number_format($cmd->montantTotal, 0, ',', ' ') }} F
                </td>
                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                    @if($cmd->livraison?->modeLivraison === 'boutique')
                        <x-heroicon-o-building-storefront class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retrait
                        @if($cmd->livraison->pointVente)
                            <span class="text-gray-400">({{ $cmd->livraison->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }})</span>
                        @endif
                    @elseif($cmd->livraison?->modeLivraison === 'domicile')
                        <x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> À domicile
                    @else
                        —
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $cmd->paiement ? strtoupper(str_replace('_', ' ', $cmd->paiement->modePaiement)) : '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                 {{ $statutColors[$cmd->statut] ?? '' }}">
                        {{ $statutLabels[$cmd->statut] ?? $cmd->statut }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('commande.detail', $cmd->idCommande) }}"
                        class="text-xs text-primary hover:underline">
                        Voir <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucune commande trouvée.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $commandes->withQueryString()->links() }}</div>

@endsection