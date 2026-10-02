@extends('layouts.client')
@section('title', 'ClaireAfrique — Librairie Papeterie Dakar')

@section('content')

{{-- ═══════════════════════════════════ BANNIÈRE HÉRO ═══ --}}
<section class="relative overflow-hidden bg-paper-dots min-h-96">

    {{-- Voile léger pour garder le texte lisible par-dessus la texture --}}
    <div class="absolute inset-0 z-0 bg-paper/60"></div>

    {{-- Motifs décoratifs --}}
    <div class="absolute top-0 right-0 w-2/3 h-full z-0 opacity-20"
         style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2280%22 cy=%2220%22 r=%2260%22 fill=%22%231A4731%22 opacity=%220.1%22/></svg>') no-repeat center; background-size: cover;">
    </div>

    <div class="max-w-7xl mx-auto px-6 py-12 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 items-center min-h-80">

            {{-- Livres visuels à gauche --}}
            <div class="relative flex items-center justify-center h-80">

    {{-- Livre 1 --}}
    <div class="absolute left-4 top-16 w-28 shadow-xl transform -rotate-12 hover:-rotate-6 transition-all duration-300 z-10">
        <div class="rounded-lg overflow-hidden bg-green-700" style="aspect-ratio: 2/3">
            <div class="h-full flex flex-col items-center justify-center p-3 text-center">
                <div class="text-3xl mb-1"><x-heroicon-o-book-open class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
                <div class="text-white text-xs font-bold leading-tight">
                    Les Bouts de Bois de Dieu
                </div>
                <div class="text-green-200 text-xs mt-1">
                    Sembène Ousmane
                </div>
            </div>
        </div>
    </div>

    {{-- Livre principal --}}
    <div class="absolute left-20 top-4 w-36 shadow-2xl transform rotate-2 hover:rotate-0 transition-all duration-300 z-30">
        <div class="bg-primary-dark rounded-lg overflow-hidden" style="aspect-ratio: 2/3">
            <div class="h-full flex flex-col items-center justify-center p-4 text-center">
                <div class="text-4xl mb-2"><x-heroicon-o-book-open class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
                <div class="text-white text-sm font-bold leading-tight">
                    L'Aventure Ambiguë
                </div>
                <div class="text-primary-pale text-xs mt-2">
                    Cheikh Hamidou Kane
                </div>
            </div>
        </div>
    </div>

    {{-- Livre 3 --}}
    <div class="absolute left-44 top-16 w-28 shadow-xl transform rotate-12 hover:rotate-6 transition-all duration-300 z-20">
        <div class="rounded-lg overflow-hidden bg-amber-700" style="aspect-ratio: 2/3">
            <div class="h-full flex flex-col items-center justify-center p-3 text-center">
                <div class="text-3xl mb-1"><x-heroicon-o-book-open class="w-7 h-7 inline-block flex-shrink-0 align-[-3px]" /></div>
                <div class="text-white text-xs font-bold leading-tight">
                    Une si longue lettre
                </div>
                <div class="text-amber-100 text-xs mt-1">
                    Mariama Bâ
                </div>
            </div>
        </div>
    </div>

  
    {{-- Ombre au sol --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 w-72 h-6 bg-black/10 blur-xl rounded-full"></div>

  

</div>

            {{-- Texte à droite --}}
            <div class="sm:pl-8">
                <div class="inline-flex items-center gap-2 bg-primary/10 text-primary
                            text-xs font-semibold px-3 py-1.5 rounded-full mb-4">
                    <x-heroicon-o-globe-alt class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Librairie · Papeterie · Dakar
                </div>

                <h1 class="text-3xl sm:text-4xl font-medium text-ink leading-tight mb-4">
                    Découvrez votre<br>
                    <span class="text-primary-dark">nouvelle</span>
                    <span class="text-primary">collection</span>
                </h1>

                {{-- Trait décoratif --}}
                <div class="flex items-center gap-2 mb-6">
                    <div class="h-1 w-12 bg-amber-ca rounded-full"></div>
                    <div class="h-1 w-4 bg-primary rounded-full"></div>
                </div>

                <p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-sm">
                    Livres, papeterie et fournitures de bureau disponibles en ligne.
                    Commandez depuis chez vous avec livraison rapide à Dakar.
                </p>

                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    <a href="{{ route('client.catalogue') }}"
                        class="bg-primary-dark text-white font-semibold px-6 py-3
                               rounded-xl hover:bg-primary transition-colors text-sm
                               flex items-center gap-2 shadow-lg">
                        Explorer maintenant
                        <span class="text-lg"><x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /></span>
                    </a>
                    @guest
                    <a href="{{ route('register') }}"
                        class="border-2 border-primary text-primary font-medium px-6 py-3
                               rounded-xl hover:bg-primary hover:text-white
                               transition-colors text-sm">
                        Créer un compte
                    </a>
                    @endguest
                </div>

                {{-- Stats --}}
                <div class="flex items-center gap-6 mt-10 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary-dark">
                       
                        </div>
                        
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-primary-dark">
                         
                        </div>
                      
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="text-center">
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



{{-- ═══════════════════════════════════ CATÉGORIES ═══ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Nos catégories</h2>
            <p class="text-gray-400 text-xs mt-1">
                Trouvez facilement ce que vous cherchez
            </p>
        </div>
        <a href="{{ route('client.catalogue') }}"
            class="text-sm text-primary font-medium hover:underline">
            Tout voir <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
        </a>
    </div>
    <div class="flex gap-3 overflow-x-auto pb-2">
        @php
            // Variations de teinte du vert de marque + un rappel ambre en
            // fin de cycle, plutôt qu'un arc-en-ciel de couleurs Tailwind
            // par défaut sans rapport avec l'identité du site.
            $tons = [
                'bg-primary-bg border-primary-pale hover:border-primary text-primary-dark',
                'bg-primary-pale/60 border-primary-pale hover:border-primary text-primary-dark',
                'bg-paper border-paper-line hover:border-amber-ca text-primary-dark',
            ];
        @endphp
        @foreach($categories as $i => $cat)
        <a href="{{ route('client.catalogue') }}?categorie={{ $cat->idCategorie }}"
            class="flex-shrink-0 {{ $tons[$i % count($tons)] }} border rounded-lg
                   px-5 py-4 text-center transition-all hover:shadow-sm group min-w-28">
            <x-icone-categorie :nom="$cat->nomCategorie"
                class="w-6 h-6 mx-auto mb-2 text-primary group-hover:text-primary-dark transition-colors" />
            <div class="text-xs font-semibold text-gray-700 group-hover:text-primary-dark
                        transition-colors whitespace-nowrap">
                {{ $cat->nomCategorie }}
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- ═══════════════════ LITTÉRATURE AFRICAINE — Vedette ═══ --}}
@if($litteratureAfricaine->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-end mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-3 h-3 bg-amber-400 rounded-full"></div>
                    <span class="text-xs font-semibold text-amber-600 uppercase
                                 tracking-widest">
                        Sélection
                    </span>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Littérature Africaine</h2>
                <p class="text-gray-400 text-xs mt-1">
                    Les grands auteurs du continent
                </p>
            </div>
            @if($categorielit)
            <a href="{{ route('client.catalogue') }}?categorie={{ $categorielit->idCategorie }}"
                class="text-sm text-primary font-medium hover:underline">
                Voir toute la collection <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
            </a>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($litteratureAfricaine as $article)
                <x-carte-article :article="$article" :estFavori="false" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════ NOUVEAUTÉS ═══ --}}
@if($nouveautes->count() > 0)
<section class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex justify-between items-end mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="w-3 h-3 bg-primary rounded-full"></div>
                <span class="text-xs font-semibold text-primary uppercase tracking-widest">
                    Vient d'arriver
                </span>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Nouveautés</h2>
        </div>
        <a href="{{ route('client.catalogue') }}"
            class="text-sm text-primary font-medium hover:underline">
            Tout voir <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
        </a>
    </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($nouveautes as $article)
                <x-carte-article :article="$article" :estFavori="false" />
            @endforeach
        </div>
    
</section>
@endif

{{-- ═══════════════════════════════════ CTA FINAL ═══ --}}
<section class="mx-6 mb-12 rounded-3xl overflow-hidden"
         style="background: linear-gradient(135deg, #1A4731 0%, #2E7D52 100%)">
    <div class="px-12 py-12 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white mb-2">
                Prêt à commander ?
            </h2>
            <p class="text-primary-pale text-sm max-w-md">
                Livraison rapide à Dakar et en banlieue.
                Paiement sécurisé via Wave ou Orange Money.
            </p>
        </div>
        <div class="flex items-center gap-4 flex-shrink-0">
            <a href="{{ route('client.catalogue') }}"
                class="bg-white text-primary-dark font-semibold px-6 py-3
                       rounded-xl hover:bg-primary-pale transition-colors text-sm shadow-lg">
                Commander maintenant
            </a>
            @guest
            <a href="{{ route('register') }}"
                class="border border-white/40 text-white font-medium px-6 py-3
                       rounded-xl hover:bg-white/10 transition-colors text-sm">
                Créer un compte
            </a>
            @endguest
        </div>
    </div>
</section>

@endsection