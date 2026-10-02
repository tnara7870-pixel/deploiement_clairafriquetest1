@extends('layouts.stock')
@section('title', 'Tableau de bord Stock')
@section('page_title', 'Tableau de bord — Stock')

@section('content')

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('stock.articles') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Total articles</div>
        <div class="text-2xl font-semibold text-primary-dark">
            {{ number_format($totalArticles, 0, ',', ' ') }}
        </div>
        <div class="text-xs text-primary mt-1">Unités en stock</div>
    </a>
    <a href="{{ route('stock.alertes') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Alertes actives</div>
        <div class="text-2xl font-semibold {{ $alertes->count() > 0 ? 'text-red-500' : 'text-primary-dark' }}">
            {{ $alertes->count() }}
        </div>
        <div class="text-xs {{ $alertes->count() > 0 ? 'text-red-400' : 'text-primary' }} mt-1">
            @if($alertes->count() > 0)<x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Action requise @else Stock OK @endif
        </div>
    </a>
    <a href="{{ route('stock.mouvements') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Entrées du jour</div>
        <div class="text-2xl font-semibold text-primary-dark">+{{ $entreesJour }}</div>
        <div class="text-xs text-primary mt-1">Unités reçues</div>
    </a>
    <a href="{{ route('stock.mouvements') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Sorties du jour</div>
        <div class="text-2xl font-semibold text-primary-dark">-{{ $sortiesJour }}</div>
        <div class="text-xs text-gray-400 mt-1">Via commandes</div>
    </a>
</div>

