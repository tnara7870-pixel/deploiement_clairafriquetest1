@php
    $espaceNotifPage = explode('.', request()->route()->getName())[0];
@endphp

<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold text-primary-dark">Notifications</h1>
        @if($notifications->getCollection()->contains(fn($n) => $n->read_at === null))
        <form method="POST" action="{{ route($espaceNotifPage . '.notifications.toutesLues') }}">
            @csrf @method('PATCH')
            <button type="submit" class="text-xs text-primary hover:underline">Tout marquer lu</button>
        </form>
        @endif
    </div>

    <div class="bg-white border border-primary-pale rounded-xl divide-y divide-primary-pale overflow-hidden">
        @forelse($notifications as $n)
            <form method="POST" action="{{ route($espaceNotifPage . '.notifications.lue', $n->id) }}">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-full text-left px-5 py-4 hover:bg-primary-bg transition
                           {{ $n->read_at ? '' : 'bg-primary-bg/60' }}">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-800">{{ $n->data['titre'] ?? '' }}</div>
                        @if(!$n->read_at)
                        <span class="w-2 h-2 rounded-full bg-red-600 flex-shrink-0"></span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500 mt-1">{{ $n->data['message'] ?? '' }}</div>
                    <div class="text-xs text-gray-400 mt-1.5">{{ $n->created_at->format('d/m/Y à H:i') }}</div>
                </button>
            </form>
        @empty
            <p class="text-sm text-gray-400 px-5 py-8 text-center">Aucune notification pour le moment.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
