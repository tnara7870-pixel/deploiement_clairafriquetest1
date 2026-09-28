@extends('layouts.client')
@section('title', 'Récapitulatif de commande')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@section('content')
@include('client._etapes-commande', ['etape' => 2])
<div class="max-w-6xl mx-auto px-6 py-8">
    {{-- En-tête avec étape --}}
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-primary-pale">
        <div>
            <h1 class="text-2xl font-bold text-primary-dark">
                Récapitulatif 
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Vérifiez vos articles et choisissez votre mode de règlement.
            </p>
        </div>
        <a href="{{ route('client.panier') }}"
            class="text-xs font-medium text-primary hover:text-primary-dark hover:underline flex items-center gap-1 transition-colors">
            <x-heroicon-o-arrow-left class="w-3.5 h-3.5" /> Modifier mon panier
        </a>
    </div>

    <form method="POST" action="{{ route('client.commande.preparer') }}" id="form-recap">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- COLONNE GAUCHE (7/12) : Contenu de la commande & Synthèse --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- Carte Articles --}}
                <div class="bg-white border border-primary-pale rounded-2xl p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                        <h2 class="text-base font-bold text-primary-dark flex items-center gap-2">
                            <x-heroicon-o-cube class="w-5 h-5" /> Articles commandés
                        </h2>
                        <span class="text-xs font-semibold text-primary-dark bg-primary-pale/40 px-3 py-1 rounded-full">
                            {{ $panier->lignePaniers->count() }} article(s)
                        </span>
                    </div>

                    {{-- Liste des articles --}}
                    <div class="divide-y divide-gray-100 max-h-[380px] overflow-y-auto pr-2">
                        @foreach($panier->lignePaniers as $ligne)
                        <div class="py-3.5 flex justify-between items-center gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 bg-primary-bg rounded-xl flex items-center justify-center shrink-0 border border-primary-pale/50">
                                    <x-heroicon-o-book-open class="w-5 h-5 text-primary" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-800 truncate">
                                        {{ $ligne->article->designation }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ number_format($ligne->article->prix, 0, ',', ' ') }} F CFA × {{ $ligne->quantite }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm font-bold text-primary-dark shrink-0">
                                {{ number_format($ligne->sousTotal(), 0, ',', ' ') }} F CFA
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Synthèse Financière --}}
                    <div class="mt-6 bg-primary-bg/50 rounded-xl p-4 space-y-2.5 border border-primary-pale">
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Sous-total articles</span>
                            <span class="font-medium">{{ number_format($total, 0, ',', ' ') }} F CFA</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Frais de livraison</span>
                            <span id="frais-livraison" class="font-medium text-emerald-600">Offert</span>
                        </div>
                        <div class="flex justify-between text-base font-bold border-t border-primary-pale/80 pt-3 mt-2">
                            <span class="text-gray-900">Total à régler</span>
                            <span id="total-final" class="text-primary-dark text-lg font-black">
                                {{ number_format($total, 0, ',', ' ') }} F CFA
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Note de réassurance sous le récapitulatif --}}
                <div class="flex items-center gap-3 p-4 bg-emerald-50/60 border border-emerald-100 rounded-xl text-xs text-emerald-800">
                    <x-heroicon-o-shield-check class="w-5 h-5 flex-shrink-0" />
                    <div>
                        <span class="font-semibold">Achetez en toute sérénité :</span> Vos informations de paiement et vos commandes sont gérées de manière totalement sécurisée par la librairie Clairafrique.
                    </div>
                </div>

            </div>

            {{-- COLONNE DROITE (5/12) : Actions (Livraison + Paiement + Confirmation) --}}
            <div class="lg:col-span-5 space-y-6">
                
                {{-- Étape 1 : Mode de Livraison --}}
                <div class="bg-white border border-primary-pale rounded-2xl p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-primary-dark mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-primary text-white text-xs flex items-center justify-center font-bold">1</span>
                        Mode de livraison
                    </h2>
                    <div class="grid grid-cols-1 gap-3">
                        <label class="flex items-center gap-3 border border-gray-200 rounded-xl p-3.5 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-pale/30 shadow-xs">
                            <input type="radio" name="modeLivraison" value="boutique" class="text-primary focus:ring-primary" checked onchange="toggleAdresse(this.value)">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 flex items-center gap-1.5"><x-heroicon-o-building-storefront class="w-4 h-4" /> Retrait en boutique</div>
                                <div class="text-xs text-gray-400">Gratuit · Disponible en librairie</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 border border-gray-200 rounded-xl p-3.5 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-pale/30 shadow-xs">
                            <input type="radio" name="modeLivraison" value="domicile" class="text-primary focus:ring-primary" onchange="toggleAdresse(this.value)">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 flex items-center gap-1.5"><x-heroicon-o-home class="w-4 h-4" /> Livraison à domicile</div>
                                <div class="text-xs text-gray-400">Dakar et proche banlieue</div>
                            </div>
                        </label>
                    </div>

                    {{-- Champ Point de vente (retrait en boutique) --}}
                    <div id="champ-boutique" class="mt-4 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Point de vente *
                        </label>
                        <select id="input-point-vente" name="pointVente" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary bg-primary-bg/30">
                            <option value="ucad" {{ old('pointVente') == 'ucad' ? 'selected' : '' }}>UCAD</option>
                            <option value="centre_ville" {{ old('pointVente') == 'centre_ville' ? 'selected' : '' }}>Centre-ville</option>
                        </select>
                    </div>

                    {{-- Champ Adresse --}}
                    <div id="champ-adresse" class="hidden mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-gray-700">
                                Adresse exacte de livraison *
                            </label>
                            <button type="button" id="btn-localiser"
                                onclick="localiserAdresse()"
                                class="text-xs text-primary font-medium hover:underline flex items-center gap-1">
                                <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                                Utiliser ma position
                            </button>
                        </div>
                        <textarea id="input-adresse" name="adresse" rows="3"
                            placeholder="Quartier, rue, numéro de maison, point de repère…"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary bg-primary-bg/30">{{ old('adresse') }}</textarea>

                        {{-- Message d'état de la géolocalisation --}}
                        <p id="statut-localisation" class="text-xs text-gray-400 mt-1.5 hidden"></p>

                        {{-- Carte interactive : n'apparaît qu'après localisation réussie.
                             Le pin est déplaçable pour corriger le GPS si besoin — dans
                             beaucoup de quartiers l'adressage informel rend un point GPS
                             seul imprécis, d'où le champ texte ci-dessus qui reste
                             obligatoire et fait foi pour le livreur. --}}
                        <div id="carte-adresse-wrap" class="hidden mt-3">
                            <div id="carte-adresse" class="w-full h-52 rounded-xl border border-gray-200 z-0"></div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Vous pouvez déplacer le repère si la position n'est pas exacte.
                            </p>
                        </div>

                        <input type="hidden" id="input-latitude" name="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" id="input-longitude" name="longitude" value="{{ old('longitude') }}">
                    </div>
                </div>

                {{-- Étape 2 : Moyen de paiement & Validation --}}
                <div class="bg-white border border-primary-pale rounded-2xl p-6 shadow-sm" id="section-paiement">
                    <h2 class="text-sm font-bold text-primary-dark mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-primary text-white text-xs flex items-center justify-center font-bold">2</span>
                        Moyen de paiement
                    </h2>

                    <div id="options-paiement-enligne" class="flex flex-col gap-3 mb-6">
                        {{-- Wave --}}
                        <label class="flex items-center gap-3 border border-gray-200 rounded-xl p-3.5 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-pale/30 shadow-xs">
                            <input type="radio" name="modePaiement" value="wave" class="text-primary focus:ring-primary" checked>
                            <div class="flex-1 flex justify-between items-center">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">Wave</div>
                                    <div class="text-xs text-gray-400">Paiement mobile instantané</div>
                                </div>
                                <span class="text-xs font-bold bg-sky-100 text-sky-600 px-2.5 py-1 rounded-md">
                                    🌊 Wave
                                </span>
                            </div>
                        </label>

                        {{-- Orange Money --}}
                        <label class="flex items-center gap-3 border border-gray-200 rounded-xl p-3.5 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-pale/30 shadow-xs">
                            <input type="radio" name="modePaiement" value="orange_money" class="text-primary focus:ring-primary">
                            <div class="flex-1 flex justify-between items-center">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">Orange Money</div>
                                    <div class="text-xs text-gray-400">Code/Paiement mobile</div>
                                </div>
                                <span class="text-xs font-bold bg-orange-100 text-orange-600 px-2 py-1 rounded-md">
                                    🟠 OM
                                </span>
                            </div>
                        </label>

                        {{-- Espèces (Retrait Boutique uniquement) --}}
                        <label id="option-especes" class="flex items-center gap-3 border border-gray-200 rounded-xl p-3.5 cursor-pointer hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-pale/30 shadow-xs">
                            <input type="radio" name="modePaiement" value="especes" class="text-primary focus:ring-primary">
                            <div class="flex-1 flex justify-between items-center">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">Espèces en boutique</div>
                                    <div class="text-xs text-gray-400">Règlement lors du retrait</div>
                                </div>
                                <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md">
                                    <x-heroicon-o-banknotes class="w-3.5 h-3.5 inline-block flex-shrink-0 align-[-2px]" /> Cash
                                </span>
                            </div>
                        </label>
                    </div>

                    {{-- Bouton CTA dynamique avec montant --}}
                    <button type="submit" id="btn-submit-commande"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-all duration-200 text-center shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <span class="flex items-center gap-1.5"><x-heroicon-o-lock-closed class="w-4 h-4" /> Confirmer et payer</span>
                        <span id="btn-total-text" class="bg-black/20 px-2.5 py-0.5 rounded-lg text-xs font-medium">
                            {{ number_format($total, 0, ',', ' ') }} F
                        </span>
                    </button>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
const sousTotal = {{ $total }};
const frais = {{ config('claireafrique.frais_livraison_domicile', 2000) }};

function toggleAdresse(val) {
    const champ = document.getElementById('champ-adresse');
    const inputAdresse = document.getElementById('input-adresse');
    const champBoutique = document.getElementById('champ-boutique');
    const inputPointVente = document.getElementById('input-point-vente');
    const optionEspeces = document.getElementById('option-especes');
    const fraisEl = document.getElementById('frais-livraison');
    const totalEl = document.getElementById('total-final');
    const btnTotalText = document.getElementById('btn-total-text');

    if (val === 'domicile') {
        champ.classList.remove('hidden');
        inputAdresse.setAttribute('required', 'required');

        champBoutique.classList.add('hidden');
        inputPointVente.removeAttribute('required');

        optionEspeces.classList.add('hidden');
        const especesRadio = document.querySelector('input[value="especes"]');
        if (especesRadio && especesRadio.checked) {
            document.querySelector('input[value="wave"]').checked = true;
        }

        const totalCalcule = sousTotal + frais;
        const totalFormate = new Intl.NumberFormat('fr-FR').format(totalCalcule);

        fraisEl.textContent = new Intl.NumberFormat('fr-FR').format(frais) + ' F CFA';
        fraisEl.classList.remove('text-emerald-600');
        fraisEl.classList.add('text-gray-700');
        
        totalEl.textContent = totalFormate + ' F CFA';
        btnTotalText.textContent = totalFormate + ' F';
    } else {
        champ.classList.add('hidden');
        inputAdresse.removeAttribute('required');

        champBoutique.classList.remove('hidden');
        inputPointVente.setAttribute('required', 'required');

        optionEspeces.classList.remove('hidden');

        const totalFormate = new Intl.NumberFormat('fr-FR').format(sousTotal);

        fraisEl.textContent = 'Offert';
        fraisEl.classList.add('text-emerald-600');
        fraisEl.classList.remove('text-gray-700');

        totalEl.textContent = totalFormate + ' F CFA';
        btnTotalText.textContent = totalFormate + ' F';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modeSelectionne = document.querySelector('input[name="modeLivraison"]:checked');
    if (modeSelectionne) {
        toggleAdresse(modeSelectionne.value);
    }
});
</script>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
let carteAdresse = null;
let repereAdresse = null;

function localiserAdresse() {
    const statut = document.getElementById('statut-localisation');
    const btn = document.getElementById('btn-localiser');

    if (!navigator.geolocation) {
        statut.textContent = "La géolocalisation n'est pas disponible sur ce navigateur. Renseignez l'adresse manuellement.";
        statut.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    statut.textContent = 'Localisation en cours…';
    statut.classList.remove('hidden', 'text-red-500');
    statut.classList.add('text-gray-400');

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            afficherCarte(lat, lng);
            renseignerCoordonnees(lat, lng);
            rechercherAdresseApproximative(lat, lng);
            btn.disabled = false;
        },
        function(erreur) {
            btn.disabled = false;
            statut.classList.remove('text-gray-400');
            statut.classList.add('text-red-500');
            // Message adapté à chaque cas plutôt qu'une erreur générique —
            // le refus de permission est le cas le plus fréquent et le
            // client doit comprendre qu'il peut simplement continuer à
            // écrire l'adresse à la main, ce n'est pas bloquant.
            if (erreur.code === erreur.PERMISSION_DENIED) {
                statut.textContent = "Position refusée. Vous pouvez toujours écrire l'adresse manuellement ci-dessus.";
            } else {
                statut.textContent = "Impossible de vous localiser pour le moment. Écrivez l'adresse manuellement.";
            }
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

function afficherCarte(lat, lng) {
    document.getElementById('carte-adresse-wrap').classList.remove('hidden');

    if (!carteAdresse) {
        carteAdresse = L.map('carte-adresse').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19,
        }).addTo(carteAdresse);

        repereAdresse = L.marker([lat, lng], { draggable: true }).addTo(carteAdresse);

        // Le repère est déplaçable : si le GPS n'est pas précis (fréquent
        // en zone mal cartographiée), le client ajuste lui-même plutôt que
        // de subir une position figée et fausse.
        repereAdresse.on('dragend', function() {
            const pos = repereAdresse.getLatLng();
            renseignerCoordonnees(pos.lat, pos.lng);
            rechercherAdresseApproximative(pos.lat, pos.lng);
        });
    } else {
        carteAdresse.setView([lat, lng], 16);
        repereAdresse.setLatLng([lat, lng]);
    }

    // Leaflet a besoin d'un recalcul de taille quand son conteneur passe
    // de hidden à visible, sinon la carte s'affiche mal tant qu'on ne
    // redimensionne pas la fenêtre.
    setTimeout(() => carteAdresse.invalidateSize(), 100);
}

function renseignerCoordonnees(lat, lng) {
    document.getElementById('input-latitude').value = lat.toFixed(7);
    document.getElementById('input-longitude').value = lng.toFixed(7);
}

function rechercherAdresseApproximative(lat, lng) {
    const statut = document.getElementById('statut-localisation');
    const champAdresse = document.getElementById('input-adresse');

    // Géocodage inverse via Nominatim (OpenStreetMap, gratuit, sans clé
    // API). Le résultat ne fait que PRÉ-REMPLIR le champ texte — le
    // client garde la main pour corriger/compléter, l'adressage à Dakar
    // étant souvent informel (pas de nom de rue officiel dans beaucoup
    // de quartiers).
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
        headers: { 'Accept-Language': 'fr' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.display_name && !champAdresse.value.trim()) {
                champAdresse.value = data.display_name;
            }
            statut.classList.remove('text-red-500');
            statut.classList.add('text-primary');
            statut.textContent = 'Position trouvée — vérifiez et complétez l\'adresse si besoin.';
        })
        .catch(() => {
            // Le géocodage inverse est un confort, pas une nécessité : les
            // coordonnées GPS sont déjà enregistrées même si cet appel
            // échoue (réseau lent, service indisponible).
            statut.classList.remove('text-red-500');
            statut.classList.add('text-primary');
            statut.textContent = 'Position enregistrée. Complétez l\'adresse si besoin.';
        });
}
</script>
@endpush
@endsection