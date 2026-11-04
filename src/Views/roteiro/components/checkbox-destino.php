<label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-300 has-checked:border-blue-900 has-checked:bg-blue-50">
    <input type="checkbox" name="destinos[]" value="{{id}}" class="h-4 w-4 shrink-0 accent-blue-900" {{marcado}}>
    <span class="min-w-0">
        <span class="block truncate font-medium text-blue-950">{{cidade}}</span>
        <span class="block truncate text-xs text-slate-500">{{estado}}</span>
    </span>
</label>
