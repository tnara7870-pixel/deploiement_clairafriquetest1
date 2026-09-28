@extends('layouts.admin')
@section('title', 'Examen de la demande d\'annulation')
@section('page_title', 'Examen de la demande d\'annulation — ' . $commande->numeroCommande)

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.dashboard') }}" class="text-xs text-primary hover:underline font-medium">
        <x-heroicon-o-arrow-left class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retour au tableau de bord
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 flex flex-col gap-6">
        <div class="bg-white border border-primary-pale rounded-xl p-5 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-lg font-bold text-primary-dark">
                        Commande n° {{ $commande->numeroCommande }}
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">
                        Passée le {{ \Carbon\Carbon::parse($commande->dateCommande)->format('d/m/Y à H:i') }}
                    </p>
                </div>
                @php
                    $statutColors = [
                        'en_attente'         => 'bg-yellow-100 text-yellow-800',
                        'validee'            => 'bg-blue-100 text-blue-800',
                        'en_livraison'       => 'bg-indigo-100 text-indigo-800',
                        'livree'             => 'bg-green-100 text-green-800',
                        'annulee'            => 'bg-red-100 text-red-700',
                        'demande_annulation' => 'bg-orange-100 text-orange-700',
                    ];
                    $statutLabels = [
                        'en_attente'         => 'En attente',
                        'validee'            => 'Validée',
                        'en_livraison'       => 'En livraison',
                        'livree'             => 'Livrée',
                        'annulee'            => 'Annulée',
                        'demande_annulation' => 'Demande d\'annulation',
                    ];
                @endphp
                <div class="flex items-center gap-3">
                    <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $statutColors[$commande->statut] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $statutLabels[$commande->statut] ?? $commande->statut }}
                    </span>
                    <a href="{{ route('admin.commande.annulation', $commande->idCommande) }}"
                       class="bg-primary-pale text-primary-dark text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-primary hover:text-white transition-colors">
                        Actualiser
                    </a>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-5 border border-gray-100">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Informations Client</div>
                <div class="text-sm font-semibold text-gray-800">
                    {{ $commande->utilisateur->prenom }} {{ $commande->utilisateur->nom }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $commande->utilisateur->email }}</div>
                <div class="text-xs text-gray-500">{{ $commande->utilisateur->telephone ?? 'Téléphone non renseigné' }}</div>
            </div>

            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                Articles commandés ({{ $commande->ligneCommandes->count() }})
            </div>
            <div class="divide-y divide-gray-100 border-t border-b border-gray-100 mb-4">
                @foreach($commande->ligneCommandes as $ligne)
                <div class="flex justify-between items-center py-3">
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $ligne->article->designation }}</div>
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

            @php
                $fraisLivraison = ($commande->livraison && $commande->livraison->modeLivraison === 'domicile')
                    ? config('claireafrique.frais_livraison_domicile', 2000)
                    : 0;
                $sousTotal = $commande->ligneCommandes->sum(fn($l) => $l->sousTotal());
            @endphp
            <div class="space-y-1.5 pt-2">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Sous-total</span>
                    <span>{{ number_format($sousTotal, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex justify-between text-xs text-gray-500 pb-2 border-b border-gray-100">
                    <span>Frais de livraison</span>
                    <span>{{ $fraisLivraison > 0 ? number_format($fraisLivraison, 0, ',', ' ') . ' F' : 'Gratuit' }}</span>
                </div>
                <div class="flex justify-between items-center pt-2">
                    <span class="font-bold text-gray-700">Total Commande</span>
                    <span class="text-xl font-extrabold text-primary-dark">
                        {{ number_format($commande->montantTotal, 0, ',', ' ') }} F CFA
                    </span>
                </div>
            </div>
        </div>

        @if($commande->statut === 'demande_annulation')
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-xs font-semibold text-orange-700 uppercase tracking-wider mb-2">
                <x-heroicon-o-clock class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Demande d'annulation du client
            </h3>
            @if($commande->motifAnnulation)
            <p class="text-sm text-gray-700 mb-4 italic">« {{ $commande->motifAnnulation }} »</p>
            @endif
            <p class="text-xs text-gray-500 mb-4">
                Si vous validez : le stock est restitué automatiquement.
                Si un paiement a été encaissé, un remboursement manuel reste à enregistrer séparément.
            </p>
            <div class="flex gap-3 mb-1">
                <form method="POST" action="{{ route('commande.annulation.valider', $commande->idCommande) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        onclick="return confirm('Valider l\'annulation ? Le stock sera restitué immédiatement.');"
                        class="bg-red-600 text-white font-medium px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">
                        Valider l'annulation
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('modal-refus').classList.remove('hidden')"
                    class="bg-white border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                    Refuser la demande
                </button>
            </div>

            <div id="modal-refus" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
                <div class="bg-white rounded-xl p-6 w-full max-w-md">
                    <h2 class="text-sm font-semibold text-primary-dark mb-1">Refuser la demande d'annulation</h2>
                    <p class="text-xs text-gray-500 mb-4">
                        Le motif sera communiqué au client et conservé dans l'historique.
                    </p>
                    <form method="POST" action="{{ route('commande.annulation.refuser', $commande->idCommande) }}">
                        @csrf @method('PATCH')
                        <textarea name="motifRefus" rows="3" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4
                                   focus:outline-none focus:border-primary bg-primary-bg"
                            placeholder="Ex. la commande est déjà en cours de préparation…"></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="document.getElementById('modal-refus').classList.add('hidden')"
                                class="text-xs text-gray-500 px-3 py-2 rounded-lg hover:bg-gray-100">
                                Annuler
                            </button>
                            <button type="submit"
                                class="text-xs font-medium text-white bg-gray-800 hover:bg-black px-4 py-2 rounded-lg transition">
                                Confirmer le refus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($commande->necessiteRemboursement())
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-xs font-semibold text-red-700 uppercase tracking-wider mb-2">
                <x-heroicon-o-banknotes class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Remboursement à effectuer
            </h3>
            <p class="text-xs text-gray-600 mb-4">
                Cette commande est annulée et le paiement a été encaissé. Effectuez le remboursement
                PayDunya et enregistrez-le ici.
            </p>
            <form method="POST" action="{{ route('commande.remboursement.enregistrer', $commande->idCommande) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Référence de la transaction *</label>
                        <input type="text" name="referenceTransaction" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Date du remboursement *</label>
                        <input type="date" name="dateRemboursement" required value="{{ now()->format('Y-m-d') }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Montant remboursé (F CFA) *</label>
                    <input type="number" name="montant" required min="1" step="1"
                        value="{{ $commande->paiement->montant }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Commentaire (facultatif)</label>
                    <textarea name="commentaire" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary bg-white"></textarea>
                </div>
                <button type="submit" class="bg-red-600 text-white font-medium px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">
                    Enregistrer le remboursement
                </button>
            </form>
        </div>
        @endif

        @if($commande->remboursements->isNotEmpty())
        <div class="bg-white border border-primary-pale rounded-xl p-5 shadow-sm">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Remboursements enregistrés</h3>
            @foreach($commande->remboursements as $rb)
            <div class="text-sm text-gray-700 mb-1">
                {{ number_format($rb->montant, 0, ',', ' ') }} F CFA — réf. {{ $rb->referenceTransaction }}
                le {{ $rb->dateRemboursement->format('d/m/Y') }}
            </div>
            @if($rb->commentaire)
            <div class="text-xs text-gray-400 italic">{{ $rb->commentaire }}</div>
            @endif
            @endforeach
        </div>
        @endif

        <div class="bg-white border border-primary-pale rounded-xl p-5 shadow-sm">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Historique de la commande</h3>
            <div class="space-y-3">
                @foreach($commande->historique->reverse() as $evenement)
                @php
                    $actionLabels = [
                        'creation'                  => 'Commande créée',
                        'changement_statut'         => 'Statut modifié : ' . ($evenement->statutPrecedent ?? '—') . ' <x-heroicon-o-arrow-right class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> ' . $evenement->statutNouveau,
                        'demande_annulation'        => "Demande d'annulation du client",
                        'annulation_validee'        => 'Annulation validée',
                        'annulation_refusee'        => 'Annulation refusée',
                        'remboursement_enregistre'  => 'Remboursement enregistré',
                        'probleme_stock_resolu'     => 'Signalement de rupture de stock traité',
                    ];
                @endphp
                <div class="flex gap-3 text-xs">
                    <span class="text-gray-400 flex-shrink-0 w-32">{{ $evenement->dateAction->format('d/m/Y H:i') }}</span>
                    <div>
                        <div class="font-medium text-gray-700">
                            {{ $actionLabels[$evenement->action] ?? $evenement->action }}
                            @if($evenement->utilisateur)
                                <span class="text-gray-400 font-normal">— {{ $evenement->utilisateur->prenom }} {{ $evenement->utilisateur->nom }}</span>
                            @endif
                        </div>
                        @if($evenement->commentaire)
                        <div class="text-gray-500 italic">{{ $evenement->commentaire }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <div class="bg-white border border-primary-pale rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Livraison</span>
                    @if($commande->livraison)
                        @php
                            $lColors = [
                                'preparee'     => 'bg-gray-100 text-gray-700',
                                'en_livraison' => 'bg-blue-100 text-blue-700',
                                'livree'       => 'bg-green-100 text-green-700',
                                'annulee'      => 'bg-red-100 text-red-600',
                            ];
                        @endphp
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $lColors[$commande->livraison->statutLivraison] ?? '' }}">
                            {{ ucfirst($commande->livraison->statutLivraison) }}
                        </span>
                    @endif
                </div>

                @if($commande->livraison)
                    <div class="space-y-3 mb-4">
                        <div>
                            <div class="text-xs text-gray-400">Mode de retrait</div>
                            <div class="text-sm font-medium text-gray-800">
                                @if($commande->livraison->modeLivraison === 'domicile')<x-heroicon-o-home class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> À domicile @else<x-heroicon-o-building-storefront class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" /> Retrait en boutique @if($commande->livraison->pointVente) — {{ $commande->livraison->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville' }} @endif @endif
                            </div>
                        </div>
                        @if($commande->livraison->adresseLivraison)
                        <div>
                            <div class="text-xs text-gray-400">Adresse</div>
                            <div class="text-xs text-gray-700 font-medium">{{ $commande->livraison->adresseLivraison }}</div>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-gray-400 my-4">Aucune donnée de livraison.</p>
                @endif
            </div>

            @if($commande->statut === 'demande_annulation')
            <p class="text-xs text-orange-600 italic pt-3 border-t border-gray-100">
                Modification bloquée : une demande d'annulation est en cours de traitement.
            </p>
            @elseif($commande->livraison && !in_array($commande->livraison->statutLivraison, ['livree','annulee']))
            <form method="POST" action="{{ route('commande.livraison.update', $commande->livraison->idLivraison) }}" class="pt-3 border-t border-gray-100">
                @csrf @method('PATCH')
                <select name="statutLivraison" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-2 focus:outline-none bg-white">
                    <option value="preparee" {{ $commande->livraison->statutLivraison == 'preparee' ? 'selected' : '' }}>Préparée</option>
                    @if($commande->livraison->modeLivraison !== 'boutique')
                    <option value="en_livraison" {{ $commande->livraison->statutLivraison == 'en_livraison' ? 'selected' : '' }}>En livraison</option>
                    @endif
                    <option value="livree" {{ $commande->livraison->statutLivraison == 'livree' ? 'selected' : '' }}>Livrée</option>
                </select>
                <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded-lg text-xs font-medium hover:bg-black transition-colors">
                    Mettre à jour la livraison
                </button>
            </form>
            @endif
        </div>

        <div class="bg-white border border-primary-pale rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Paiement</span>
                    @if($commande->paiement)
                        @php
                            $pColors = [
                                'en_attente' => 'bg-yellow-100 text-yellow-800',
                                'valide'     => 'bg-green-100 text-green-700',
                                'echoue'     => 'bg-red-100 text-red-600',
                                'rembourse'  => 'bg-gray-100 text-gray-600',
                            ];
                        @endphp
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $pColors[$commande->paiement->statutPaiement] ?? '' }}">
                            {{ ucfirst($commande->paiement->statutPaiement) }}
                        </span>
                    @endif
                </div>

                @if($commande->paiement)
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400">Mode</span>
                            <span class="text-xs font-bold text-gray-700">{{ strtoupper(str_replace('_', ' ', $commande->paiement->modePaiement)) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400">Montant</span>
                            <span class="text-xs font-bold text-primary-dark">{{ number_format($commande->paiement->montant, 0, ',', ' ') }} F</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400">Réf.</span>
                            <span class="text-xs font-mono text-gray-600">{{ $commande->paiement->referenceTransaction ?? '—' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400 my-4">Aucun paiement associé.</p>
                @endif
            </div>

            @if($commande->statut === 'demande_annulation')
            <p class="text-xs text-orange-600 italic pt-3 border-t border-gray-100">
                Modification bloquée : une demande d'annulation est en cours de traitement.
            </p>
            @endif
        </div>
    </div>
</div>

@endsection
