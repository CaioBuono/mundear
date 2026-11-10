<article class="cartao border-l-4 border-l-(--mc) !p-0" style="{{cores}}">
    <div class="grid items-center gap-5 py-3.5 pl-3.5 pr-[18px] md:grid-cols-[220px_minmax(0,1fr)_auto]">
        {{galeria}}

        <div class="min-w-0">
            <h3 class="text-[16.5px] font-bold">{{nome}}</h3>
            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[13px] text-apagado">{{iconePin}}{{local}} · duração média {{duracao}}</div>
            <div class="mt-2 flex flex-wrap gap-1.5">{{etiquetas}}</div>
        </div>

        <div class="flex flex-col items-start gap-2 md:items-end">
            <span class="font-display text-[21px] font-extrabold leading-tight text-(--mc)">{{total}}</span>
            <span class="text-xs text-apagado md:text-right">{{porPessoa}}</span>
            <div class="flex flex-wrap gap-2 md:justify-end">{{botao}}</div>
        </div>
    </div>
</article>
