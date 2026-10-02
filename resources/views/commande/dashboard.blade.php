@extends('layouts.commande')
@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord — Commandes')

@section('content')

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('commande.liste', ['statut' => 'en_attente']) }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">En attente</div>
        <div class="text-2xl font-semibold {{ $enAttente > 0 ? 'text-yellow-500' : 'text-primary-dark' }}">
            {{ $enAttente }}
        </div>
        <div class="text-xs {{ $enAttente > 0 ? 'text-yellow-400' : 'text-primary' }} mt-1">
            @if($enAttente > 0)<x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> À traiter @else Tout est traité @endif
        </div>
    </a>
    <a href="{{ route('commande.livraisons') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Livraisons à préparer</div>
        <div class="text-2xl font-semibold text-primary-dark">{{ $livraisonsAPreparer }}</div>
        <div class="text-xs text-gray-400 mt-1">En attente d'expédition</div>
    </a>
    <a href="{{ route('commande.liste', ['statut' => 'livree']) }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Livrées ce mois</div>
        <div class="text-2xl font-semibold text-primary-dark">{{ $livreesMois }}</div>
        <div class="text-xs text-primary mt-1">{{ now()->locale('fr')->monthName }}</div>
    </a>
    <a href="{{ route('commande.paiements') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Paiements à vérifier</div>
        <div class="text-2xl font-semibold {{ $paiementsAVerifier > 0 ? 'text-red-500' : 'text-primary-dark' }}">
            {{ $paiementsAVerifier }}
        </div>
        <div class="text-xs {{ $paiementsAVerifier > 0 ? 'text-red-400' : 'text-primary' }} mt-1">
            {{ $paiementsAVerifier > 0 ? 'Action requise' : 'Tout est vérifié' }}
        </div>
    </a>
</div>

@if($demandesAnnulation->count() > 0)
<div class="bg-white border border-orange-200 rounded-xl overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-orange-100 bg-orange-50 flex justify-between items-center">
        <span class="text-sm font-semibold text-orange-700">
            <x-heroicon-o-clock class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $demandesAnnulation->count() }} demande(s) d'annulation en attente
        </span>
    </div>
    <div class="divide-y divide-primary-pale">
        @foreach($demandesAnnulation as $cmd)
        <a href="{{ route('commande.detail', $cmd->idCommande) }}"
           class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-orange-50 transition">
            <div class="min-w-0">
                <div class="text-xs font-medium text-gray-800">
                    {{ $cmd->numeroCommande }} — {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </div>
                <div class="text-xs text-gray-400 truncate max-w-md">
                    {{ $cmd->motifAnnulation ?? 'Aucun motif précisé' }}
                </div>
            </div>
            <span class="text-xs font-medium text-orange-600 flex-shrink-0">Examiner <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></span>
        </a>
        @endforeach
    </div>
</div>
@endif

@if($commandesProblemeStock->count() > 0)
<div class="bg-white border border-red-200 rounded-xl overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex justify-between items-center">
        <span class="text-sm font-semibold text-red-700">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $commandesProblemeStock->count() }} commande(s) payée(s) avec rupture de stock
        </span>
    </div>
    <div class="divide-y divide-primary-pale">
        @foreach($commandesProblemeStock as $cmd)
        <div class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-red-50 transition">
            <a href="{{ route('commande.detail', $cmd->idCommande) }}" class="min-w-0">
                <div class="text-xs font-medium text-gray-800">
                    {{ $cmd->numeroCommande }} — {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </div>
                <div class="text-xs text-gray-400 truncate max-w-md">
                    {{ $cmd->problemeStockDetails }}
                </div>
            </a>
            <form method="POST" action="{{ route('commande.problemeStock.resoudre', $cmd->idCommande) }}" class="flex-shrink-0">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs font-medium text-red-600 hover:underline">
                    Marquer comme traité
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Dernières commandes --}}
<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
        <span class="text-sm font-semibold text-primary-dark">Dernières commandes</span>
        <a href="{{ route('commande.liste') }}"
            class="text-xs text-primary hover:underline">Voir tout <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">N° Commande</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Montant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Paiement</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Livraison</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
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
            <tr class="hover:bg-primary-bg">
                <td class="px-4 py-3 text-xs font-medium text-primary-dark">
                    {{ $cmd->numeroCommande }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">
                    {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-primary-dark">
                    {{ number_format($cmd->montantTotal, 0, ',', ' ') }} F
                </td>
                <td class="px-4 py-3">
                    @if($cmd->paiement)
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
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                     {{ $pColors[$cmd->paiement->statutPaiement] ?? '' }}">
                            {{ $pLabels[$cmd->paiement->statutPaiement] ?? '' }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($cmd->livraison)
                        <span class="text-xs text-gray-500">
                            {{ $cmd->livraison->modeLivraison === 'domicile' ? 'Domicile' : 'Boutique' }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                 {{ $statutColors[$cmd->statut] ?? 'bg-gray-100 text-gray-600' }}">
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
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucune commande enregistrée.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection