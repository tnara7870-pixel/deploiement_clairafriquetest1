<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper-dots min-h-screen flex items-center justify-center font-sans py-8">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl text-primary-dark tracking-tight">Claire<span class="text-amber-ca">Afrique</span></h1>
        <p class="text-sm text-gray-500 mt-1">Librairie · Papeterie · Dakar</p>
    </div>

    <div class="bg-white border border-primary-pale rounded-xl p-8 shadow-sm">
        <h2 class="text-lg font-semibold text-primary-dark mb-6">Créer un compte</h2>

        @if($errors->any())
            <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nom</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg"
                        placeholder="Diallo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg"
                        placeholder="Mamadou">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Adresse email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="vous@exemple.com">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">
                    Téléphone 
                </label>
                <input type="text" name="telephone" value="{{ old('telephone') }}" required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="+221 77 000 00 00">
            </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Mot de passe
                    </label>

                    <div class="relative">
                        <input
                            type="password"
                            name="motDePasse"
                            id="mdp1"
                            required
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 pr-10 text-sm
                                focus:outline-none focus:border-primary bg-primary-bg"
                            placeholder="8 caractères minimum">

                        <button
                            type="button"
                            onclick="toggleMdp('mdp1','eye-open1','eye-close1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">

                            <x-heroicon-o-eye id="eye-open1" class="w-5 h-5" />
                            <x-heroicon-o-eye-slash id="eye-close1" class="w-5 h-5 hidden" />
                        </button>
                    </div>
                </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-600 mb-1">
                    Confirmer le mot de passe
                </label>

                <div class="relative">
                    <input
                        type="password"
                        name="motDePasse_confirmation"
                        id="mdp2"
                        required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 pr-10 text-sm
                            focus:outline-none focus:border-primary bg-primary-bg"
                        placeholder="••••••••">

                    <button
                        type="button"
                        onclick="toggleMdp('mdp2','eye-open2','eye-close2')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">

                        <x-heroicon-o-eye id="eye-open2" class="w-5 h-5" />
                        <x-heroicon-o-eye-slash id="eye-close2" class="w-5 h-5 hidden" />
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-start gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="conditionsAcceptees" value="1" required
                        {{ old('conditionsAcceptees') ? 'checked' : '' }}
                        class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary">
                    <span>
                        J'accepte les
                        <a href="{{ route('legal.cgu') }}" target="_blank" class="text-primary underline">conditions d'utilisation</a>
                        et la
                        <a href="{{ route('legal.confidentialite') }}" target="_blank" class="text-primary underline">politique de confidentialité</a>
                        relatives au traitement de mes données personnelles, conformément à la loi n°2008-12.
                    </span>
                </label>
            </div>

            <button type="submit"
                class="w-full bg-primary hover:bg-primary-light text-white font-medium
                       py-2.5 rounded-lg text-sm transition">
                Créer mon compte
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Déjà un compte ?
            <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">
                Se connecter
            </a>
        </p>
    </div>
</div>

<script>
function toggleMdp(inputId, eyeOpenId, eyeCloseId) {
    const input = document.getElementById(inputId);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClose = document.getElementById(eyeCloseId);

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
</script>
@include('shared._cookie-banner')

</body>
</html>