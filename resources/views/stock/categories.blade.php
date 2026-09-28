@extends('layouts.stock')
@section('title', 'Catégories')
@section('page_title', 'Gestion des catégories')

@section('content')

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Rechercher une catégorie…"
        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:border-primary bg-primary-bg h-9">
    <button type="submit" class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light">
        Rechercher
    </button>
    @if(request('search'))
    <a href="{{ route('stock.categories') }}" class="text-xs text-gray-400 hover:underline">Réinitialiser</a>
    @endif
</form>

<div class="flex justify-between items-center mb-5">
    <span class="text-sm text-gray-500">{{ $categories->total() }} catégorie(s)</span>
    <button onclick="document.getElementById('modal-add-cat').classList.remove('hidden')"
        class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
        + Nouvelle catégorie
    </button>
</div>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Nom</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Description</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Articles</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($categories as $cat)
            <tr class="hover:bg-primary-bg">
                <td class="px-4 py-3 text-sm font-medium text-gray-800">
                    {{ $cat->nomCategorie }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $cat->description ?? '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                    {{ $cat->articles_count }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $cat->statut
                            ? 'bg-primary-pale text-primary-dark'
                            : 'bg-red-100 text-red-600' }}">
                        {{ $cat->statut ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 flex items-center gap-3">
                    <button onclick="ouvrirModifCat({{ $cat }})"
                        class="text-xs text-primary hover:underline">
                        Modifier
                    </button>
                    <form method="POST"
                        action="{{ route('stock.categorie.statut', $cat->idCategorie) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs text-gray-400 hover:underline">
                            {{ $cat->statut ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    @if($cat->articles_count > 0)
                    <span class="text-xs text-gray-300 cursor-not-allowed"
                        title="Contient encore {{ $cat->articles_count }} article(s)">
                        Supprimer
                    </span>
                    @else
                    <form method="POST" action="{{ route('stock.categorie.destroy', $cat->idCategorie) }}"
                        onsubmit="return confirm('Supprimer définitivement la catégorie « {{ $cat->nomCategorie }} » ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">
                            Supprimer
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucune catégorie.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-primary-pale">
        {{ $categories->links() }}
    </div>
</div>

{{-- MODAL AJOUT --}}
<div id="modal-add-cat"
    class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Nouvelle catégorie</h3>
            <button onclick="document.getElementById('modal-add-cat').classList.add('hidden')"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('stock.categorie.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Nom *</label>
                <input type="text" name="nomCategorie" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg">
            </div>
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('modal-add-cat').classList.add('hidden')"
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
<div id="modal-edit-cat"
    class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Modifier la catégorie</h3>
            <button onclick="document.getElementById('modal-edit-cat').classList.add('hidden')"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>
        <form id="form-edit-cat" method="POST" action="">
            @csrf @method('PATCH')
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Nom *</label>
                <input type="text" name="nomCategorie" id="edit-cat-nom" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg">
            </div>
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <textarea name="description" id="edit-cat-desc" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('modal-edit-cat').classList.add('hidden')"
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
function ouvrirModifCat(cat) {
    document.getElementById('edit-cat-nom').value  = cat.nomCategorie;
    document.getElementById('edit-cat-desc').value = cat.description ?? '';
    document.getElementById('form-edit-cat').action =
        '/stock/categorie/' + cat.idCategorie;
    document.getElementById('modal-edit-cat').classList.remove('hidden');
}
</script>

@endsection