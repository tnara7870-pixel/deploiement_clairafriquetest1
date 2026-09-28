@extends('layouts.stock')
@section('title', 'Mouvements')
@section('page_title', 'Mouvements de stock')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    {{-- Formulaire enregistrement --}}
    <div class="col-span-1 bg-white border border-primary-pale rounded-xl p-4 h-fit">
        <div class="text-sm font-semibold text-primary-dark mb-4">
            Enregistrer un mouvement
        </div>
        <form method="POST" action="{{ route('stock.mouvement.store') }}">
            @csrf
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">Type *</label>
                <select name="typeMouvement" id="select-type-mouvement"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white">
                    <option value="entree"><x-heroicon-o-arrow-down class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Entrée stock</option>
                    <option value="ajustement"><x-heroicon-o-cog-6-tooth class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Ajustement</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="recherche-article-mvt" class="block text-xs text-gray-500 mb-1">Article *</label>
                <input type="text" id="recherche-article-mvt" list="liste-articles-mvt"
                    placeholder="Tapez la référence ou le nom de l'article…" autocomplete="off"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-white"
                    oninput="choisirArticleMvt(this)">
                <datalist id="liste-articles-mvt">
                    @foreach($articles as $art)
                        <option data-id="{{ $art->idArticle }}"
                            data-stock-ucad="{{ $art->stockPour('ucad') }}"
                            data-stock-centre-ville="{{ $art->stockPour('centre_ville') }}"
                            value="{{ $art->reference }} — {{ $art->designation }} (Total : {{ $art->quantiteStock }})">
                        </option>
                    @endforeach
                </datalist>
                <input type="hidden" name="idArticle" id="idArticleMvtChoisi" required>
                <p class="text-xs text-gray-400 mt-1">Tapez une référence ou un nom, puis sélectionnez dans la liste.</p>
            </div>

            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">Point de vente concerné *</label>
                @if(auth()->user()->estScopePointVente())
                    {{-- Compte scopé : le point est imposé, pas de choix possible. --}}
                    <div class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-primary-bg text-gray-600">
                        {{ auth()->user()->pointVenteAssigne === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                    </div>
                @else
                    <select name="pointVente" id="select-point-vente-mvt" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white">
                        <option value="ucad">UCAD</option>
                        <option value="centre_ville">Centre-ville</option>
                    </select>
                @endif
                <p class="text-xs text-gray-500 mt-1" id="hint-stock-point">
                    Sélectionnez un article pour voir son stock par point.
                </p>
            </div>

            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1" id="label-quantite">
                    Quantité *
                </label>
                <input type="number" name="quantite" min="1" value="1" id="input-quantite"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white" required>
                <p class="text-xs text-amber-600 mt-1 hidden" id="hint-ajustement">
                    Pour un ajustement, entrez la quantité réelle constatée en stock.
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Motif</label>
                <input type="text" name="motif"
                    placeholder="Ex : Réception fournisseur, inventaire..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-primary-bg">
            </div>

            <button type="submit"
                class="w-full bg-primary text-white py-2 rounded-lg text-sm font-medium hover:bg-primary-light transition-colors">
                Enregistrer
            </button>
        </form>
    </div>

    {{-- Historique --}}
    <div class="col-span-1 lg:col-span-2 bg-white border border-primary-pale rounded-xl overflow-hidden">
        {{-- Barre de filtres --}}
        <div class="px-4 py-3 border-b border-primary-pale">
            <form method="GET" class="flex items-center gap-2 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Rechercher…"
                    class="border border-gray-200 rounded-lg px-3 h-8 text-xs focus:outline-none focus:border-primary bg-primary-bg w-36">
                
                <select name="type"
                    class="border border-gray-200 rounded-lg px-2 h-8 text-xs focus:outline-none bg-white">
                    <option value="">Tous types</option>
                    <option value="entree"     {{ request('type') == 'entree'     ? 'selected' : '' }}>Entrées</option>
                    <option value="sortie"     {{ request('type') == 'sortie'     ? 'selected' : '' }}>Sorties</option>
                    <option value="retour"     {{ request('type') == 'retour'     ? 'selected' : '' }}>Retours</option>
                    <option value="ajustement" {{ request('type') == 'ajustement' ? 'selected' : '' }}>Ajustements</option>
                </select>

                @unless(auth()->user()->estScopePointVente())
                <select name="pointVente"
                    class="border border-gray-200 rounded-lg px-2 h-8 text-xs focus:outline-none bg-white">
                    <option value="">Tous points de vente</option>
                    <option value="ucad"         {{ request('pointVente') == 'ucad'         ? 'selected' : '' }}>UCAD</option>
                    <option value="centre_ville" {{ request('pointVente') == 'centre_ville' ? 'selected' : '' }}>Centre-ville</option>
                </select>
                @endunless

                <x-filtre-date />

                <button type="submit"
                    class="bg-primary text-white px-3 h-8 rounded-lg text-xs shrink-0 hover:bg-primary-light transition-colors">
                    Filtrer
                </button>

                <a href="{{ route('stock.mouvements') }}"
                    class="border border-gray-200 text-gray-500 px-3 h-8 flex items-center rounded-lg text-xs shrink-0 hover:bg-gray-50 transition-colors">
                    Réinitialiser
                </a>
            </form>
        </div>

        {{-- Tableau des mouvements --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-primary-bg border-b border-primary-pale text-gray-400 text-xs">
                        <th class="px-4 py-2 font-semibold">Date</th>
                        <th class="px-4 py-2 font-semibold">Type</th>
                        <th class="px-4 py-2 font-semibold">Article</th>
                        <th class="px-4 py-2 font-semibold">Point de vente</th>
                        <th class="px-4 py-2 font-semibold">Qté</th>
                        <th class="px-4 py-2 font-semibold">Motif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-pale">
                    @forelse($mouvements as $mouv)
                        @php
                            $typeColors = [
                                'entree'     => 'bg-emerald-100 text-emerald-700',
                                'sortie'     => 'bg-red-100 text-red-600',
                                'retour'     => 'bg-blue-100 text-blue-600',
                                'ajustement' => 'bg-amber-100 text-amber-700',
                            ];
                            $typeLabels = [
                                'entree'     => 'Entrée',
                                'sortie'     => 'Sortie',
                                'retour'     => 'Retour',
                                'ajustement' => 'Ajustement',
                            ];
                        @endphp
                        <tr class="hover:bg-primary-bg/50 transition-colors">
                            <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($mouv->dateMouvement)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $typeColors[$mouv->typeMouvement] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $typeLabels[$mouv->typeMouvement] ?? $mouv->typeMouvement }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-700 font-medium">
                                {{ $mouv->article->designation ?? 'Article supprimé' }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">
                                @if($mouv->pointVente)
                                    {{ $mouv->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                                @else
                                    <span class="italic text-gray-300">Antérieur à la répartition</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm font-semibold whitespace-nowrap">
                                @if($mouv->typeMouvement === 'entree' || $mouv->typeMouvement === 'retour')
                                    <span class="text-emerald-600">+{{ $mouv->quantite }}</span>
                                @elseif($mouv->typeMouvement === 'sortie')
                                    {{-- Stockée en négatif depuis le 02/08/2026 : le signe est déjà inclus. --}}
                                    <span class="text-red-500">{{ $mouv->quantite }}</span>
                                @else
                                    {{-- Ajustement : delta signé (peut être positif ou négatif). --}}
                                    <span class="text-amber-600">{{ $mouv->quantite > 0 ? '+' : '' }}{{ $mouv->quantite }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">
                                {{ $mouv->motif ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-gray-400">
                                Aucun mouvement trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-2 border-t border-primary-pale">
            {{ $mouvements->withQueryString()->links() }}
        </div>
    </div>
</div>

<script>
function choisirArticleMvt(input) {
    const option = input.list.querySelector(`option[value="${CSS.escape(input.value)}"]`);
    document.getElementById('idArticleMvtChoisi').value = option?.dataset?.id || '';
    afficherStockPoint();
}

function afficherStockPoint() {
    const input = document.getElementById('recherche-article-mvt');
    const option = input.list.querySelector(`option[value="${CSS.escape(input.value)}"]`);
    const hint = document.getElementById('hint-stock-point');
    const selectPoint = document.getElementById('select-point-vente-mvt');

    if (!option) {
        hint.textContent = 'Sélectionnez un article pour voir son stock par point.';
        return;
    }

    if (selectPoint) {
        const point = selectPoint.value;
        const stock = point === 'ucad' ? option.dataset.stockUcad : option.dataset.stockCentreVille;
        hint.textContent = 'Stock disponible à ce point : ' + stock;
    } else {
        // Compte scopé : un seul point possible, affiché en dur dans le HTML juste au-dessus.
        const pointScope = "{{ auth()->user()->pointVenteAssigne }}";
        const stock = pointScope === 'ucad' ? option.dataset.stockUcad : option.dataset.stockCentreVille;
        hint.textContent = 'Stock disponible à ce point : ' + stock;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const selectPoint = document.getElementById('select-point-vente-mvt');
    if (selectPoint) {
        selectPoint.addEventListener('change', afficherStockPoint);
    }

    const selectType = document.getElementById('select-type-mouvement');
    const hint = document.getElementById('hint-ajustement');
    const label = document.getElementById('label-quantite');
    const inputQuantite = document.getElementById('input-quantite');

    selectType.addEventListener('change', function() {
        if (this.value === 'ajustement') {
            hint.classList.remove('hidden');
            label.textContent = 'Nouvelle quantité en stock *';
            inputQuantite.setAttribute('min', '0');
        } else {
            hint.classList.add('hidden');
            label.textContent = 'Quantité *';
            inputQuantite.setAttribute('min', '1');
            if (inputQuantite.value < 1) inputQuantite.value = 1;
        }
    });
});
</script>
@endsection