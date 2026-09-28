{{--
    Suivi visuel de la commande — remplace le simple badge de statut par une
    frise donnant une réponse immédiate à "où en est ma commande, que
    reste-t-il ?". Directement rendu possible par la simplification des
    statuts du 04/08/2026 (4 paliers au lieu de 5, un seul statut de
    livraison "en_livraison" au lieu de deux qui se chevauchaient) : une
    frise sur des statuts ambigus aurait été aussi confuse que le problème
    qu'elle est censée résoudre.

    Usage : @include('client._suivi-commande', ['commande' => $commande])
--}}
@php
    $ordreStatuts = ['en_attente' => 0, 'validee' => 1, 'en_livraison' => 2, 'livree' => 3];

    $estRetraitBoutique = ($commande->livraison->modeLivraison ?? null) === 'boutique';

    $etapesSuivi = [
        0 => ['label' => 'Commande reçue', 'icone' => 'inbox-arrow-down'],
        1 => ['label' => 'Confirmée',      'icone' => 'check-circle'],
        2 => ['label' => $estRetraitBoutique ? 'Prête à récupérer' : 'En livraison', 'icone' => $estRetraitBoutique ? 'building-storefront' : 'truck'],
        3 => ['label' => $estRetraitBoutique ? 'Récupérée' : 'Livrée',               'icone' => 'sparkles'],
    ];

    // Une demande d'annulation en cours ne fait pas régresser la frise :
    // on affiche la progression réelle atteinte avant la demande
    // (statutAvantAnnulation), avec le bandeau d'alerte déjà présent
    // au-dessus qui signale l'examen en cours.
    $statutEffectif = $commande->statut === 'demande_annulation'
        ? ($commande->statutAvantAnnulation ?? 'validee')
        : $commande->statut;

    $rangActuel = $ordreStatuts[$statutEffectif] ?? null;
@endphp

@if($commande->statut === 'annulee')
    <div class="flex items-center gap-3 bg-red-50 border border-red-100 rounded-xl px-5 py-4">
        <span class="text-2xl"><x-heroicon-o-no-symbol class="w-6 h-6 inline-block flex-shrink-0" /></span>
        <div>
            <div class="text-sm font-semibold text-red-700">Commande annulée</div>
            <div class="text-xs text-red-500">Cette commande ne sera pas livrée.</div>
        </div>
    </div>
@elseif($rangActuel !== null)
    <ol class="flex items-start" aria-label="Suivi de la commande">
        @foreach($etapesSuivi as $rang => $info)
            @php
                $estFait   = $rang < $rangActuel;
                $estActuel = $rang === $rangActuel;
            @endphp
            <li class="flex-1 flex flex-col items-center text-center relative"
                @if($estActuel) aria-current="step" @endif>
                @if($rang > 0)
                    <div class="absolute top-4 right-1/2 w-full h-0.5 -z-10
                        {{ $estFait || $estActuel ? 'bg-primary' : 'bg-gray-200' }}"></div>
                @endif
                <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm shrink-0 bg-white border-2 transition-colors
                    {{ $estFait
                        ? 'border-primary bg-primary text-white'
                        : ($estActuel
                            ? 'border-primary text-primary ring-4 ring-primary-pale'
                            : 'border-gray-200 text-gray-300') }}">
                    @if($estFait)
                        <x-heroicon-o-check class="w-4 h-4" />
                    @else
                        <x-dynamic-component :component="'heroicon-o-' . $info['icone']" class="w-4 h-4" />
                    @endif
                </span>
                <span class="text-[11px] font-medium mt-2 leading-tight
                    {{ $estActuel ? 'text-primary-dark' : ($estFait ? 'text-gray-500' : 'text-gray-400') }}">
                    {{ $info['label'] }}
                </span>
            </li>
        @endforeach
    </ol>
@endif
