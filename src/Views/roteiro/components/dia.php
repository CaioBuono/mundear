<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" id="dia-{{data}}">
    <header class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/70 px-5 py-3">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 flex-col items-center justify-center rounded-xl bg-blue-950 text-white">
                <span class="text-[10px] uppercase leading-none tracking-wide text-white/70">Dia</span>
                <span class="text-base font-bold leading-tight">{{numero}}</span>
            </div>
            <div>
                <p class="font-semibold text-blue-950">{{dataFormatada}}</p>
                <p class="text-xs text-slate-500">{{diaSemana}}</p>
            </div>
        </div>
        {{acaoDia}}
    </header>

    <div class="divide-y divide-slate-100">
        {{itens}}
    </div>
</section>
