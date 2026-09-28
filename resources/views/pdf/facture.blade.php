<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1C2B22; }

        .header { background: #1A4731; color: white; padding: 24px; margin-bottom: 24px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .logo { font-size: 20px; font-weight: bold; }
        .tagline { font-size: 11px; color: #A8D5BA; margin-top: 2px; }
        .facture-title { text-align: right; }
        .facture-title h2 { font-size: 18px; font-weight: bold; }
        .facture-title p { font-size: 11px; color: #A8D5BA; margin-top: 2px; }

        .infos { display: flex; justify-content: space-between; margin-bottom: 24px; padding: 0 4px; }
        .info-block { width: 48%; }
        .info-block h4 { font-size: 10px; text-transform: uppercase; color: #5A6B63;
                         letter-spacing: 0.08em; margin-bottom: 6px; border-bottom: 1px solid #D6EDDF;
                         padding-bottom: 4px; }
        .info-block p { font-size: 11px; margin-bottom: 3px; color: #1C2B22; }
        .info-block .highlight { font-weight: bold; color: #1A4731; }

        .statut { display: inline-block; padding: 3px 10px; border-radius: 12px;
                  font-size: 10px; font-weight: bold; margin-top: 4px; }
        .statut-valide  { background: #D6EDDF; color: #1A4731; }
        .statut-attente { background: #FFF3CD; color: #856404; }
        .statut-annule  { background: #FDECEA; color: #C0392B; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #1A4731; color: white; }
        thead th { padding: 9px 12px; text-align: left; font-size: 11px; font-weight: bold; }
        tbody tr { border-bottom: 1px solid #F0F4F1; }
        tbody tr:nth-child(even) { background: #F7FAF8; }
        tbody td { padding: 9px 12px; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totaux { width: 260px; margin-left: auto; margin-bottom: 24px; }
        .totaux table { margin-bottom: 0; }
        .totaux tbody tr { border-bottom: 1px solid #F0F4F1; }
        .totaux tbody td { padding: 7px 12px; }
        .total-final { background: #1A4731 !important; color: white; }
        .total-final td { font-weight: bold; font-size: 13px; color: white !important; }

        .livraison { background: #F7FAF8; border: 1px solid #D6EDDF; border-radius: 6px;
                     padding: 12px 16px; margin-bottom: 24px; }
        .livraison h4 { font-size: 10px; text-transform: uppercase; color: #5A6B63;
                        letter-spacing: 0.08em; margin-bottom: 8px; }
        .livraison-grid { display: flex; gap: 24px; }
        .livraison-item { flex: 1; }
        .livraison-item .label { font-size: 10px; color: #5A6B63; margin-bottom: 2px; }
        .livraison-item .value { font-size: 11px; font-weight: bold; color: #1A4731; }

        .footer { border-top: 2px solid #D6EDDF; padding-top: 14px; text-align: center; }
        .footer p { font-size: 10px; color: #5A6B63; margin-bottom: 3px; }
        .footer .merci { font-size: 13px; font-weight: bold; color: #1A4731; margin-bottom: 6px; }
        .footer .contact { color: #2E7D52; }

        .paiement-info { background: #D6EDDF; border-radius: 6px; padding: 10px 14px;
                         margin-bottom: 24px; display: flex; justify-content: space-between;
                         align-items: center; }
        .paiement-info .mode { font-size: 11px; color: #1A4731; }
        .paiement-info .ref { font-size: 10px; color: #5A6B63; font-family: monospace; }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="logo">ClaireAfrique</div>
                <div class="tagline">Librairie · Papeterie · Dakar, Sénégal</div>
            </div>
            <div class="facture-title">
                <h2>FACTURE</h2>
                <p>{{ $commande->numeroCommande }}</p>
                <p>{{ \Carbon\Carbon::parse($commande->dateCommande)->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- INFOS CLIENT + COMMANDE --}}
    <div class="infos">
        <div class="info-block">
            <h4>Facturé à</h4>
            <p class="highlight">{{ $commande->utilisateur->prenom }} {{ $commande->utilisateur->nom }}</p>
            <p>{{ $commande->utilisateur->email }}</p>
            <p>{{ $commande->utilisateur->telephone ?? '' }}</p>
        </div>
        <div class="info-block" style="text-align: right;">
            <h4>Détails de la commande</h4>
            <p><strong>N° :</strong> {{ $commande->numeroCommande }}</p>
            <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($commande->dateCommande)->format('d/m/Y') }}</p>
            <p>
                @php
                    $statutLabels = [
                        'en_attente'   => 'En attente',
                        'validee'      => 'Validée',
                        'en_livraison' => 'En livraison',
                        'livree'       => 'Livrée',
                        'annulee'      => 'Annulée',
                    ];
                    $statutClasses = [
                        'en_attente'   => 'statut-attente',
                        'validee'      => 'statut-valide',
                        'en_livraison' => 'statut-valide',
                        'livree'       => 'statut-valide',
                        'annulee'      => 'statut-annule',
                    ];
                @endphp
                <span class="statut {{ $statutClasses[$commande->statut] ?? 'statut-attente' }}">
                    {{ $statutLabels[$commande->statut] ?? $commande->statut }}
                </span>
            </p>
        </div>
    </div>

    {{-- ARTICLES --}}
    <table>
        <thead>
            <tr>
                <th style="width: 40%">Article</th>
                <th class="text-center" style="width: 15%">Référence</th>
                <th class="text-center" style="width: 15%">Qté</th>
                <th class="text-right" style="width: 15%">Prix unit.</th>
                <th class="text-right" style="width: 15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->ligneCommandes as $ligne)
            <tr>
                <td>{{ $ligne->article->designation }}</td>
                <td class="text-center">{{ $ligne->article->reference }}</td>
                <td class="text-center">{{ $ligne->quantite }}</td>
                <td class="text-right">{{ number_format($ligne->prixUnitaire, 0, ',', ' ') }} F</td>
                <td class="text-right">{{ number_format($ligne->sousTotal(), 0, ',', ' ') }} F</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAUX --}}
    {{-- TOTAUX --}}
@php
    $fraisLivraison = ($commande->livraison && $commande->livraison->modeLivraison === 'domicile')
        ? config('claireafrique.frais_livraison_domicile', 1500)
        : 0;
    $sousTotal = $commande->ligneCommandes->sum(fn($l) => $l->sousTotal());
@endphp

<div class="totaux">
    <table>
        <tbody>
            <tr>
                <td>Sous-total articles</td>
                <td class="text-right">
                    {{ number_format($sousTotal, 0, ',', ' ') }} F
                </td>
            </tr>
            <tr>
                <td>Frais de livraison</td>
                <td class="text-right">
                    @if($fraisLivraison > 0)
                        {{ number_format($fraisLivraison, 0, ',', ' ') }} F
                    @else
                        Gratuit (Retrait en boutique)
                        
                    @endif
                </td>
            </tr>
            <tr class="total-final">
                <td>TOTAL TTC</td>
                <td class="text-right">
                    {{ number_format($commande->montantTotal, 0, ',', ' ') }} F CFA
                </td>
            </tr>
        </tbody>
    </table>
</div>
    {{-- PAIEMENT --}}
    @if($commande->paiement)
    <div class="paiement-info">
        <div>
            <div class="mode">
                💳 Paiement :
                {{ strtoupper(str_replace('_', ' ', $commande->paiement->modePaiement)) }}
            </div>
            @if($commande->paiement->referenceTransaction)
            <div class="ref">Réf : {{ $commande->paiement->referenceTransaction }}</div>
            @endif
        </div>
        <div>
            @php
                $pLabels = [
                    'en_attente' => 'En attente',
                    'valide'     => '✓ Validé',
                    'echoue'     => '✗ Échoué',
                    'rembourse'  => '↩ Remboursé',
                ];
            @endphp
            <span class="statut statut-valide">
                {{ $pLabels[$commande->paiement->statutPaiement] ?? '' }}
            </span>
        </div>
    </div>
    @endif

    {{-- LIVRAISON --}}
    @if($commande->livraison)
    <div class="livraison">
        <h4>Informations de livraison</h4>
        <div class="livraison-grid">
            <div class="livraison-item">
                <div class="label">Mode</div>
                <div class="value">
                    {{ $commande->livraison->modeLivraison === 'domicile'
                        ? ' Livraison à domicile'
                        : 'Retrait en boutique' . ($commande->livraison->pointVente ? ' — ' . ($commande->livraison->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville') : '') }}
                </div>
            </div>
            @if($commande->livraison->adresseLivraison)
            <div class="livraison-item">
                <div class="label">Adresse</div>
                <div class="value">{{ $commande->livraison->adresseLivraison }}</div>
            </div>
            @endif
            <div class="livraison-item">
                <div class="label">Statut</div>
                <div class="value">
                    @php
                        $lLabels = [
                            'preparee'     => 'En préparation',
                            'en_livraison' => 'En livraison',
                            'livree'       => '✓ Livrée',
                            'annulee'      => 'Annulée',
                        ];
                    @endphp
                    {{ $lLabels[$commande->livraison->statutLivraison] ?? '' }}
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p class="merci">Merci pour votre confiance !</p>
        <p>ClaireAfrique — Librairie Papeterie · Dakar, Sénégal</p>
        <p class="contact">contact@claireafrique.sn · +221 77 000 00 00</p>
        <p style="margin-top: 8px; font-size: 9px; color: #aaa;">
            Document généré le {{ now()->format('d/m/Y à H:i') }} — ClaireAfrique © {{ date('Y') }}
        </p>
    </div>

</body>
</html>