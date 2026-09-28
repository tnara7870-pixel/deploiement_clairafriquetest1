@php
    // L'espace est normalement passé explicitement par le layout parent
    $espaceNotif = $espaceNotif ?? explode('.', request()->route()->getName())[0];
    
    // Couleur du déclencheur adaptable au fond du layout parent
    $classeDeclencheur = $classeDeclencheur ?? 'text-gray-500 hover:text-primary-dark';

    // Rendu synchrone côté serveur — seules les non-lues apparaissent
    // dans l'aperçu de la cloche ; l'historique complet (lues incluses)
    // reste consultable via "Voir tout".
    $notifNonLues  = auth()->user()->unreadNotifications()->count();
    $notifRecentes = auth()->user()->unreadNotifications()->latest()->limit(8)->get();
@endphp

<div class="relative" id="menu-notif-wrapper">
    <button type="button" id="btn-menu-notif" onclick="toggleMenuNotifications()"
        aria-haspopup="true" aria-expanded="false" aria-controls="menu-notif-dropdown"
        aria-label="Notifications{{ $notifNonLues > 0 ? ' (' . $notifNonLues . ' non lues)' : '' }}"
        data-route-toutes-lues="{{ route($espaceNotif . '.notifications.toutesLues') }}"
        data-csrf="{{ csrf_token() }}"
        class="relative text-xs flex items-center {{ $classeDeclencheur }}">
        <x-heroicon-o-bell class="w-4 h-4 inline-block flex-shrink-0 align-[-3px]" />
        @if($notifNonLues > 0)
        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-semibold">
            {{ $notifNonLues > 9 ? '9+' : $notifNonLues }}
        </span>
        @endif
    </button>

    <div id="menu-notif-dropdown" class="absolute right-0 top-full mt-1 w-80 bg-white border border-primary-pale rounded-xl shadow-lg hidden z-50 max-h-96 overflow-y-auto">
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-primary-pale">
            <span class="text-xs font-semibold text-gray-600">Notifications</span>
            <div class="flex items-center gap-3">
                @if($notifNonLues > 0)
                <form method="POST" action="{{ route($espaceNotif . '.notifications.toutesLues') }}">
                    @csrf 
                    @method('PATCH')
                    <button type="submit" class="text-xs text-primary hover:underline">Tout marquer lu</button>
                </form>
                @endif
                <a href="{{ route($espaceNotif . '.notifications') }}" class="text-xs text-gray-400 hover:underline">Voir tout</a>
            </div>
        </div>

        <div class="divide-y divide-primary-pale">
            @forelse($notifRecentes as $n)
                <form method="POST" action="{{ route($espaceNotif . '.notifications.lue', $n->id) }}">
                    @csrf 
                    @method('PATCH')
                    <button type="submit" class="w-full text-left px-4 py-3 hover:bg-primary-bg transition {{ $n->read_at ? '' : 'bg-primary-bg/60' }}">
                        <div class="text-xs font-semibold text-gray-800">{{ $n->data['titre'] ?? '' }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $n->data['message'] ?? '' }}</div>
                        <div class="text-[10px] text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                    </button>
                </form>
            @empty
                <p class="text-xs text-gray-400 px-4 py-4">Aucune notification.</p>
            @endforelse
        </div>
    </div>
</div>

@once
<script>
function toggleMenuNotifications() {
    const menu = document.getElementById('menu-notif-dropdown');
    const btn  = document.getElementById('btn-menu-notif');
    const ouvert = !menu.classList.contains('hidden');
    menu.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', ouvert ? 'false' : 'true');

    // À l'ouverture (pas à la fermeture) : on marque comme lues les
    // notifications actuellement affichées, sans recharger la page — au
    // prochain aperçu elles auront disparu, comme sur un téléphone.
    // Elles restent visibles indéfiniment dans "Voir tout" (l'historique
    // complet n'est jamais filtré sur read_at).
    if (!ouvert) {
        const route = btn.dataset.routeToutesLues;
        const token = btn.dataset.csrf;
        if (route && token) {
            fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({ _method: 'PATCH' }),
            }).catch(() => {});
        }
    }
}

document.addEventListener('click', function (e) {
    const wrapper = document.getElementById('menu-notif-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('menu-notif-dropdown')?.classList.add('hidden');
        document.getElementById('btn-menu-notif')?.setAttribute('aria-expanded', 'false');
    }
});
</script>
@endonce