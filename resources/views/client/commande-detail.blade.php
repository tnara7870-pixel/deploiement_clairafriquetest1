@extends('layouts.client')
@section('title', 'Commande ' . $commande->numeroCommande)

@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">
    <div class="mb-4">
        <a href="{{ route('client.commandes') }}"
            class="text-xs text-primary hover:underline">
            <x-heroicon-o-arrow-left class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-3px]" /> Mes commandes
        </a>
    </div>

    <div class="bg-white border border-primary-pale rounded-xl p-6 mb-4">
        @php
            $statutColors = [
                'en_attente'         => 'bg-yellow-100 text-yellow-700',
                'validee'            => 'bg-primary-pale text-primary-dark',
                'en_livraison'       => 'bg-blue-100 text-blue-700',
                'livree'             => 'bg-primary-pale text-primary',
                'annulee'            => 'bg-red-100 text-red-600',
                'demande_annulation' => 'bg-orange-100 text-orange-700',
            ];
            $statutLabels = [
                'en_attente'         => 'En attente',
                'validee'            => 'Validée',
                'en_livraison'       => 'En livraison',
                'livree'             => 'Livrée',
                'annulee'            => 'Annulée',
                'demande_annulation' => 'Annulation en cours',
            ];
        @endphp

        <div class="flex justify-between items-start mb-5">
            <div>
                <h1 class="text-base font-semibold text-primary-dark">
                    {{ $commande->numeroCommande }}
                </h1>
                <p class="text-xs text-gray-400 mt-0.5">
                    Passée le {{ \Carbon\Carbon::parse($commande->dateCommande)->format('d/m/Y à H:i') }}
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                {{-- Bouton télécharger facture --}}
                <a href="{{ route('client.commande.facture', $commande->idCommande) }}"
                    class="bg-primary-pale text-primary-dark text-xs font-medium px-3 py-1.5
                           rounded-lg hover:bg-primary hover:text-white transition-colors
                           flex items-center gap-1">
                    <x-heroicon-o-document-text class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Télécharger la facture
                </a>
                <span class="text-xs px-3 py-1 rounded-full font-medium {{ $statutColors[$commande->statut] ?? '' }}">
                    {{ $statutLabels[$commande->statut] ?? $commande->statut }}
                </span>
            </div>
        </div>

        {{-- Suivi de commande --}}
        <div class="mb-5 pb-5 border-b border-primary-pale">
            @include('client._suivi-commande', ['commande' => $commande])
        </div>

        @if($commande->statut === 'demande_annulation')
            <div class="bg-orange-50 border border-orange-200 text-orange-700 text-xs rounded-lg px-4 py-3 mb-4">
                <x-heroicon-o-clock class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Votre demande d'annulation est en cours d'examen par notre équipe.
                @if($commande->motifAnnulation)
                    <br><span class="text-orange-600">Motif indiqué : {{ $commande->motifAnnulation }}</span>
                @endif
            </div>
        @elseif(in_array($commande->statut, ['en_attente', 'validee']))
            <div class="mb-4">
                <button type="button" onclick="document.getElementById('modal-annulation').classList.remove('hidden')"
                    class="text-xs text-red-600 hover:underline">
                    Demander l'annulation de cette commande
                </button>
            </div>
        @endif

        @if($commande->statut === 'annulee' && $commande->remboursements->isNotEmpty())
            <div class="bg-primary-pale/40 border border-primary-pale text-primary-dark text-xs rounded-lg px-4 py-3 mb-4">
                <x-heroicon-o-arrow-uturn-left class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Remboursement effectué le
                {{ $commande->remboursements->first()->dateRemboursement->format('d/m/Y') }}
                ({{ number_format($commande->remboursements->first()->montant, 0, ',', ' ') }} F CFA).
            </div>
        @endif

        {{-- Articles --}}
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">
            Articles
        </div>
        <div class="divide-y divide-primary-pale mb-4">
            @foreach($commande->ligneCommandes as $ligne)
                <div class="flex justify-between items-center py-2.5">
                    <div>
                        <div class="text-sm text-gray-800">{{ $ligne->article->designation }}</div>
                        <div class="text-xs text-gray-400">
                            {{ number_format($ligne->prixUnitaire, 0, ',', ' ') }} F × {{ $ligne->quantite }}
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-primary-dark">
                        {{ number_format($ligne->sousTotal(), 0, ',', ' ') }} F
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-between items-center pt-3 border-t border-primary-pale">
            <span class="font-semibold text-gray-600">Total</span>
            <span class="text-lg font-semibold text-primary-dark">
                {{ number_format($commande->montantTotal, 0, ',', ' ') }} F
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        {{-- Livraison --}}
        <div class="bg-white border border-primary-pale rounded-xl p-5">
            <div class="text-sm font-semibold text-primary-dark mb-3">Livraison</div>
            @if($commande->livraison)
                <div class="text-sm text-gray-700 mb-1">
                    @if($commande->livraison->modeLivraison === 'domicile')
                        <x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> À domicile
                    @else
                        <x-heroicon-o-building-storefront class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retrait en boutique
                        @if($commande->livraison->pointVente)
                            — {{ $commande->livraison->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                        @endif
                    @endif
                </div>
                @if($commande->livraison->adresseLivraison)
                    <div class="text-xs text-gray-500 mb-2">
                        {{ $commande->livraison->adresseLivraison }}
                    </div>
                @endif
                @php
                    $lLabels = [
                        'preparee'     => 'En préparation',
                        'en_livraison' => 'En livraison',
                        'livree'       => 'Livrée',
                        'annulee'      => 'Annulée',
                    ];
                @endphp
                <div class="text-xs font-medium text-primary-dark">
                    {{ $lLabels[$commande->livraison->statutLivraison] ?? '' }}
                </div>
            @endif
        </div>

        {{-- Paiement --}}
        <div class="bg-white border border-primary-pale rounded-xl p-5">
            <div class="text-sm font-semibold text-primary-dark mb-3">Paiement</div>
            @if($commande->paiement)
                <div class="text-sm text-gray-700 mb-1">
                    {{ strtoupper(str_replace('_', ' ', $commande->paiement->modePaiement)) }}
                </div>
                <div class="text-xs text-gray-400 mb-2 font-mono">
                    {{ $commande->paiement->referenceTransaction ?? '—' }}
                </div>
                @php
                    $pLabels = [
                        'en_attente' => '⏳ En attente de validation',
                        'valide'     => 'Paiement validé',
                        'echoue'     => 'Paiement échoué',
                        'rembourse'  => 'Remboursé',
                    ];
                @endphp
                <div class="text-xs font-medium text-primary-dark">
                    {{ $pLabels[$commande->paiement->statutPaiement] ?? '' }}
                </div>
            @endif
        </div>
    </div>
</div>

@if(in_array($commande->statut, ['en_attente', 'validee']))
<div id="modal-annulation" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="text-sm font-semibold text-primary-dark mb-1">Demander l'annulation</h2>
        <p class="text-xs text-gray-500 mb-4">
            Votre demande sera examinée par notre équipe avant tout remboursement ou remise en stock.
        </p>
        <form method="POST" action="{{ route('client.commande.demanderAnnulation', $commande->idCommande) }}">
            @csrf
            <textarea name="motif" rows="3" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-primary bg-primary-bg"
                placeholder="Expliquez brièvement la raison de votre demande…"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-annulation').classList.add('hidden')"
                    class="text-xs text-gray-500 px-3 py-2 rounded-lg hover:bg-gray-100">
                    Annuler
                </button>
                <button type="submit"
                    class="text-xs font-medium text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition">
                    Envoyer la demande
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection