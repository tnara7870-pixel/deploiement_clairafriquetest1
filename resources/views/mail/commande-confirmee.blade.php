<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0; background:#F7FAF8; font-family: Arial, sans-serif; color:#1C2B22;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7FAF8; padding:24px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#1A4731; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">ClaireAfrique</span>
                            <div style="color:#A8D5BA; font-size:11px; margin-top:2px;">Librairie &amp; Papeterie — Dakar</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="font-size:18px; margin:0 0 12px;">Merci pour votre commande, {{ $commande->utilisateur->prenom }} !</h1>
                            <p style="font-size:14px; line-height:1.6; color:#3A4A40; margin:0 0 20px;">
                                Votre commande <strong>{{ $commande->numeroCommande }}</strong> a bien été enregistrée
                                le {{ $commande->dateCommande?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i') }}.
                                Vous trouverez votre facture en pièce jointe de cet email.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7FAF8; border-radius:8px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px; font-size:13px;">
                                        <div style="color:#5A6B63; text-transform:uppercase; font-size:10px; letter-spacing:0.05em; margin-bottom:6px;">Récapitulatif</div>
                                        @foreach($commande->ligneCommandes as $ligne)
                                        <div style="display:flex; justify-content:space-between; padding:3px 0;">
                                            {{ $ligne->quantite }} × {{ $ligne->article->designation ?? 'Article' }}
                                            — {{ number_format($ligne->quantite * $ligne->prixUnitaire, 0, ',', ' ') }} F CFA
                                        </div>
                                        @endforeach
                                        <div style="border-top:1px solid #D6EDDF; margin-top:8px; padding-top:8px; font-weight:bold; color:#1A4731;">
                                            Total : {{ number_format($commande->montantTotal, 0, ',', ' ') }} F CFA
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            @if($commande->livraison)
                            <p style="font-size:13px; color:#3A4A40; margin:0 0 20px;">
                                @if($commande->livraison->modeLivraison === 'domicile')
                                    Livraison à domicile
                                @else
                                    Retrait en boutique{{ $commande->livraison->pointVente ? ' — ' . ($commande->livraison->pointVente === 'ucad' ? 'UCAD' : 'Centre-ville') : '' }}
                                @endif
                            </p>
                            @endif

                            <a href="{{ route('client.commande.detail', $commande->idCommande) }}"
                               style="display:inline-block; background:#1A4731; color:#ffffff; text-decoration:none;
                                      padding:12px 24px; border-radius:8px; font-size:13px; font-weight:bold;">
                                Suivre ma commande
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; background:#F7FAF8; font-size:11px; color:#8A968E;">
                            ClaireAfrique — Dakar, Sénégal. Cet email a été envoyé automatiquement, merci de ne pas y répondre.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
