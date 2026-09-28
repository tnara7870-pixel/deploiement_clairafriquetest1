@extends('layouts.client')
@section('title', 'Catalogue')

@section('content')

{{-- BANNIÈRE --}}
<div class="bg-primary-dark relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-light via-amber-ca to-transparent"></div>
    <div class="max-w-7xl mx-auto px-6 py-10 flex justify-between items-center">
        <div>
            <p class="text-primary-pale text-xs font-medium tracking-widest uppercase mb-2">
                Librairie · Papeterie · Dakar
            </p>
            <h1 class="text-white text-3xl font-medium leading-tight mb-3">
                Notre catalogue
            </h1>
            <p class="text-primary-pale text-sm mb-5 max-w-md">
                Livres, papeterie et fournitures de bureau.
            </p>
        </div>
    </div>
</div>

{{-- CHIPS CATÉGORIES --}}
<div class="bg-white border-b border-primary-pale sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-6 py-2 flex gap-2 overflow-x-auto">
        <a href="{{ route('client.catalogue') }}"
            class="px-3 py-1 rounded-full text-xs font-medium shrink-0 border
                   {{ !request('categorie') && !request('search')
                       ? 'bg-primary-pale text-primary-dark border-primary'
                       : 'text-gray-500 border-gray-200 hover:bg-gray-50' }}">
            Tous
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('client.catalogue') }}?categorie={{ $cat->idCategorie }}"
            class="px-3 py-1 rounded-full text-xs font-medium shrink-0 border flex items-center gap-1.5
                   {{ request('categorie') == $cat->idCategorie
                       ? 'bg-primary-pale text-primary-dark border-primary'
                       : 'text-gray-500 border-gray-200 hover:bg-gray-50' }}">
            <x-icone-categorie :nom="$cat->nomCategorie" class="w-3.5 h-3.5" />
            {{ $cat->nomCategorie }}
        </a>
        @endforeach
    </div>
</div>

<div class="max-w-7xl mx-auto px-6 py-8">

    {{-- NOUVEAUTÉS --}}
    @if($nouveautes->count() > 0 && !request('search') && !request('categorie'))
    <div class="mb-10">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-base font-semibold text-primary-dark">Nouveautés</h2>
            <a href="{{ route('client.catalogue') }}"
                class="text-xs text-primary hover:underline">Voir tout <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($nouveautes as $article)
                <x-carte-article
                    :article="$article"
                    :estFavori="in_array($article->idArticle, $favorisIds)" />
            @endforeach
        </div>
    </div>
    @endif

    {{-- CATALOGUE --}}
    <div id="catalogue">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-base font-semibold text-primary-dark">
                @if(request('search'))
                    Résultats pour "{{ request('search') }}"
                @elseif(request('categorie'))
                    {{ $categories->firstWhere('idCategorie', request('categorie'))?->nomCategorie ?? 'Catalogue' }}
                @else
                    Catalogue
                @endif

                <span id="compteur-articles" class="text-sm font-normal text-gray-400 ml-2">
                    ({{ $articles->total() }} article(s))
                </span>
            </h2>

            {{-- Le SELECT n'est plus dans un <form> pour éviter les soumissions accidentelles --}}
            <div class="flex items-center gap-3">
                <select id="select-tri" onchange="filtrerCatalogue()"
                    class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none bg-white">
                    <option value="">Pertinence</option>
                    <option value="prix_asc"  {{ request('tri') == 'prix_asc'  ? 'selected' : '' }}>Prix croissant</option>
                    <option value="prix_desc" {{ request('tri') == 'prix_desc' ? 'selected' : '' }}>Prix décroissant</option>
                </select>
            </div>
        </div>

        {{-- VUE GRILLE --}}
        <div id="view-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @forelse($articles as $article)
                <x-carte-article
                    :article="$article"
                    :estFavori="in_array($article->idArticle, $favorisIds)" />
            @empty
            <div class="col-span-6 py-16 text-center text-gray-400">
                <div class="text-4xl mb-3"><x-heroicon-o-magnifying-glass class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
                <p class="text-sm">Aucun article trouvé.</p>
                <a href="{{ route('client.catalogue') }}"
                    class="text-primary text-xs hover:underline mt-2 block">
                    Voir tout le catalogue
                </a>
            </div>
            @endforelse
        </div>

        {{-- Une seule zone de pagination --}}
        <div id="pagination-zone" class="mt-8">
            {{ $articles->withQueryString()->links() }}
        </div>
    </div>
</div>

<script>
function filtrerCatalogue(page = 1) {
    const triSelect = document.getElementById('select-tri');
    const tri       = triSelect ? triSelect.value : '';

    const urlParams = new URLSearchParams(window.location.search);
    const categorie = urlParams.get('categorie') ?? '';
    const search    = urlParams.get('search') ?? '';

    const params = new URLSearchParams();
    if (tri)       params.set('tri', tri);
    if (categorie) params.set('categorie', categorie);
    if (search)    params.set('search', search);
    if (page > 1)  params.set('page', page);

    // Mettre à jour la barre d'adresse
    window.history.pushState({}, '', '?' + params.toString());

    const grid = document.getElementById('view-grid');
    if (grid) {
        grid.style.opacity = '0.5';
        grid.style.pointerEvents = 'none';
    }

    // On utilise la route dynamique Laravel plutôt qu'un chemin écrit en dur
    const routeUrl = "{{ route('client.catalogue') }}";
    
    fetch(routeUrl + '?' + params.toString(), {
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur réseau (' + r.status + ')');
        return r.json();
    })
    .then(data => {
        if (grid) {
            grid.innerHTML = data.html;
            grid.style.opacity = '1';
            grid.style.pointerEvents = 'auto';
        }

        const paginationZone = document.getElementById('pagination-zone');
        if (paginationZone) {
            paginationZone.innerHTML = data.pagination;
        }

        const compteur = document.getElementById('compteur-articles');
        if (compteur) {
            compteur.textContent = '(' + data.total + ' article(s))';
        }
    })
    .catch(err => {
        console.error('Erreur lors du tri:', err);
        if (grid) {
            grid.style.opacity = '1';
            grid.style.pointerEvents = 'auto';
        }
    });
}

document.addEventListener('click', function(e) {
    const link = e.target.closest('#pagination-zone a');
    if (!link) return;

    e.preventDefault();
    const url  = new URL(link.href);
    const page = url.searchParams.get('page') ?? 1;
    
    filtrerCatalogue(page);
});
</script>
@endsection