@extends('layouts.client')
@section('title', 'Mon compte')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-8">
    <h1 class="text-lg font-semibold text-primary-dark mb-6">Mon compte</h1>

    <div class="bg-white border border-primary-pale rounded-xl p-6">
        <form method="POST" action="{{ route('client.compte.update') }}">
            @csrf @method('PATCH')

            @if($errors->any())
                <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nom </label>
                    <input type="text" name="nom" value="{{ old('nom', $user->nom) }}"disabled
                        class="w-full border border-gray-100 rounded-lg px-3 py-2 text-sm
                           bg-gray-50 text-gray-400 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Prénom </label>
                    <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" disabled
                       class="w-full border border-gray-100 rounded-lg px-3 py-2 text-sm
                           bg-gray-50 text-gray-400 cursor-not-allowed">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full border border-gray-100 rounded-lg px-3 py-2 text-sm
                           bg-gray-50 text-gray-400 cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">
                    L'adresse email ne peut pas être modifiée.
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Téléphone</label>
                <input type="text" name="telephone"
                    value="{{ old('telephone', $user->telephone) }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="+221 77 000 00 00">
            </div>

            <div class="border-t border-primary-pale pt-4 mt-4 mb-4">
                <p class="text-xs font-semibold text-gray-500 mb-3">
                    Changer le mot de passe (laisser vide pour ne pas modifier)
                </p>
                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Mot de passe actuel</label>
                    <input type="password" name="motDePasseActuel"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg"
                        placeholder="Requis uniquement pour changer le mot de passe">
                    @error('motDePasseActuel')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nouveau mot de passe</label>
                        <input type="password" name="motDePasse"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:border-primary bg-primary-bg"
                            placeholder="8 caractères minimum">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Confirmer</label>
                        <input type="password" name="motDePasse_confirmation"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:border-primary bg-primary-bg"
                            placeholder="••••••••">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-primary text-white px-6 py-2 rounded-lg text-sm
                           hover:bg-primary-light font-medium">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection