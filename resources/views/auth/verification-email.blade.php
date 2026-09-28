<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de l'email — ClaireAfrique</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper-dots min-h-screen flex items-center justify-center font-sans py-8">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="font-serif text-3xl text-primary-dark tracking-tight">Claire<span class="text-amber-ca">Afrique</span></h1>
        <p class="text-sm text-gray-500 mt-1">Librairie · Papeterie · Dakar</p>
    </div>

    <div class="bg-white border border-primary-pale rounded-xl p-8 shadow-sm">
        <h2 class="text-lg font-semibold text-primary-dark mb-2">Vérifiez votre adresse email</h2>
        <p class="text-sm text-gray-500 mb-6">
            Un code à 6 chiffres a été envoyé à
            <strong>{{ auth()->user()->email }}</strong>. Saisissez-le ci-dessous pour activer votre compte.
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

        <form method="POST" action="{{ route('verification.verifier') }}">
            @csrf
            <div class="mb-6">
                <label for="code" class="block text-sm font-medium text-gray-600 mb-1">Code de vérification</label>
                <input
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    required
                    autofocus
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-center text-lg tracking-[0.5em]
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="000000">
            </div>

            <button type="submit"
                class="w-full bg-primary text-white font-medium py-2.5 rounded-lg text-sm hover:bg-primary-dark transition-colors">
                Vérifier mon compte
            </button>
        </form>

        <form method="POST" action="{{ route('verification.renvoyer') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full text-center text-sm text-primary font-medium hover:underline">
                Je n'ai pas reçu de code — renvoyer
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="hover:underline">Se déconnecter</a>
        </p>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
    </div>
</div>@include('shared._cookie-banner')
</body>
</html>
