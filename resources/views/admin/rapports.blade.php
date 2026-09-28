@extends('layouts.admin')
@section('title', 'Rapports')
@section('page_title', 'Rapports & Statistiques')

@section('content')

{{-- Filtres par date + export --}}
<form method="GET" action="{{ route('admin.rapports') }}"
      class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-end gap-3 flex-wrap">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Du</label>
        <input type="date" name="date_debut" value="{{ $dateDebut }}"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-primary-bg">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Au</label>
        <input type="date" name="date_fin" value="{{ $dateFin }}"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-primary-bg">
    </div>
    <button type="submit"
        class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light font-medium">
        Filtrer
    </button>
    @if($dateDebut || $dateFin)
    <a href="{{ route('admin.rapports') }}" class="text-xs text-gray-400 hover:underline">Réinitialiser</a>
    @endif

    <a href="{{ route('admin.rapports.export', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}"
        class="ml-auto bg-primary-dark text-white px-4 py-2 rounded-lg text-sm hover:bg-primary font-medium flex items-center gap-2">
        <x-heroicon-o-arrow-down class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Exporter en CSV
    </a>
</form>

{{-- KPIs globaux --}}
<div class="grid grid-cols-2 gap-4 mb-5">
    <div class="bg-white border border-primary-pale rounded-xl p-5">
        <div class="text-xs text-gray-400 mb-1">Chiffre d'affaires total</div>
        <div class="text-3xl font-semibold text-primary-dark">
            {{ number_format($totalVentes, 0, ',', ' ') }} F
        </div>
        <div class="text-xs text-primary mt-1">Commandes non annulées</div>
    </div>
    <div class="bg-white border border-primary-pale rounded-xl p-5">
        <div class="text-xs text-gray-400 mb-1">Total commandes</div>
        <div class="text-3xl font-semibold text-primary-dark">{{ $totalCommandes }}</div>
        <div class="flex gap-2 mt-2 flex-wrap">
            @foreach($commandesParStatut as $s)
            @php
                $colors = [
                    'en_attente'   => 'bg-yellow-100 text-yellow-700',
                    'validee'      => 'bg-primary-pale text-primary-dark',
                    'en_livraison' => 'bg-blue-100 text-blue-700',
                    'livree'       => 'bg-primary-pale text-primary',
                    'annulee'      => 'bg-red-100 text-red-600',
                ];
                $labels = [
                    'en_attente'   => 'En attente',
                    'validee'      => 'Validée',
                    'en_livraison' => 'En livraison',
                    'livree'       => 'Livrée',
                    'annulee'      => 'Annulée',
                ];
            @endphp
            <span class="text-xs px-2 py-1 rounded-full font-medium
                         {{ $colors[$s->statut] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $labels[$s->statut] ?? $s->statut }} : {{ $s->total }}
            </span>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    {{-- Top articles --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale">
            <span class="text-sm font-semibold text-primary-dark">Top 10 articles vendus</span>
        </div>
        <table class="w-full">
            <thead>
                <tr class="bg-primary-bg border-b border-primary-pale">
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Article</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Vendus</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">CA</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-pale">
                @forelse($topArticles as $art)
                <tr class="hover:bg-primary-bg">
                    <td class="px-4 py-2 text-xs text-gray-700">{{ $art->designation }}</td>
                    <td class="px-4 py-2 text-xs font-semibold text-primary-dark">
                        {{ $art->total_vendu }}
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-500">
                        {{ number_format($art->chiffre_affaires, 0, ',', ' ') }} F
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">
                        Aucune donnée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Top clients --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale">
            <span class="text-sm font-semibold text-primary-dark">Top 10 clients</span>
        </div>
        <table class="w-full">
            <thead>
                <tr class="bg-primary-bg border-b border-primary-pale">
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Client</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Commandes</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Total dépensé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-pale">
                @forelse($topClients as $client)
                <tr class="hover:bg-primary-bg">
                    <td class="px-4 py-2 text-xs text-gray-700">
                        {{ $client->prenom }} {{ $client->nom }}
                    </td>
                    <td class="px-4 py-2 text-xs font-semibold text-primary-dark">
                        {{ $client->commandes_count }}
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-500">
                        {{ number_format($client->total_depense ?? 0, 0, ',', ' ') }} F
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">
                        Aucun client
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection