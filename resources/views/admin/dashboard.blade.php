@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')

@section('content')

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('admin.rapports') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Ventes du jour</div>
        <div class="text-2xl font-semibold text-primary-dark">
            {{ number_format($ventesJour, 0, ',', ' ') }} F
        </div>
        <div class="text-xs text-primary mt-1">Commandes validées</div>
    </a>
    <a href="{{ route('admin.rapports') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Commandes du mois</div>
        <div class="text-2xl font-semibold text-primary-dark">{{ $commandesMois }}</div>
        <div class="text-xs text-primary mt-1">{{ now()->locale('fr')->monthName }}</div>
    </a>
    <a href="{{ route('admin.articles') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Articles en stock</div>
        <div class="text-2xl font-semibold text-primary-dark">
            {{ number_format($totalArticles, 0, ',', ' ') }}
        </div>
        @if($alertes->count() > 0)
            <div class="text-xs text-amber-ca mt-1">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $alertes->count() }} alerte(s) active(s)
            </div>
        @else
            <div class="text-xs text-primary mt-1">Stock OK</div>
        @endif
    </a>
    <a href="{{ route('admin.utilisateurs') }}" class="bg-white border border-primary-pale rounded-xl p-4 hover:border-primary hover:shadow-sm transition-all">
        <div class="text-xs text-gray-400 mb-1">Clients inscrits</div>
        <div class="text-2xl font-semibold text-primary-dark">{{ $totalClients }}</div>
        
    </a>
</div>

