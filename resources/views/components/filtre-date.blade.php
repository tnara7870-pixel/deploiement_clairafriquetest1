<div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
    <div class="grid min-w-0 grid-cols-[1.5rem_minmax(0,1fr)] items-center gap-1">
        <label class="text-xs text-gray-400 shrink-0">Du</label>
        <input type="date" name="date_debut" value="{{ request('date_debut') }}"
            class="w-full min-w-0 border border-gray-200 rounded-lg px-2 h-9 text-xs sm:w-auto
                   focus:outline-none focus:border-primary bg-white">
    </div>
    <div class="grid min-w-0 grid-cols-[1.5rem_minmax(0,1fr)] items-center gap-1">
        <label class="text-xs text-gray-400 shrink-0">Au</label>
        <input type="date" name="date_fin" value="{{ request('date_fin') }}"
            class="w-full min-w-0 border border-gray-200 rounded-lg px-2 h-9 text-xs sm:w-auto
                   focus:outline-none focus:border-primary bg-white">
    </div>
</div>