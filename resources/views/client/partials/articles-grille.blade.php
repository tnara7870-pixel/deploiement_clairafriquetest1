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