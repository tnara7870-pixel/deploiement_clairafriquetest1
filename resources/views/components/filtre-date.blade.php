<div class="flex items-center gap-2">
    <div class="flex items-center gap-1">
        <label class="text-xs text-gray-400 shrink-0">Du</label>
        <input type="date" name="date_debut" value="{{ request('date_debut') }}"
            class="border border-gray-200 rounded-lg px-2 h-9 text-xs
                   focus:outline-none focus:border-primary bg-white">
    </div>
    <div class="flex items-center gap-1">
        <label class="text-xs text-gray-400 shrink-0">Au</label>
        <input type="date" name="date_fin" value="{{ request('date_fin') }}"
            class="border border-gray-200 rounded-lg px-2 h-9 text-xs
                   focus:outline-none focus:border-primary bg-white">
    </div>
</div>