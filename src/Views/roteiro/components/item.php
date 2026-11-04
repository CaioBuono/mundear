<article class="flex scroll-mt-24 flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center" id="item-{{idItem}}">
    <div class="flex min-w-0 flex-1 items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{corIcone}}">{{icone}}</span>

        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide {{corTipo}}">{{tipo}}</p>
            <p class="font-semibold text-blue-950">{{descricao}}</p>
            <p class="text-sm text-slate-500">{{detalhes}}</p>
            <p class="mt-1 text-xs text-slate-500" data-descricao-quantidade="{{idItem}}">{{quantidade}}</p>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        {{controleQuantidade}}
        <p class="min-w-24 text-right font-bold text-blue-950" data-subtotal="{{idItem}}">{{subtotal}}</p>
        {{botaoRemover}}
    </div>
</article>
