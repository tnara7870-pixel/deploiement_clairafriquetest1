@extends('layouts.client')
@section('title', "Conditions Générales d'Utilisation — ClaireAfrique")

@section('content')
<div class="max-w-3xl mx-auto px-6 py-12">
    <h1 class="font-serif text-3xl text-primary-dark mb-2">Conditions Générales d'Utilisation</h1>
    <p class="text-sm text-gray-500 mb-10">Dernière mise à jour : {{ now()->locale('fr')->translatedFormat('d F Y') }}</p>

    <div class="prose prose-sm max-w-none text-gray-700 space-y-8">

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">1. Objet et champ d'application</h2>
            <p>
                Les présentes Conditions Générales d'Utilisation (« CGU ») régissent l'accès et l'utilisation
                du site ClaireAfrique, plateforme de vente en ligne de livres et de fournitures de papeterie
                exploitée par la librairie Clairafrique à Dakar, Sénégal. En créant un compte ou en passant
                commande sur le site, l'utilisateur reconnaît avoir pris connaissance des présentes CGU et
                les accepter sans réserve.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">2. Création de compte</h2>
            <p>
                La création d'un compte nécessite la fourniture d'informations exactes (nom, prénom, adresse
                email, mot de passe). L'adresse email fournie doit appartenir à l'utilisateur ; elle est
                vérifiée par l'envoi d'un code de confirmation avant l'activation complète du compte.
                L'utilisateur est responsable de la confidentialité de ses identifiants et de toute activité
                effectuée depuis son compte.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">3. Commandes, paiement et livraison</h2>
            <p>
                Les commandes peuvent être réglées en ligne (Wave, Orange Money via PayDunya) ou en espèces
                au retrait en boutique, et peuvent être retirées dans l'un des points de vente ou livrées à
                domicile. La commande est confirmée après validation du paiement ou, pour un paiement en
                espèces, après création de la commande. Les stocks affichés sont mis à jour en temps réel
                mais ne sont garantis qu'au moment de la confirmation de la commande.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">4. Annulation et remboursement</h2>
            <p>
                Une demande d'annulation peut être adressée depuis l'espace client tant que la commande n'a
                pas été livrée ou retirée. Les annulations sont examinées par un responsable et donnent lieu,
                le cas échéant, à une restitution du stock réservé et à un remboursement du montant payé.
                Pour les paiements en ligne, le remboursement est enregistré et suivi par la plateforme, mais
                exécuté par l'administrateur via le prestataire de paiement.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">5. Données personnelles et cookies</h2>
            <p>
                Le traitement des données personnelles collectées sur le site, ainsi que l'utilisation de
                cookies, sont décrits dans notre
                <a href="{{ route('legal.confidentialite') }}" class="text-primary underline">Politique de
                confidentialité</a>, conforme à la loi n°2008-12 du 25 janvier 2008 relative à la protection
                des données à caractère personnel.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">6. Responsabilité</h2>
            <p>
                ClaireAfrique s'efforce d'assurer l'exactitude des informations publiées sur le site (prix,
                disponibilité, descriptions). Des erreurs ponctuelles ne sauraient toutefois engager la
                responsabilité de la librairie au-delà du remboursement de la commande concernée.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">7. Droit applicable</h2>
            <p>
                Les présentes CGU sont soumises au droit sénégalais. Tout litige relatif à leur interprétation
                ou à leur exécution relève, à défaut de résolution amiable, des juridictions compétentes du
                Sénégal.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-primary-dark mb-2">8. Contact</h2>
            <p>
                Pour toute question relative aux présentes conditions, l'utilisateur peut contacter
                ClaireAfrique via les coordonnées indiquées sur le site.
            </p>
        </section>

    </div>
</div>
@endsection
