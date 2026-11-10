<article class="cartao border-l-4 border-l-(--mc) !p-0" style="{{cores}}">
    <div class="grid items-center gap-5 py-3.5 pl-3.5 pr-[18px] md:grid-cols-[220px_minmax(0,1fr)_auto]">
        {{galeria}}

        <div class="min-w-0">
            <h3 class="text-[16.5px] font-bold">{{nome}}</h3>
            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[13px] text-apagado">{{iconePin}}{{local}} · <span class="text-[13px] font-bold text-laranja-escuro">★ {{nota}}</span></div>
            <div class="dica mt-0.5">{{quarto}}</div>
            <div class="mt-2 flex flex-wrap gap-1.5">{{comodidades}}</div>
        </div>

        <div class="flex flex-col items-start gap-2 md:items-end">
            <span class="text-[11.5px] font-semibold text-apagado">menor preço em {{siteMenor}}</span>
            <span class="font-display text-[21px] font-extrabold leading-tight text-(--mc)">{{total}}</span>
            <span class="text-xs text-apagado md:text-right">{{porNoite}}</span>
            <div class="flex flex-wrap gap-2 md:justify-end">
                <button type="button" class="btn-secundario group" data-alternar="{{idComparar}}" aria-expanded="false">Comparar {{quantidadeSites}} sites{{iconeBaixo}}</button>
                {{botao}}
            </div>
        </div>
    </div>

    <div id="{{idComparar}}" class="rounded-b-xl border-t border-linha bg-[#f8fafc] px-[18px] pb-3 pt-1.5" hidden>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] border-collapse text-sm">
                <thead>
                    <tr class="text-left text-[11.5px] font-bold uppercase tracking-[.06em] text-apagado">
                        <th class="px-2 pb-1.5 pt-2.5">Site</th>
                        <th class="px-2 pb-1.5 pt-2.5 text-right">Por noite</th>
                        <th class="px-2 pb-1.5 pt-2.5 text-right">Total ({{textoNoites}})</th>
                        <th class="px-2 pb-1.5 pt-2.5 text-right">Diferença</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>{{linhasComparacao}}</tbody>
            </table>
        </div>
    </div>
</article>