{{-- Graphiques --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    {{-- Ventes par mois --}}
    <div class="lg:col-span-2 bg-white border border-primary-pale rounded-xl p-4">
        <div class="text-sm font-semibold text-primary-dark mb-4">
            Ventes mensuelles (6 derniers mois)
        </div>
        <div class="flex items-end gap-2 h-32">
            @php $maxVente = $ventesMois->max('total') ?: 1; @endphp
            @forelse($ventesMois as $v)
                @php
                    $hauteur = round(($v->total / $maxVente) * 100);
                    $moisNom = \Carbon\Carbon::createFromDate($v->annee, $v->mois, 1)
                                ->locale('fr')->isoFormat('MMM');
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-xs text-primary-dark font-medium">
                        {{ number_format($v->total/1000, 0) }}k
                    </span>
                    <div class="w-full rounded-t"
                         style="height:{{ $hauteur }}%; background:#1A4731; min-height:4px;">
                    </div>
                    <span class="text-xs text-gray-400">{{ $moisNom }}</span>
                </div>
            @empty
                <div class="w-full flex items-center justify-center text-xs text-gray-400">
                    Aucune vente enregistrée
                </div>
            @endforelse
        </div>
    </div>

    {{-- Ventes par catégorie --}}
    <div class="bg-white border border-primary-pale rounded-xl p-4">
        <div class="text-sm font-semibold text-primary-dark mb-4">
            Top catégories
        </div>
        @php $maxCat = $ventesCategorie->max('total') ?: 1; @endphp
        <div class="flex flex-col gap-2">
            @forelse($ventesCategorie as $cat)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 truncate">{{ $cat->nomCategorie }}</span>
                        <span class="text-primary-dark font-medium ml-2">{{ $cat->total }}</span>
                    </div>
                    <div class="w-full bg-primary-pale rounded h-1.5">
                        <div class="bg-primary h-1.5 rounded"
                             style="width:{{ round(($cat->total/$maxCat)*100) }}%">
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400">Aucune donnée</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Comptes verrouillés (échecs de connexion répétés) --}}
@if($comptesVerrouilles->count() > 0)
<div class="bg-white border border-red-200 rounded-xl overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex justify-between items-center">
        <span class="text-sm font-semibold text-red-700">
            <x-heroicon-o-lock-closed class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $comptesVerrouilles->count() }} compte(s) actuellement verrouillé(s)
        </span>
        <div class="flex items-center gap-3">
            <span class="text-xs text-red-500">3 échecs de connexion consécutifs</span>
            <a href="{{ route('admin.utilisateurs') }}" class="text-xs text-red-500 hover:underline">Voir tout <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
    </div>
    <div class="divide-y divide-primary-pale">
        @foreach($comptesVerrouilles as $u)
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <div class="min-w-0">
                <div class="text-xs font-medium text-gray-800 truncate">
                    {{ $u->prenom }} {{ $u->nom }}
                    <span class="text-gray-400 font-normal">({{ $u->roles->first()->name ?? 'client' }})</span>
                </div>
                <div class="text-xs text-gray-400 truncate">
                    {{ $u->email }} · débloqué automatiquement à {{ $u->bloqueJusqua->format('H:i:s') }}
                </div>
            </div>
            <form method="POST" action="{{ route('admin.utilisateur.debloquer', $u->idUtilisateur) }}"
                  onsubmit="return confirm('Débloquer ce compte immédiatement ?');" class="flex-shrink-0">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="text-xs font-medium text-white bg-primary hover:bg-primary-light
                           px-3 py-1.5 rounded-lg transition">
                    Débloquer
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Demandes d'annulation en attente de décision --}}
@if($demandesAnnulation->count() > 0)
<div class="bg-white border border-orange-200 rounded-xl overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-orange-100 bg-orange-50 flex justify-between items-center">
        <span class="text-sm font-semibold text-orange-700">
            <x-heroicon-o-clock class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $demandesAnnulation->count() }} demande(s) d'annulation en attente
        </span>
    </div>
    <div class="divide-y divide-primary-pale">
        @foreach($demandesAnnulation as $cmd)
        <a href="{{ route('admin.commande.annulation', $cmd->idCommande) }}"
           class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-orange-50 transition">
            <div class="min-w-0">
                <div class="text-xs font-medium text-gray-800">
                    {{ $cmd->numeroCommande }} — {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </div>
                <div class="text-xs text-gray-400 truncate max-w-md">
                    {{ $cmd->motifAnnulation ?? 'Aucun motif précisé' }}
                </div>
            </div>
            <span class="text-xs font-medium text-orange-600 flex-shrink-0">Examiner <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></span>
        </a>
        @endforeach
    </div>
</div>
@endif
@if($commandesARembourser->count() > 0)
<div class="bg-white border border-red-200 rounded-xl overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex justify-between items-center">
        <span class="text-sm font-semibold text-red-700">
            <x-heroicon-o-banknotes class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $commandesARembourser->count() }} commande(s) en attente de remboursement
        </span>
    </div>
    <div class="divide-y divide-primary-pale">
        @foreach($commandesARembourser as $cmd)
        <a href="{{ route('admin.commande.annulation', $cmd->idCommande) }}"
           class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-red-50 transition">
            <div class="min-w-0">
                <div class="text-xs font-medium text-gray-800">
                    {{ $cmd->numeroCommande }} — {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </div>
                <div class="text-xs text-gray-400">
                    {{ number_format($cmd->paiement->montant, 0, ',', ' ') }} F CFA
                    ({{ strtoupper($cmd->paiement->modePaiement) }})
                </div>
            </div>
            <span class="text-xs font-medium text-red-600 flex-shrink-0">Rembourser <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></span>
        </a>
        @endforeach
    </div>
</div>
@endif


@if($commandesProblemeStock->count() > 0)
<div class="bg-white border border-red-200 rounded-xl overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex justify-between items-center">
        <span class="text-sm font-semibold text-red-700">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> {{ $commandesProblemeStock->count() }} commande(s) payée(s) avec rupture de stock
        </span>
    </div>
    <div class="divide-y divide-primary-pale">
        @foreach($commandesProblemeStock as $cmd)
        <div class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-red-50 transition">
            <a href="{{ route('admin.commande.annulation', $cmd->idCommande) }}" class="min-w-0">
                <div class="text-xs font-medium text-gray-800">
                    {{ $cmd->numeroCommande }} — {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                </div>
                <div class="text-xs text-gray-400 truncate max-w-md">
                    {{ $cmd->problemeStockDetails }}
                </div>
            </a>
            <a href="{{ route('admin.commande.annulation', $cmd->idCommande) }}"
               class="text-xs font-medium text-red-600 flex-shrink-0">Examiner <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Top articles vendus & Ruptures imminentes --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    {{-- Top 5 articles vendus --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
            <span class="text-sm font-semibold text-primary-dark">Top 5 articles vendus</span>
            <a href="{{ route('admin.rapports') }}" class="text-xs text-primary hover:underline">Voir tout <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
        <div class="divide-y divide-primary-pale">
            @forelse($topArticles as $i => $art)
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-6 h-6 rounded-full bg-primary-pale text-primary-dark text-xs
                            font-semibold flex items-center justify-center flex-shrink-0">
                    {{ $i + 1 }}
                </div>
                @if($art->image)
                    <img src="{{ Storage::url($art->image) }}" alt=""
                         class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                @else
                    <div class="w-8 h-8 rounded-lg bg-primary-bg flex-shrink-0"></div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-gray-800 truncate">
                        {{ $art->designation }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $art->quantiteVendue }} vendu(s) ·
                        {{ number_format($art->chiffreAffaires, 0, ',', ' ') }} F
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-4 text-xs text-gray-400 text-center">
                Aucune vente enregistrée pour le moment
            </div>
            @endforelse
        </div>
    </div>

    {{-- Ruptures imminentes --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
            <span class="text-sm font-semibold text-primary-dark">Ruptures imminentes</span>
            <a href="{{ route('admin.articles') }}" class="text-xs text-primary hover:underline">Voir tout <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
        <div class="divide-y divide-primary-pale">
            @forelse($rupturesImminentes as $r)
                @php $marge = $r->article->quantiteStock - $r->quantiteMinimale; @endphp
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0
                                {{ $marge <= 0 ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700' }}">
                        @if($marge <= 0)
                            <x-heroicon-o-no-symbol class="w-4 h-4" />
                        @else
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium text-gray-800 truncate">
                            {{ $r->article->designation }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $r->article->quantiteStock }} en stock · seuil {{ $r->quantiteMinimale }}
                        </div>
                    </div>
                </div>
            @empty
            <div class="px-4 py-4 text-xs text-gray-400 text-center">
                Aucun article proche de la rupture
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Dernières commandes & Alertes --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Dernières commandes --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
            <span class="text-sm font-semibold text-primary-dark">Dernières commandes</span>
           
        </div>
        <table class="w-full">
            <thead>
                <tr class="bg-primary-bg">
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">N°</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Client</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Montant</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dernieresCommandes as $cmd)
                <tr class="border-t border-primary-pale hover:bg-primary-bg">
                    <td class="px-4 py-2 text-xs font-medium text-primary-dark">
                        {{ $cmd->numeroCommande }}
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-600">
                        {{ $cmd->utilisateur->prenom }} {{ $cmd->utilisateur->nom }}
                    </td>
                    <td class="px-4 py-2 text-xs font-medium text-primary-dark">
                        {{ number_format($cmd->montantTotal, 0, ',', ' ') }} F
                    </td>
                    <td class="px-4 py-2">
                        @php
                            $colors = [
                                'en_attente'  => 'bg-yellow-100 text-yellow-700',
                                'validee'     => 'bg-primary-pale text-primary-dark',
                                'en_livraison'=> 'bg-blue-100 text-blue-700',
                                'livree'      => 'bg-primary-pale text-primary',
                                'annulee'     => 'bg-red-100 text-red-700',
                            ];
                            $labels = [
                                'en_attente'  => 'En attente',
                                'validee'     => 'Validée',
                                'en_livraison'=> 'En livraison',
                                'livree'      => 'Livrée',
                                'annulee'     => 'Annulée',
                            ];
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                                     {{ $colors[$cmd->statut] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $labels[$cmd->statut] ?? $cmd->statut }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">
                        Aucune commande enregistrée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Alertes & Mouvements --}}
    <div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-primary-pale flex justify-between items-center">
            <span class="text-sm font-semibold text-primary-dark">Alertes & Notifications</span>
            <a href="{{ route('admin.mouvements') }}" class="text-xs text-primary hover:underline">Voir tout <x-heroicon-o-arrow-right class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></a>
        </div>
        <div class="divide-y divide-primary-pale">
            @forelse($alertes as $alerte)
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center
                            justify-center text-red-600 text-xs flex-shrink-0"><x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /></div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-gray-800 truncate">
                        Stock critique : {{ $alerte->article->designation }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $alerte->article->quantiteStock }} unité(s) —
                        seuil : {{ $alerte->quantiteMinimale }}
                    </div>
                </div>
            </div>
            @empty
            <div class="px-4 py-4 text-xs text-gray-400 text-center">
                Aucune alerte active
            </div>
            @endforelse

            @forelse($derniersMouvements as $mouv)
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-7 h-7 rounded-lg
                    {{ $mouv->typeMouvement === 'entree' ? 'bg-primary-pale text-primary' : 'bg-red-50 text-red-500' }}
                    flex items-center justify-center text-xs flex-shrink-0">
                    @if($mouv->typeMouvement === 'entree')
                        <x-heroicon-o-arrow-down class="w-4 h-4" />
                    @else
                        <x-heroicon-o-arrow-up class="w-4 h-4" />
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-gray-800 truncate">
                        {{ ucfirst($mouv->typeMouvement) }} — {{ $mouv->article->designation }}
                    </div>
                    <div class="text-xs text-gray-400">
                        Qté : {{ $mouv->quantite }} ·
                        {{ $mouv->utilisateur ? $mouv->utilisateur->prenom . ' ' . $mouv->utilisateur->nom : 'Système' }}
                    </div>
                </div>
            </div>
            @empty
            @endforelse
        </div>
    </div>
</div>

@endsection