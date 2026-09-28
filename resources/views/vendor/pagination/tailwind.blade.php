{{-- Vue de pagination par défaut du projet (remplace celle de Laravel).
     Placée ici, elle s'applique automatiquement à tout appel ->links()
     dans l'application, sans rien changer dans chaque contrôleur/vue. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-xs text-gray-500">
            {{ __('Affichage de') }}
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            {{ __('à') }}
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
            {{ __('sur') }}
            <span class="font-medium">{{ $paginator->total() }}</span>
            {{ __('résultat(s)') }}
        </p>

        <div class="flex items-center gap-1">
            {{-- Lien précédent --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed select-none">
                    ← {{ __('Précédent') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="px-3 py-1.5 text-xs rounded-lg border border-primary-pale text-primary-dark hover:bg-primary-bg transition-colors">
                    ← {{ __('Précédent') }}
                </a>
            @endif

            {{-- Numéros de page --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2.5 py-1.5 text-xs text-gray-400 select-none">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="px-3 py-1.5 text-xs rounded-lg bg-primary text-white font-semibold min-w-[32px] text-center">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="px-3 py-1.5 text-xs rounded-lg border border-primary-pale text-primary-dark hover:bg-primary-bg transition-colors min-w-[32px] text-center">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Indicateur de page compact (mobile) --}}
            <span class="sm:hidden text-xs text-gray-500 px-2">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Lien suivant --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="px-3 py-1.5 text-xs rounded-lg border border-primary-pale text-primary-dark hover:bg-primary-bg transition-colors">
                    {{ __('Suivant') }} →
                </a>
            @else
                <span aria-disabled="true" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed select-none">
                    {{ __('Suivant') }} →
                </span>
            @endif
        </div>
    </nav>
@endif
