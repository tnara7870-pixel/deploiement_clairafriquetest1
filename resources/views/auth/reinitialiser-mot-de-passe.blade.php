<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper-dots min-h-screen flex items-center justify-center font-sans">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl text-primary-dark tracking-tight">Claire<span class="text-amber-ca">Afrique</span></h1>
        <p class="text-sm text-gray-500 mt-1">Librairie · Papeterie · Dakar</p>
    </div>

    <div class="bg-white border border-primary-pale rounded-xl p-8 shadow-sm">
        <h2 class="text-lg font-semibold text-primary-dark mb-6">Nouveau mot de passe</h2>

        @if($errors->any())
            <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Adresse email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="vous@exemple.com">
            </div>

            <div class="mb-4">
                <label for="motDePasse" class="block text-sm font-medium text-gray-600 mb-1">Nouveau mot de passe</label>
                <input
                    id="motDePasse"
                    type="password"
                    name="motDePasse"
                    required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="8 caractères minimum">
            </div>

            <div class="mb-2">
                <label for="motDePasse_confirmation" class="block text-sm font-medium text-gray-600 mb-1">Confirmer le mot de passe</label>
                <input
                    id="motDePasse_confirmation"
                    type="password"
                    name="motDePasse_confirmation"
                    required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="••••••••">
            </div>
            <p class="text-xs text-gray-400 mb-6">
                Au moins 8 caractères, avec une majuscule, une minuscule, un chiffre et un caractère spécial.
            </p>

            <button type="submit"
                class="w-full bg-primary text-white font-medium py-2.5 rounded-lg text-sm hover:bg-primary-dark transition-colors">
                Réinitialiser le mot de passe
            </button>
        </form>
    </div>
</div>@include('shared._cookie-banner')
</body>
</html>
