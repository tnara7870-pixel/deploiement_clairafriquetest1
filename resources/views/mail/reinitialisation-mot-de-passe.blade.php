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
                            <h1 style="font-size:18px; margin:0 0 12px;">Bonjour {{ $user->prenom }},</h1>
                            <p style="font-size:14px; line-height:1.6; color:#3A4A40; margin:0 0 20px;">
                                Vous avez demandé la réinitialisation du mot de passe associé à votre compte
                                ClaireAfrique (<strong>{{ $user->email }}</strong>). Cliquez sur le bouton
                                ci-dessous pour choisir un nouveau mot de passe.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}"
                                           style="display:inline-block; background:#1A4731; color:#ffffff; text-decoration:none;
                                                  padding:12px 28px; border-radius:8px; font-size:13px; font-weight:bold;">
                                            Réinitialiser mon mot de passe
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; color:#5A6B63; margin:0 0 12px;">
                                Ce lien est valable <strong>60&nbsp;minutes</strong>. Si vous n'êtes pas à l'origine
                                de cette demande, vous pouvez ignorer cet email : votre mot de passe restera
                                inchangé.
                            </p>

                            <p style="font-size:11px; color:#8A968E; margin:0; word-break:break-all;">
                                Le bouton ne fonctionne pas ? Copiez ce lien dans votre navigateur&nbsp;:<br>
                                {{ $url }}
                            </p>
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
