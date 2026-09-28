{{-- Bandeau de consentement aux cookies, conforme à la loi n°2008-12 du
     25 janvier 2008 sur la protection des données à caractère personnel :
     affiché à toute personne n'ayant pas encore fait son choix, avant
     tout dépôt de cookie non strictement nécessaire. Le choix est mémorisé
     dans un cookie propre (strictement nécessaire, donc déposé sans
     consentement préalable) pour ne pas réafficher le bandeau à chaque page. --}}
<div id="bandeau-cookies"
     class="hidden fixed bottom-0 left-0 right-0 z-[100] bg-primary-dark text-white
            px-6 py-5 shadow-[0_-4px_16px_rgba(0,0,0,0.15)]"
     role="dialog" aria-live="polite" aria-label="Consentement aux cookies">
    <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center gap-4">
        <p class="text-xs md:text-sm text-primary-pale flex-1">
            Nous utilisons des cookies strictement nécessaires au fonctionnement du site (connexion, panier,
            sécurité), et pourrons utiliser à l'avenir des cookies de mesure d'audience soumis à votre accord.
            En savoir plus dans notre
            <a href="{{ route('legal.confidentialite') }}#cookies" class="underline hover:text-white">politique
                de confidentialité</a>.
        </p>
        <div class="flex gap-3 flex-shrink-0">
            <button type="button" onclick="claireafriqueCookies.refuser()"
                class="text-xs md:text-sm border border-white/30 text-white px-4 py-2 rounded-lg
                       hover:bg-white/10 transition-colors">
                Refuser les cookies non essentiels
            </button>
            <button type="button" onclick="claireafriqueCookies.accepter()"
                class="text-xs md:text-sm bg-amber-ca text-white px-4 py-2 rounded-lg font-medium
                       hover:opacity-90 transition-opacity">
                Tout accepter
            </button>
        </div>
    </div>
</div>

<script>
// ── Consentement aux cookies ────────────────────────────────────────
// Cookie de préférence lui-même strictement nécessaire (il sert
// uniquement à ne pas réafficher le bandeau) : il est déposé sans
// attendre de consentement, ce qui est conforme à la loi n°2008-12.
window.claireafriqueCookies = {
    NOM_COOKIE: 'ca_consentement_cookies',
    DUREE_JOURS: 365,

    lire() {
        const match = document.cookie.match(new RegExp('(?:^|; )' + this.NOM_COOKIE + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    },

    ecrire(valeur) {
        const date = new Date();
        date.setTime(date.getTime() + this.DUREE_JOURS * 24 * 60 * 60 * 1000);
        document.cookie = `${this.NOM_COOKIE}=${encodeURIComponent(valeur)}; expires=${date.toUTCString()}; path=/; SameSite=Lax`;
    },

    accepter() {
        this.ecrire('accepte');
        document.getElementById('bandeau-cookies')?.classList.add('hidden');
        // Point d'extension : c'est ici qu'un futur script de mesure
        // d'audience (Google Analytics, Matomo, etc.) devrait être chargé
        // dynamiquement, uniquement après acceptation explicite.
    },

    refuser() {
        this.ecrire('refuse');
        document.getElementById('bandeau-cookies')?.classList.add('hidden');
    },
};

document.addEventListener('DOMContentLoaded', function () {
    if (!window.claireafriqueCookies.lire()) {
        document.getElementById('bandeau-cookies')?.classList.remove('hidden');
    }
});
</script>