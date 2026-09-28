<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper-dots min-h-screen flex items-center justify-center font-sans">

<div class="w-full max-w-md">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl text-primary-dark tracking-tight">Claire<span class="text-amber-ca">Afrique</span></h1>
        <p class="text-sm text-gray-500 mt-1">Librairie · Papeterie · Dakar</p>
    </div>

    {{-- Card --}}
    <div class="bg-white border border-primary-pale rounded-lg p-8 shadow-sm">
        <h2 class="text-lg font-medium text-primary-dark mb-6">Connexion</h2>

        {{-- Message succès --}}
        @if(session('success'))
            <div class="bg-primary-pale text-primary-dark text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @php
            // Le blocage peut venir soit d'un rechargement de la page
            // (calculé dans showLogin), soit d'une tentative qui vient
            // juste d'échouer (flashé par login() via session()->with()).
            $secondesBlocage = session('secondesRestantes', $secondesRestantes ?? null);
        @endphp

        {{-- Compte bloqué : message + décompte, formulaire désactivé --}}
        @if($secondesBlocage)
            <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                Trop de tentatives. Votre compte est bloqué pour
                <span id="decompte-blocage" class="font-semibold">10:00</span>.
            </div>
        @elseif($errors->any())
            {{-- Erreurs classiques (identifiants incorrects, etc.) --}}
            <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="form-login">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">
                    Adresse email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $emailBloque ?? '') }}"
                    required
                    {{ $secondesBlocage ? 'disabled' : '' }}
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg
                           disabled:bg-gray-100 disabled:text-gray-400"
                    placeholder="vous@exemple.com"
                >
            </div>

            <div class="mb-6">
        <label class="block text-sm font-medium text-gray-600 mb-1">Mot de passe</label>
        <div class="relative">
        <input
            type="password"
            name="motDePasse"
            id="motDePasse"
            required
            {{ $secondesBlocage ? 'disabled' : '' }}
            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 pr-10 text-sm
                focus:outline-none focus:border-primary bg-primary-bg
                disabled:bg-gray-100 disabled:text-gray-400"
            placeholder="••••••••">

        <button
            type="button"
            onclick="toggleMdp()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">

            <x-heroicon-o-eye id="eye-open" class="w-5 h-5" />
            <x-heroicon-o-eye-slash id="eye-close" class="w-5 h-5 hidden" />

        </button>
    </div>
    </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary" {{ $secondesBlocage ? 'disabled' : '' }}>
                    Se souvenir de moi
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                    Mot de passe oublié ?
                </a>
            </div>

            <button type="submit" id="btn-connexion"
                {{ $secondesBlocage ? 'disabled' : '' }}
                class="w-full bg-primary hover:bg-primary-light text-white font-medium
                       py-2.5 rounded-lg text-sm transition
                       disabled:bg-gray-300 disabled:cursor-not-allowed disabled:hover:bg-gray-300">
                {{ $secondesBlocage ? 'Compte bloqué' : 'Se connecter' }}
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-primary font-medium hover:underline">
                Créer un compte
            </a>
        </p>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        © {{ date('Y') }} ClaireAfrique — Tous droits réservés
    </p>
</div>

<script>
function toggleMdp() {
    const input = document.getElementById('motDePasse');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClose = document.getElementById('eye-close');

    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClose.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClose.classList.add('hidden');
    }
}

// Décompte du blocage de compte (3 tentatives échouées → 10 minutes)
const secondesBlocage = {{ (int) ($secondesBlocage ?? 0) }};

if (secondesBlocage > 0) {
    let restant = secondesBlocage;
    const affichage = document.getElementById('decompte-blocage');

    const intervalle = setInterval(() => {
        restant--;

        if (restant <= 0) {
            clearInterval(intervalle);
            // Le blocage est terminé : on recharge la page pour
            // réactiver réellement le formulaire (le serveur revérifiera
            // l'état du blocage à chaque tentative de toute façon).
            window.location.reload();
            return;
        }

        const minutes = Math.floor(restant / 60);
        const secondes = restant % 60;
        affichage.textContent =
            String(minutes).padStart(2, '0') + ':' + String(secondes).padStart(2, '0');
    }, 1000);
}
</script>@include('shared._cookie-banner')
</body>
</html>