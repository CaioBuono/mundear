<article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex h-28 items-center justify-center bg-gradient-to-br from-orange-100 via-amber-50 to-blue-50 text-orange-500">
        {{iconeCama}}
    </div>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-semibold text-orange-700">{{tipo}}</span>
                <h3 class="mt-2 font-semibold text-blue-950">{{nome}}</h3>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-sm font-semibold text-slate-700">
                {{iconeEstrela}} {{avaliacao}}
            </span>
        </div>

        <p class="mt-2 flex items-start gap-1.5 text-sm text-slate-500">{{iconePin}} {{endereco}} · {{cidade}}</p>

        <div class="mt-auto pt-5">
            <div class="flex items-end justify-between gap-3 border-t border-slate-100 pt-4">
                <div>
                    <p class="text-xs text-slate-500">{{resumoEstadia}}</p>
                    <p class="text-sm text-slate-500"><span class="font-semibold text-blue-950">{{precoDiaria}}</span> / diária</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500">Subtotal</p>
                    <p class="text-xl font-bold text-orange-500">{{subtotal}}</p>
                </div>
            </div>

            <div class="mt-4">
                {{formAdicionar}}
            </div>
        </div>
    </div>
</article>
