@extends('layouts.stock')
@section('title', 'Articles')
@section('page_title', 'Gestion des articles')

@section('content')

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Rechercher par nom ou référence…"
        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:border-primary bg-primary-bg h-9">
    <select name="categorie"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm
               focus:outline-none bg-white">
        <option value="">Toutes catégories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->idCategorie }}"
                {{ request('categorie') == $cat->idCategorie ? 'selected' : '' }}>
                {{ $cat->nomCategorie }}
            </option>
        @endforeach
    </select>
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm
               hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('stock.articles') }}"
        class="border border-gray-200 text-gray-500 px-4 h-9 flex items-center
               rounded-lg text-sm hover:bg-gray-50 shrink-0">
        Réinitialiser
    </a>
</form>

<div class="flex justify-between items-center mb-4">
    <span class="text-sm text-gray-500">{{ $articles->total() }} article(s)</span>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
        class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
        + Nouvel article
    </button>
</div>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Article</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Catégorie</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Prix</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Stock</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Seuil</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($articles as $article)
            @php
                $seuil = $article->seuilAlerte;
                $stockAffiche = $pointVente ? $article->stockPour($pointVente) : $article->quantiteStock;
                $enAlerte = $seuil && $stockAffiche <= $seuil->quantiteMinimale;
            @endphp
            <tr class="hover:bg-primary-bg {{ $enAlerte ? 'bg-yellow-50' : '' }}">
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-800">
                        {{ $article->designation }}
                    </div>
                    <div class="text-xs text-gray-400 font-mono">{{ $article->reference }}</div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $article->categorie->nomCategorie }}
                </td>
                <td class="px-4 py-3 text-sm font-semibold text-primary-dark">
                    {{ number_format($article->prix, 0, ',', ' ') }} F
                </td>
                <td class="px-4 py-3">
                    <span class="text-sm font-semibold
                        {{ $enAlerte ? 'text-yellow-600' : 'text-gray-700' }}">
                        {{ $stockAffiche }}
                        @if($enAlerte)<x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />@endif
                    </span>
                    @unless($pointVente)
                        <div class="text-[10px] text-gray-400">
                            UCAD : {{ $article->stockPour('ucad') }} · Centre-ville : {{ $article->stockPour('centre_ville') }}
                        </div>
                    @endunless
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $seuil ? $seuil->quantiteMinimale : '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $article->statut ? 'bg-primary-pale text-primary-dark' : 'bg-red-100 text-red-600' }}">
                        {{ $article->statut ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <button onclick="ouvrirModif({{ $article }}, {{ $seuil?->quantiteMinimale ?? 0 }})"
                            class="text-xs text-primary hover:underline">
                            Modifier
                        </button>
                        <form method="POST" action="{{ route('stock.article.statut', $article->idArticle) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-gray-400 hover:underline">
                                {{ $article->statut ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('stock.article.destroy', $article->idArticle) }}"
                            onsubmit="return confirm('Supprimer définitivement « {{ $article->designation }} » ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucun article trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $articles->withQueryString()->links() }}</div>

{{-- MODAL AJOUT --}}
<div id="modal-add"
    class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Nouvel article</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('stock.article.store') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Référence *</label>
                    <input type="text" name="reference" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Catégorie *</label>
                    <select name="idCategorie" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none bg-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->idCategorie }}">{{ $cat->nomCategorie }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Désignation *</label>
                <input type="text" name="designation" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg">
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Prix (F) *</label>
                    <input type="number" name="prix" min="0" step="50" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Stock initial *</label>
                    <input type="number" name="quantiteStock" min="0" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Seuil alerte</label>
                    <input type="number" name="seuilMinimal" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Point de vente recevant le stock initial</label>
                @if(auth()->user()->estScopePointVente())
                    <div class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-primary-bg text-gray-600">
                        {{ auth()->user()->pointVenteAssigne === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                    </div>
                @else
                    <select name="pointVenteInitial"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                        <option value="ucad">UCAD</option>
                        <option value="centre_ville">Centre-ville</option>
                    </select>
                @endif
                <p class="text-[11px] text-gray-400 mt-1">
                    Le catalogue reste commun aux deux points ; seul ce stock de départ est ciblé — l'autre point démarre à 0 et se redistribue ensuite par mouvement.
                </p>
            </div>
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Image (optionnel)</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3
                           file:rounded-lg file:border-0 file:text-xs file:font-medium
                           file:bg-primary-pale file:text-primary-dark">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="border border-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
                    Annuler
                </button>
                <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL MODIFICATION --}}
<div id="modal-edit"
    class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Modifier l'article</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>
        <form id="form-edit" method="POST" action="" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Désignation *</label>
                    <input type="text" name="designation" id="edit-designation" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Catégorie *</label>
                    <select name="idCategorie" id="edit-categorie" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none bg-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->idCategorie }}">{{ $cat->nomCategorie }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Prix (F) *</label>
                    <input type="number" name="prix" id="edit-prix" min="0" step="50" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Stock actuel</label>
                    <input type="number" id="edit-stock" min="0" disabled
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               bg-gray-100 text-gray-500 cursor-not-allowed">
                    <p class="text-[11px] text-gray-400 mt-1">
                        Modifiable uniquement via un
                        <a href="{{ route('stock.mouvements') }}" class="underline">mouvement de stock</a>.
                    </p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Seuil alerte</label>
                    <input type="number" name="seuilMinimal" id="edit-seuil" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
            </div>
            <div class="mb-4">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="statut" id="edit-statut" value="1"
                        class="rounded border-gray-300 text-primary">
                    Article actif
                </label>
            </div>
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Nouvelle image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3
                           file:rounded-lg file:border-0 file:text-xs file:font-medium
                           file:bg-primary-pale file:text-primary-dark">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="border border-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
                    Annuler
                </button>
                <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function ouvrirModif(article, seuil) {
    document.getElementById('edit-designation').value = article.designation;
    document.getElementById('edit-prix').value        = article.prix;
    document.getElementById('edit-stock').value       = article.quantiteStock;
    document.getElementById('edit-categorie').value   = article.idCategorie;
    document.getElementById('edit-statut').checked    = article.statut == 1;
    document.getElementById('edit-seuil').value       = seuil || '';
    document.getElementById('form-edit').action =
        '/stock/article/' + article.idArticle;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>

@endsection