@extends('layouts.client')
@section('title', 'Politique de confidentialité — ClaireAfrique')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-12">
    <h1 class="font-serif text-3xl text-primary-dark mb-2">Politique de confidentialité</h1>
    <p class="text-sm text-gray-500 mb-10">Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('d F Y') }}</p>

    <div class="prose prose-sm max-w-none text-gray-700 space-y-8">

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">1. Responsable de traitement</h2>
            <p>
                Le responsable du traitement des données personnelles collectées sur le site ClaireAfrique est
                la librairie Clairafrique, établie à Dakar, Sénégal. La présente politique est établie
                conformément à la loi n°2008-12 du 25 janvier 2008 relative à la protection des données à
                caractère personnel, sous le contrôle de la Commission de Protection des Données Personnelles
                (CDP) du Sénégal.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">2. Données collectées</h2>
            <p>Sont collectées, selon l'usage du site :</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Données d'identification : nom, prénom, adresse email, numéro de téléphone ;</li>
                <li>Données de compte : mot de passe (stocké sous forme chiffrée), historique de connexion ;</li>
                <li>Données de commande : articles commandés, adresse de livraison, point de vente choisi,
                    statut de paiement ;</li>
                <li>Données techniques : adresse IP, journal des tentatives de connexion, cookies (voir
                    section 6).</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">3. Finalités du traitement</h2>
            <p>Ces données sont utilisées pour :</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>La création et la gestion du compte utilisateur, y compris la vérification de l'adresse
                    email par code de confirmation ;</li>
                <li>Le traitement des commandes, des paiements et des livraisons ;</li>
                <li>La sécurisation du compte (limitation des tentatives de connexion, réinitialisation du
                    mot de passe) ;</li>
                <li>L'envoi de communications relatives aux commandes (confirmation, statut, facture) ;</li>
                <li>Le respect des obligations légales et comptables applicables à l'activité commerciale.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">4. Durée de conservation</h2>
            <p>
                Les données de compte sont conservées pendant toute la durée de la relation commerciale, puis
                archivées ou supprimées conformément aux délais de prescription légale applicables (notamment
                en matière commerciale et fiscale). Les données de connexion (tentatives échouées) sont
                conservées le temps nécessaire à la sécurité du compte.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">5. Droits de la personne concernée</h2>
            <p>
                Conformément à la loi n°2008-12, toute personne dispose d'un droit d'accès, de rectification,
                d'opposition et de suppression de ses données personnelles, ainsi que du droit de retirer son
                consentement à tout moment. Ces droits peuvent être exercés en écrivant à ClaireAfrique via
                les coordonnées de contact indiquées sur le site, ou depuis la rubrique « Mon compte ». En cas
                de désaccord persistant, une réclamation peut être adressée à la Commission de Protection des
                Données Personnelles (CDP) du Sénégal.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">6. Cookies</h2>
            <p>
                Le site utilise des cookies, c'est-à-dire de petits fichiers déposés sur le terminal de
                l'utilisateur lors de sa navigation.
            </p>
            <p class="font-medium text-primary-dark mt-3">Cookies strictement nécessaires</p>
            <p>
                Ces cookies (session de connexion, jeton de sécurité CSRF, mémorisation du choix relatif aux
                cookies) sont indispensables au fonctionnement du site et ne peuvent pas être désactivés ; ils
                ne nécessitent pas de consentement préalable.
            </p>
            <p class="font-medium text-primary-dark mt-3">Cookies de mesure d'audience et fonctionnels</p>
            <p>
                D'autres cookies, non strictement nécessaires (mesure d'audience, préférences d'affichage),
                peuvent être utilisés à l'avenir pour améliorer le site. Leur dépôt est soumis au consentement
                préalable de l'utilisateur, recueilli via le bandeau affiché lors de la première visite.
                L'utilisateur peut à tout moment modifier son choix en effaçant les cookies de son navigateur,
                ce qui réaffichera le bandeau de consentement.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">7. Sécurité</h2>
            <p>
                ClaireAfrique met en œuvre des mesures techniques et organisationnelles pour protéger les
                données personnelles (mots de passe chiffrés, connexion HTTPS, contrôle d'accès par rôle,
                validation côté serveur des paiements en ligne).
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">8. Contact</h2>
            <p>
                Pour toute question relative au traitement de vos données personnelles, vous pouvez contacter
                ClaireAfrique via les coordonnées indiquées sur le site.
            </p>
        </section>

    </div>
</div>
@endsection