{{-- Grille principale --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

    {{-- Inventaire --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
            <span class="text-sm font-semibold text-primary-dark">Inventaire en temps réel</span>
            <a href="{{ route('stock.articles') }}" class="text-xs text-primary hover:underline">
                Voir tout <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
            </a>
        </div>
        <table class="w-full">
            <thead>
                <tr class="bg-primary-bg border-b border-primary-pale">
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Article</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">
                        {{ $pointVente ? 'Qté (' . ($pointVente === 'ucad' ? 'UCAD' : 'Centre-ville') . ')' : 'Qté (total)' }}
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Niveau</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-400">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary-pale">
                @forelse($inventaire as $article)
                @php
                    $seuil = $article->seuilAlerte;
                    $stock = $pointVente ? $article->stockPour($pointVente) : $article->quantiteStock;
                    $max   = max($seuil?->quantiteMinimale * 5 ?? 100, $stock, 1);
                    $pct   = min(100, round(($stock / $max) * 100));
                    if ($seuil && $stock <= $seuil->quantiteMinimale) {
                        $color = $stock == 0 ? 'bg-red-500' : 'bg-yellow-400';
                        $tag   = $stock == 0 ? ['bg-red-100 text-red-600', 'Rupture']
                                             : ['bg-yellow-100 text-yellow-700', 'Bas'];
                    } else {
                        $color = 'bg-primary';
                        $tag   = ['bg-primary-pale text-primary-dark', 'OK'];
                    }
                @endphp
                <tr class="hover:bg-primary-bg">
                    <td class="px-4 py-2 text-xs text-gray-700 max-w-xs truncate">
                        {{ $article->designation }}
                        @unless($pointVente)
                            <div class="text-[10px] text-gray-400">
                                UCAD : {{ $article->stockPour('ucad') }} · Centre-ville : {{ $article->stockPour('centre_ville') }}
                            </div>
                        @endunless
                    </td>
                    <td class="px-4 py-2 text-sm font-semibold text-primary-dark">
                        {{ $stock }}
                    </td>
                    <td class="px-4 py-2">
                        <div class="w-16 bg-gray-100 rounded h-1.5">
                            <div class="{{ $color }} h-1.5 rounded"
                                 style="width:{{ $pct }}%"></div>
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $tag[0] }}">
                            {{ $tag[1] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">
                        Aucun article
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-2 border-t border-primary-pale">
            {{ $inventaire->links() }}
        </div>
    </div>

    {{-- Mouvements + formulaire rapide --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden flex flex-col">
        <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
            <span class="text-sm font-semibold text-primary-dark">Mouvements récents</span>
            <a href="{{ route('stock.mouvements') }}" class="text-xs text-primary hover:underline">
                Voir tout <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
            </a>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-primary-pale">
            @forelse($derniersMouvements as $mouv)
            @php
                $icons  = ['entree' => 'arrow-down', 'sortie' => 'arrow-up', 'retour' => 'arrow-uturn-left', 'ajustement' => 'cog-6-tooth'];
                $colors = [
                    'entree'     => 'bg-primary-pale text-primary-dark',
                    'sortie'     => 'bg-red-100 text-red-600',
                    'retour'     => 'bg-blue-100 text-blue-600',
                    'ajustement' => 'bg-yellow-100 text-yellow-700',
                ];
                $qtyColor = in_array($mouv->typeMouvement, ['entree','retour'])
                    ? 'text-primary font-semibold'
                    : 'text-red-500 font-semibold';
                $qtySign  = in_array($mouv->typeMouvement, ['entree','retour']) ? '+' : '-';
            @endphp
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center
                            text-xs flex-shrink-0 {{ $colors[$mouv->typeMouvement] ?? '' }}">
                    <x-dynamic-component :component="'heroicon-o-' . ($icons[$mouv->typeMouvement] ?? 'question-mark-circle')" class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-gray-800 truncate">
                        {{ ucfirst($mouv->typeMouvement) }} — {{ $mouv->article->designation }}
                        @if($mouv->pointVente)
                            <span class="text-[10px] text-gray-400">({{ $mouv->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }})</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $mouv->utilisateur->prenom ?? 'Système' }} ·
                        {{ \Carbon\Carbon::parse($mouv->dateMouvement)->diffForHumans() }}
                    </div>
                </div>
                <span class="text-sm {{ $qtyColor }}">
                    {{ $qtySign }}{{ $mouv->quantite }}
                </span>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-xs text-gray-400">
                Aucun mouvement enregistré
            </div>
            @endforelse
        </div>

        {{-- Formulaire rapide --}}
        <div class="border-t border-primary-pale bg-primary-bg p-4">
            <div class="text-xs font-semibold text-primary-dark mb-3">
                Enregistrer un mouvement
            </div>
            <form method="POST" action="{{ route('stock.mouvement.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Type</label>
                        <select name="typeMouvement"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5
                                   text-xs bg-white focus:outline-none focus:border-primary">
                            <option value="entree">Entrée</option>
                            <option value="ajustement">Ajustement</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Article</label>
                        <input type="text" id="recherche-article-dash" list="liste-articles-dash"
                            placeholder="Réf. ou nom…" autocomplete="off"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5
                                   text-xs bg-white focus:outline-none focus:border-primary"
                            oninput="document.getElementById('idArticleDashChoisi').value = (this.list.querySelector(`option[value=&quot;${CSS.escape(this.value)}&quot;]`) || {}).dataset?.id || '';">
                        <datalist id="liste-articles-dash">
                            @foreach($articlesPourRecherche as $art)
                                <option data-id="{{ $art->idArticle }}"
                                    value="{{ $art->reference }} — {{ Str::limit($art->designation, 25) }}">
                                </option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="idArticle" id="idArticleDashChoisi" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs text-gray-500 mb-1">Point de vente concerné</label>
                    @if($pointVente)
                        <div class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs bg-white text-gray-600">
                            {{ $pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                        </div>
                    @else
                        <select name="pointVente" required
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5
                                   text-xs bg-white focus:outline-none focus:border-primary">
                            <option value="ucad">UCAD</option>
                            <option value="centre_ville">Centre-ville</option>
                        </select>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Quantité</label>
                        <input type="number" name="quantite" min="1" value="1"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5
                                   text-xs bg-white focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Motif</label>
                        <input type="text" name="motif"
                            placeholder="Ex: réception fournisseur"
                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5
                                   text-xs bg-white focus:outline-none focus:border-primary">
                    </div>
                </div>
                <button type="submit"
                    class="w-full bg-primary text-white py-2 rounded-lg text-xs
                           font-medium hover:bg-primary-light">
                    Enregistrer le mouvement
                </button>
            </form>
        </div>
    </div>
</div>

@endsection