{{--
    Fil d'étapes du tunnel de commande — orientation du client sur
    "où j'en suis / que reste-t-il à faire" pendant panier <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> récapitulatif
    <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> paiement. Voir demande UX du 04/08/2026 (parcours de commande client).

    Volontairement absent du chemin de paiement en ligne (Wave/Orange Money)
    une fois redirigé vers PayDunya : cette page est hébergée par PayDunya,
    hors de notre contrôle. C'est une limite honnête, pas un oubli — le
    fil reprend dès le retour sur nos pages.

    Usage : @include('client._etapes-commande', ['etape' => 2])
--}}
@php
    $etapesCommande = [
        1 => 'Panier',
        2 => 'Récapitulatif',
        3 => 'Paiement',
    ];
    $derniereEtape = count($etapesCommande);
@endphp
<nav aria-label="Étape de la commande" class="max-w-3xl mx-auto px-6 pt-6">
    <ol class="flex items-center">
        @foreach($etapesCommande as $numero => $libelle)
            @php
                $estFait   = $numero < $etape;
                $estActuel = $numero === $etape;
            @endphp
            <li class="flex items-center {{ $numero < $derniereEtape ? 'flex-1' : '' }}"
                @if($estActuel) aria-current="step" @endif>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                        {{ $estFait
                            ? 'bg-primary text-white'
                            : ($estActuel
                                ? 'bg-primary text-white ring-4 ring-primary-pale'
                                : 'bg-gray-100 text-gray-400') }}">
                        @if($estFait)<x-heroicon-o-check class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />@else{{ $numero }}@endif
                    </span>
                    <span class="text-xs font-medium hidden sm:inline
                        {{ $estActuel ? 'text-primary-dark' : ($estFait ? 'text-gray-500' : 'text-gray-400') }}">
                        {{ $libelle }}
                    </span>
                </div>
                @if($numero < $derniereEtape)
                    <div class="flex-1 h-0.5 mx-3 {{ $estFait ? 'bg-primary' : 'bg-gray-200' }}"></div>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
