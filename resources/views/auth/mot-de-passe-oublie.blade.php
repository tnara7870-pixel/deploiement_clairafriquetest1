<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper-dots min-h-screen flex items-center justify-center font-sans">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl text-primary-dark tracking-tight">Claire<span class="text-amber-ca">Afrique</span></h1>
        <p class="text-sm text-gray-500 mt-1">Librairie · Papeterie · Dakar</p>
    </div>

    <div class="bg-white border border-primary-pale rounded-xl p-8 shadow-sm">
        <h2 class="text-lg font-semibold text-primary-dark mb-2">Mot de passe oublié</h2>
        <p class="text-sm text-gray-500 mb-6">
            Indiquez votre adresse email : si un compte y est associé, vous recevrez un lien pour réinitialiser votre mot de passe.
        </p>

        @if(session('success'))
            <div class="bg-primary-pale text-primary-dark text-sm px-4 py-3 rounded-lg mb-4" role="status">
                {{ session('success') }}
            </div>
        @elseif($errors->any())
            <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Adresse email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="vous@exemple.com">
            </div>

            <button type="submit"
                class="w-full bg-primary text-white font-medium py-2.5 rounded-lg text-sm hover:bg-primary-dark transition-colors">
                Envoyer le lien de réinitialisation
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Retour à la connexion</a>
        </p>
    </div>
</div>@include('shared._cookie-banner')
</body>
</html>
