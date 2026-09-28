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
                                Merci de vous être inscrit(e) sur ClaireAfrique. Pour confirmer que cette adresse
                                email vous appartient bien et activer votre compte, saisissez le code suivant
                                sur la page de vérification&nbsp;:
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7FAF8; border-radius:8px; margin-bottom:20px;">
                                <tr>
                                    <td align="center" style="padding:20px;">
                                        <span style="display:inline-block; font-size:30px; font-weight:bold; letter-spacing:0.35em; color:#1A4731;">
                                            {{ $code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:13px; color:#5A6B63; margin:0 0 20px;">
                                Ce code est valable <strong>15&nbsp;minutes</strong>. Si vous n'êtes pas à l'origine
                                de cette inscription, vous pouvez ignorer cet email sans rien faire de plus.
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
