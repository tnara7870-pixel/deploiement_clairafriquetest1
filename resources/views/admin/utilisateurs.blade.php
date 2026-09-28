@extends('layouts.admin')
@section('title', 'Utilisateurs')
@section('page_title', 'Gestion des utilisateurs')

@section('content')

{{-- Bouton en haut --}}
<div class="flex justify-between items-center mb-5">
    <span class="text-sm text-gray-500">{{ $utilisateurs->total() }} utilisateur(s)</span>
    <div class="flex items-center gap-3">
        <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')"
            class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
            + Nouvel utilisateur
        </button>
        <button type="button" onclick="openPasswordModal('', '')"
            class="bg-secondary text-white px-4 py-2 rounded-lg text-sm hover:bg-secondary-light">
            Réinitialiser un mot de passe
        </button>
    </div>
</div>

{{-- Modal création --}}
<div id="modal-add-user"
    class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Nouvel utilisateur</h3>
            <button onclick="document.getElementById('modal-add-user').classList.add('hidden')"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.utilisateur.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nom *</label>
                    <input type="text" name="nom" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Prénom *</label>
                    <input type="text" name="prenom" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-primary bg-primary-bg">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Email *</label>
                <input type="email" name="email" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg">
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Téléphone</label>
                <input type="text" name="telephone"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="+221 77 000 00 00">
            </div>
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Rôle *</label>
                <select name="role" id="add-role-select" onchange="toggleAddPointVente(this.value)"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none bg-white">
                    <option value="res.stock">Res. Stock</option>
                    <option value="res.commande">Res. Commande</option>
                    <option value="administrateur">Administrateur</option>
                </select>
            </div>
            <div class="mb-4" id="add-point-vente-wrapper">
                <label class="block text-xs text-gray-500 mb-1">Point de vente assigné</label>
                <select name="pointVenteAssigne"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none bg-white">
                    <option value="">Accès global (aucun point assigné)</option>
                    <option value="ucad">UCAD</option>
                    <option value="centre_ville">Centre-ville</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">
                    Laisser sur "Accès global" pour un compte qui doit voir les deux points de vente.
                </p>
            </div>
            <div class="mb-5">
                <label class="block text-xs text-gray-500 mb-1">Mot de passe *</label>
                <input type="password" name="motDePasse" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="8 caractères minimum">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('modal-add-user').classList.add('hidden')"
                    class="border border-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
                    Annuler
                </button>
                <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAddPointVente(role) {
        document.getElementById('add-point-vente-wrapper').classList.toggle('hidden', role === 'administrateur');
    }

    const roleRouteTemplate = "{{ route('admin.utilisateur.role', ['id' => 'USER_ID']) }}";

    function openRoleModal(userId, roleActuel, pointActuel) {
        document.getElementById('roleForm').action = roleRouteTemplate.replace('USER_ID', userId);
        const roleSelect = document.getElementById('role-edit-select');
        roleSelect.value = roleActuel;
        document.getElementById('point-vente-edit-select').value = pointActuel || '';
        togglePointVenteEdit(roleActuel);
        document.getElementById('modal-role').classList.remove('hidden');
    }

    function togglePointVenteEdit(role) {
        document.getElementById('point-vente-edit-wrapper').classList.toggle('hidden', role === 'administrateur');
    }

    function closeRoleModal() {
        document.getElementById('modal-role').classList.add('hidden');
    }

    const passwordRouteTemplate = "{{ route('admin.utilisateur.password.update', ['id' => 'USER_ID']) }}";

    function openPasswordModal(userName, userId) {
        const passwordModalUser = document.getElementById('passwordModalUser');
        const passwordForm = document.getElementById('passwordForm');
        const passwordUserSelectWrapper = document.getElementById('passwordUserSelectWrapper');
        const passwordUserSelect = document.getElementById('passwordUserSelect');

        if (userId) {
            passwordModalUser.textContent = userName;
            passwordForm.action = passwordRouteTemplate.replace('USER_ID', userId);
            passwordUserSelectWrapper.classList.add('hidden');
            passwordUserSelect.value = '';
        } else {
            passwordModalUser.textContent = 'Sélectionnez un responsable';
            passwordForm.action = '';
            passwordUserSelectWrapper.classList.remove('hidden');
        }

        document.getElementById('modal-password').classList.remove('hidden');
    }

    function updatePasswordFormAction(select) {
        const passwordForm = document.getElementById('passwordForm');
        const passwordModalUser = document.getElementById('passwordModalUser');

        if (select.value) {
            passwordForm.action = passwordRouteTemplate.replace('USER_ID', select.value);
            passwordModalUser.textContent = select.options[select.selectedIndex].text;
        } else {
            passwordForm.action = '';
            passwordModalUser.textContent = 'Sélectionnez un responsable';
        }
    }

    function closePasswordModal() {
        document.getElementById('modal-password').classList.add('hidden');
    }

    function validatePasswordForm() {
        const form = document.getElementById('passwordForm');
        if (!form.action) {
            alert('Veuillez sélectionner un responsable pour réinitialiser le mot de passe.');
            return false;
        }
        return true;
    }
</script>

