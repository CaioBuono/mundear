<a href="/roteiros/editar?id={{id}}" class="cartao flex flex-col gap-1.5 !p-5 transition hover:border-navy hover:shadow-[0_2px_4px_rgba(19,40,77,.08),0_10px_24px_rgba(19,40,77,.1)]">
    <span class="mb-1.5 flex items-start justify-between gap-2.5">
        <h3 class="text-[17.5px] font-bold">{{titulo}}</h3>
        {{status}}
    </span>
    <span class="flex items-center gap-2 text-sm text-apagado">{{iconePin}}{{destinos}}</span>
    <span class="flex items-center gap-2 text-sm text-apagado">{{iconeCalendario}}{{periodo}} · {{dias}}</span>
    <span class="flex items-center gap-2 text-sm text-apagado">{{iconePessoas}}{{pessoas}}</span>
    <span class="mt-2.5 flex items-end justify-between gap-2.5 border-t border-linha pt-3.5">
        <span class="flex flex-col gap-0.5">
            <span class="dica">{{itens}}</span>
            {{link}}
        </span>
        <span class="flex flex-col items-end">
            <small class="text-xs text-apagado">Total estimado</small>
            <b class="font-display text-xl font-extrabold text-navy">{{total}}</b>
        </span>
    </span>
</a>
