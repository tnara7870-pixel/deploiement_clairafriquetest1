@props(['article', 'estFavori' => false])

<div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md
            transition-all border border-gray-100 hover:border-primary group">

    {{-- Image / Couverture --}}
    <a href="{{ route('client.article', $article->idArticle) }}" class="block">
        <div class="relative overflow-hidden"
             style="aspect-ratio: 2/3; background: linear-gradient(135deg, #1A4731, #2E7D52)">

            @if($article->image)
                <img src="{{ Storage::url($article->image) }}"
                     alt="{{ $article->designation }}"
                     class="w-full h-full object-cover group-hover:scale-105
                            transition-transform duration-300">
            @else
                <div class="w-full h-full flex flex-col items-center
                            justify-center p-3 text-center">
                    <div class="text-4xl mb-2"><x-heroicon-o-cube class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
                    <div class="text-white text-xs font-bold leading-tight line-clamp-3">
                        {{ $article->designation }}
                    </div>
                </div>
            @endif

            {{-- Badge stock limité --}}
            @if($article->seuilAlerte && $article->quantiteStock <= $article->seuilAlerte->quantiteMinimale)
                <div class="absolute bottom-2 left-2 bg-yellow-500 text-white
                            text-xs font-bold px-2 py-0.5 rounded-full">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Stock limité
                </div>
            @endif

            {{-- Bouton favori — toujours visible --}}
            @auth
           <button onclick="toggleFavori(this, {{ $article->idArticle }}); event.preventDefault();"
                aria-label="{{ $estFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' }}"
                aria-pressed="{{ $estFavori ? 'true' : 'false' }}"
                class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white flex items-center
                       justify-center text-base shadow-md transition-transform hover:scale-110 active:scale-95 z-10">
                <span class="favori-icone-wrap {{ $estFavori ? 'text-red-500' : 'text-gray-300 hover:text-red-500' }} transition-colors">
                    <x-heroicon-s-heart class="favori-icone-pleine w-4 h-4 {{ $estFavori ? '' : 'hidden' }}" />
                    <x-heroicon-o-heart class="favori-icone-vide w-4 h-4 {{ $estFavori ? 'hidden' : '' }}" />
                </span>
            </button>
            @endauth
        </div>
    </a>

    {{-- Infos --}}
    <div class="p-3">
        <div class="text-[10px] uppercase tracking-wide text-primary/70 font-medium mb-0.5">
            {{ $article->categorie->nomCategorie }}
        </div>
        <a href="{{ route('client.article', $article->idArticle) }}"
            class="text-sm font-semibold text-gray-800 hover:text-primary
                   transition-colors line-clamp-2 block mb-2">
            {{ $article->designation }}
        </a>
        <div class="flex items-center justify-between">
            <span class="font-mono text-base font-medium text-primary-dark">
                {{ number_format($article->prix, 0, ',', ' ') }} F
            </span>
            @auth
            <button onclick="ajouterPanier(this, {{ $article->idArticle }})"
                class="bg-primary text-white w-7 h-7 rounded-lg text-base
                       hover:bg-primary-light flex items-center justify-center
                       transition-colors">
                +
            </button>
            @else
            <a href="{{ route('login') }}"
                class="bg-primary text-white w-7 h-7 rounded-lg text-base
                       hover:bg-primary-light flex items-center justify-center">
                +
            </a>
            @endauth
        </div>
    </div>
</div>