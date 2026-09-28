@php
    // Déduit "admin" / "stock" / "commande" du nom de la route courante
    // (ex. "admin.compte") pour cibler la bonne route de mise à jour.
    $espace = explode('.', request()->route()->getName())[0];
@endphp

<div class="max-w-lg">
    <h1 class="text-lg font-semibold text-primary-dark mb-1">Mon compte</h1>
    <p class="text-xs text-gray-400 mb-6">
        Connecté en tant que {{ $user->prenom }} {{ $user->nom }} ({{ $user->email }})
    </p>

    <div class="bg-white border border-primary-pale rounded-xl p-6">
        @if(Auth::user()->hasRole('administrateur'))
            <form method="POST" action="{{ route($espace . '.compte.update') }}">
                @csrf @method('PATCH')

                @if($errors->any())
                    <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif

                <p class="text-xs font-semibold text-gray-500 mb-3">
                    Changer le mot de passe
                </p>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Mot de passe actuel *</label>
                    <input type="password" name="motDePasseActuel" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                    @error('motDePasseActuel')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nouveau mot de passe *</label>
                        <input type="password" name="motDePasse" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:border-primary bg-primary-bg"
                            placeholder="8 caractères minimum">
                        @error('motDePasse')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Confirmer *</label>
                        <input type="password" name="motDePasse_confirmation" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:border-primary bg-primary-bg"
                            placeholder="••••••••">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mb-4">
                    Au moins 8 caractères, avec une majuscule, une minuscule, un chiffre et un caractère spécial.
                </p>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-primary text-white px-6 py-2 rounded-lg text-sm
                               hover:bg-primary-light font-medium">
                        Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        @else
            <div class="text-sm text-gray-700">
                La modification du mot de passe n'est pas autorisée depuis cette interface.
                Merci de contacter un administrateur pour effectuer cette mise à jour.
            </div>
        @endif
    </div>

    @if(Auth::user()->hasRole('administrateur'))
        <div class="bg-white border border-primary-pale rounded-xl p-6 mt-6">
            <h2 class="text-sm font-semibold text-primary-dark mb-3">
                Réinitialiser le mot de passe d'un responsable
            </h2>

            <form method="POST" action=""
                  id="adminPasswordResetForm" onsubmit="return validateAdminPasswordReset()">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Responsable *</label>
                    <select name="responsable_id" id="responsableSelect"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg"
                        onchange="updateAdminPasswordResetAction(this)">
                        <option value="">Sélectionner un responsable</option>
                        @foreach($responsables as $responsable)
                            @php $roleNom = $responsable->roles->first()?->name; @endphp
                            <option value="{{ $responsable->idUtilisateur }}">
                                {{ $responsable->prenom }} {{ $responsable->nom }} — {{ $roleNom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Nouveau mot de passe *</label>
                    <input type="password" name="motDePasse" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg"
                        placeholder="8 caractères minimum">
                </div>

                <div class="mb-5">
                    <label class="block text-xs text-gray-500 mb-1">Confirmer *</label>
                    <input type="password" name="motDePasse_confirmation" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg"
                        placeholder="••••••••">
                </div>

                <p class="text-xs text-gray-400 mb-4">
                    Ces mots de passe s'appliqueront uniquement aux responsables stock/commande.
                </p>

                <div class="flex justify-end gap-3">
                    <button type="submit"
                        class="bg-primary text-white px-6 py-2 rounded-lg text-sm
                               hover:bg-primary-light font-medium">
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>

        <script>
            const adminPasswordResetRoute = "{{ route('admin.utilisateur.password.update', ['id' => '__ID__']) }}";

            function updateAdminPasswordResetAction(select) {
                const form = document.getElementById('adminPasswordResetForm');
                if (select.value) {
                    form.action = adminPasswordResetRoute.replace('__ID__', select.value);
                } else {
                    form.action = '';
                }
            }

            function validateAdminPasswordReset() {
                const select = document.getElementById('responsableSelect');
                if (!select.value) {
                    alert('Veuillez sélectionner un responsable.');
                    return false;
                }
                return true;
            }
        </script>
    @endif
</div>
