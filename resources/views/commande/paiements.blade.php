@extends('layouts.commande')
@section('title', 'Paiements')
@section('page_title', 'Vérification des paiements')

@section('content')

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3 flex-wrap">
    <select name="statut"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous les statuts</option>
        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
        <option value="valide"     {{ request('statut') == 'valide'     ? 'selected' : '' }}>Validé</option>
        <option value="echoue"     {{ request('statut') == 'echoue'     ? 'selected' : '' }}>Échoué</option>
        <option value="rembourse"  {{ request('statut') == 'rembourse'  ? 'selected' : '' }}>Remboursé</option>
    </select>
    <select name="mode"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous les modes</option>
        <option value="wave"         {{ request('mode') == 'wave'         ? 'selected' : '' }}>Wave</option>
        <option value="orange_money" {{ request('mode') == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
        <option value="especes"      {{ request('mode') == 'especes'      ? 'selected' : '' }}>Espèces</option>
    </select>
    <x-filtre-date />
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('commande.paiements') }}"
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
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Reçu le</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Mode</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Montant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Référence</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($paiements as $paiement)
            @php
                $pColors = [
                    'en_attente' => 'bg-yellow-100 text-yellow-700',
                    'valide'     => 'bg-primary-pale text-primary-dark',
                    'echoue'     => 'bg-red-100 text-red-600',
                    'rembourse'  => 'bg-gray-100 text-gray-600',
                ];
                $pLabels = [
                    'en_attente' => 'En attente',
                    'valide'     => 'Validé',
                    'echoue'     => 'Échoué',
                    'rembourse'  => 'Remboursé',
                ];
            @endphp
            <tr class="hover:bg-primary-bg
                {{ $paiement->statutPaiement === 'en_attente' ? 'bg-yellow-50' : '' }}">
                <td class="px-4 py-3 text-xs font-medium text-primary-dark">
                    <a href="{{ route('commande.detail', $paiement->commande->idCommande) }}" class="hover:underline">
                        {{ $paiement->commande->numeroCommande }}
                    </a>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                    {{ $paiement->datePaiement->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">
                    {{ $paiement->commande->utilisateur->prenom }}
                    {{ $paiement->commande->utilisateur->nom }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ strtoupper(str_replace('_', ' ', $paiement->modePaiement)) }}
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-primary-dark">
                    {{ number_format($paiement->montant, 0, ',', ' ') }} F
                </td>
                <td class="px-4 py-3 text-xs font-mono text-gray-500">
                    {{ $paiement->referenceTransaction ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                 {{ $pColors[$paiement->statutPaiement] ?? '' }}">
                        {{ $pLabels[$paiement->statutPaiement] ?? '' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @if($paiement->statutPaiement === 'en_attente')
                    <form method="POST"
                        action="{{ route('commande.paiement.update', $paiement->idPaiement) }}"
                        class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="statutPaiement"
                            class="border border-gray-200 rounded px-2 py-1 text-xs bg-white">
                            {{-- Un paiement en ligne (Wave/Orange Money) ne peut jamais être
                                 validé à la main : seule la confirmation PayDunya fait foi
                                 (voir CommandePaiementService::mettreAJourStatut). Le proposer
                                 dans ce menu ouvrait la porte à des commandes "payées"
                                 gratuitement par n'importe quel compte res.commande. --}}
                            @if($paiement->modePaiement === 'especes')
                            <option value="valide">Valider</option>
                            @endif
                            <option value="echoue">Échoué</option>
                        </select>
                        <button type="submit"
                            class="bg-primary text-white px-3 py-1 rounded text-xs
                                   hover:bg-primary-light shrink-0">
                            OK
                        </button>
                    </form>
                    @else
                        <span class="text-xs text-gray-300 italic">Finalisé</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucun paiement trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $paiements->withQueryString()->links() }}</div>

@endsection