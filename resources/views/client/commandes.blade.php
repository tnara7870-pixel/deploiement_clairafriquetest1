@extends('layouts.client')
@section('title', 'Mes commandes')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <h1 class="text-lg font-semibold text-primary-dark mb-6">Mes commandes</h1>

    @if($commandes->isEmpty())
        <div class="bg-white border border-primary-pale rounded-xl py-16 text-center">
            <div class="text-4xl mb-3">
                <x-heroicon-o-cube class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" />
            </div>
            <p class="text-gray-400 text-sm mb-4">Vous n'avez pas encore passé de commande.</p>
            <a href="{{ route('client.catalogue') }}"
                class="bg-primary text-white px-5 py-2 rounded-lg text-sm hover:bg-primary-light">
                Voir le catalogue
            </a>
        </div>
    @else

        <form method="GET"
            class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center justify-center gap-3">
            <x-filtre-date />

            <button type="submit"
                class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light shrink-0">
                Filtrer
            </button>

            <a href="{{ route('client.commandes') }}"
                class="border border-gray-200 text-gray-500 px-4 h-9 flex items-center rounded-lg text-sm hover:bg-gray-50 shrink-0">
                Réinitialiser
            </a>
        </form>

        <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-primary-bg border-b border-primary-pale">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">N° Commande</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Livraison</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Détail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-pale">
                    @foreach($commandes as $cmd)
                        @php
                            $statutColors = [
                                'en_attente'         => 'bg-yellow-100 text-yellow-700',
                                'validee'            => 'bg-primary-pale text-primary-dark',
                                'en_livraison'       => 'bg-blue-100 text-blue-700',
                                'livree'             => 'bg-primary-pale text-primary',
                                'annulee'            => 'bg-red-100 text-red-600',
                                'demande_annulation' => 'bg-orange-100 text-orange-700',
                            ];
                            $statutLabels = [
                                'en_attente'         => 'En attente',
                                'validee'            => 'Validée',
                                'en_livraison'       => 'En livraison',
                                'livree'             => 'Livrée',
                                'annulee'            => 'Annulée',
                                'demande_annulation' => 'Annulation en cours',
                            ];
                        @endphp
                        <tr class="hover:bg-primary-bg">
                            <td class="px-4 py-3 text-xs font-medium text-primary-dark">
                                {{ $cmd->numeroCommande }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($cmd->dateCommande)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-primary-dark">
                                {{ number_format($cmd->montantTotal, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                @if($cmd->livraison?->modeLivraison === 'domicile')
                                    <x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Domicile
                                @else
                                    <x-heroicon-o-building-storefront class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Boutique
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statutColors[$cmd->statut] ?? '' }}">
                                    {{ $statutLabels[$cmd->statut] ?? $cmd->statut }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('client.commande.detail', $cmd->idCommande) }}"
                                    class="text-xs text-primary hover:underline">
                                    Voir <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $commandes->links() }}</div>
    @endif
</div>
@endsection