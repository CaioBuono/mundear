<a href="/roteiros/editar?id={{id}}" class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <h3 class="text-lg font-semibold text-blue-950 group-hover:text-blue-800">{{titulo}}</h3>
        {{status}}
    </div>

    <p class="mt-2 flex items-center gap-1.5 text-sm text-slate-600">{{iconePin}} {{destinos}}</p>
    <p class="mb-5 mt-1 flex items-center gap-1.5 text-sm text-slate-500">{{iconeCalendario}} {{periodo}} · {{dias}}</p>

    <div class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
        <div>
            <p class="text-xs text-slate-500">{{itens}}</p>
            {{link}}
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-500">Total estimado</p>
            <p class="text-xl font-bold text-blue-950">{{total}}</p>
        </div>
    </div>
</a>