<form method="GET"
    class="bg-white border border-primary-pale rounded-xl p-4 mb-5 flex items-center gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Rechercher par nom ou email…"
        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:border-primary bg-primary-bg h-9">
    <select name="role"
        class="border border-gray-200 rounded-lg px-3 h-9 text-sm focus:outline-none bg-white">
        <option value="">Tous les rôles</option>
        <option value="res.stock"     {{ request('role') == 'res.stock'     ? 'selected' : '' }}>Res. Stock</option>
        <option value="res.commande"  {{ request('role') == 'res.commande'  ? 'selected' : '' }}>Res. Commande</option>
        <option value="administrateur"{{ request('role') == 'administrateur'? 'selected' : '' }}>Administrateur</option>
    </select>
    <button type="submit"
        class="bg-primary text-white px-4 h-9 rounded-lg text-sm hover:bg-primary-light shrink-0">
        Filtrer
    </button>
    <a href="{{ route('admin.utilisateurs') }}"
        class="border border-gray-200 text-gray-500 px-4 h-9 flex items-center
               rounded-lg text-sm hover:bg-gray-50 shrink-0">
        Réinitialiser
    </a>
</form>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Utilisateur</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Téléphone</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Rôle</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Point de vente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($utilisateurs as $user)
            @php
                $roleNom = $user->roles->first()?->name;
                $estAdmin = $roleNom === 'administrateur';
            @endphp
            <tr class="hover:bg-primary-bg">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-primary-pale flex items-center
                                    justify-center text-primary text-xs font-semibold">
                            {{ strtoupper(substr($user->prenom, 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-gray-800">
                            {{ $user->prenom }} {{ $user->nom }}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $user->email }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $user->telephone ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $estAdmin
                            ? 'bg-purple-100 text-purple-700'
                            : 'bg-blue-100 text-blue-700' }}">
                        {{ $roleNom ?? '—' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs">
                    @if(in_array($roleNom, ['res.stock', 'res.commande']))
                        @if($user->pointVenteAssigne)
                            <span class="px-2 py-1 rounded-full font-medium bg-primary-pale text-primary-dark">
                                {{ $user->pointVenteAssigne === 'ucad' ? 'UCAD' : 'Centre-ville' }}
                            </span>
                        @else
                            <span class="text-gray-400 italic">Accès global</span>
                        @endif
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $user->statut
                            ? 'bg-primary-pale text-primary-dark'
                            : 'bg-red-100 text-red-600' }}">
                        {{ $user->statut ? 'Actif' : 'Bloqué' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if(!$estAdmin)
                            <button type="button"
                                onclick="openRoleModal({{ $user->idUtilisateur }}, '{{ $roleNom }}', '{{ $user->pointVenteAssigne }}')"
                                class="text-xs text-primary hover:underline">
                                Modifier
                            </button>
                        @endif

                        @if(in_array($roleNom, ['res.stock', 'res.commande']))
                            <button type="button"
                                onclick="openPasswordModal('{{ addslashes($user->prenom . ' ' . $user->nom) }}', {{ $user->idUtilisateur }})"
                                class="text-xs text-primary hover:underline">
                                Mot de passe
                            </button>
                        @endif

                        @if(!$estAdmin)
                            <form method="POST"
                                action="{{ route('admin.utilisateur.bloquer', $user->idUtilisateur) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-medium
                                        {{ $user->statut
                                            ? 'text-red-500 hover:underline'
                                            : 'text-primary hover:underline' }}">
                                    {{ $user->statut ? 'Bloquer' : 'Débloquer' }}
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-300 italic">Protégé</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucun utilisateur trouvé.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $utilisateurs->withQueryString()->links() }}</div>

<div id="modal-role"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Modifier le rôle / point de vente</h3>
            <button type="button" onclick="closeRoleModal()"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>

        <form id="roleForm" method="POST" action="">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Rôle *</label>
                <select id="role-edit-select" name="role" onchange="togglePointVenteEdit(this.value)"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none bg-white">
                    <option value="res.stock">Res. Stock</option>
                    <option value="res.commande">Res. Commande</option>
                    <option value="administrateur">Administrateur</option>
                </select>
            </div>

            <div class="mb-5" id="point-vente-edit-wrapper">
                <label class="block text-xs text-gray-500 mb-1">Point de vente assigné</label>
                <select id="point-vente-edit-select" name="pointVenteAssigne"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none bg-white">
                    <option value="">Accès global (aucun point assigné)</option>
                    <option value="ucad">UCAD</option>
                    <option value="centre_ville">Centre-ville</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">
                    Un compte scopé à un point ne voit et ne gère plus que les commandes/le stock de ce point.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRoleModal()"
                    class="border border-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
                    Annuler
                </button>
                <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-password"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-sm font-semibold text-primary-dark">Réinitialiser le mot de passe</h3>
            <button type="button" onclick="closePasswordModal()"
                class="text-gray-400 text-xl leading-none">&times;</button>
        </div>

        <form id="passwordForm" method="POST" action="" onsubmit="return validatePasswordForm()">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Utilisateur</label>
                <p id="passwordModalUser" class="text-sm text-gray-700">Sélectionnez un responsable</p>
            </div>

            <div id="passwordUserSelectWrapper" class="mb-4 hidden">
                <label class="block text-xs text-gray-500 mb-1">Responsable *</label>
                <select id="passwordUserSelect" onchange="updatePasswordFormAction(this)"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg">
                    <option value="">Sélectionner un responsable</option>
                    @foreach($utilisateurs as $user)
                        @php $roleNom = $user->roles->first()?->name; @endphp
                        @if(in_array($roleNom, ['res.stock', 'res.commande']))
                            <option value="{{ $user->idUtilisateur }}">
                                {{ $user->prenom }} {{ $user->nom }} ({{ $roleNom }})
                            </option>
                        @endif
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
                <label class="block text-xs text-gray-500 mb-1">Confirmer le mot de passe *</label>
                <input type="password" name="motDePasse_confirmation" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:border-primary bg-primary-bg"
                    placeholder="Confirmez le mot de passe">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closePasswordModal()"
                    class="border border-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm">
                    Annuler
                </button>
                <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection