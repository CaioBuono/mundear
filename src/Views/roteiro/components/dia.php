<section class="cartao scroll-mt-24 overflow-hidden !p-0" id="dia-{{data}}">
    <header class="flex flex-wrap items-center gap-3.5 border-b border-linha bg-[#f8fafc] px-[18px] py-3.5">
        <span class="flex h-12 w-12 flex-none flex-col items-center justify-center rounded-[10px] bg-navy leading-none text-white">
            <small class="text-[9.5px] font-bold uppercase tracking-[.08em] opacity-75">Dia</small>
            <b class="mt-0.5 font-display text-xl">{{numero}}</b>
        </span>
        <span>
            <b class="block font-display text-base">{{dataFormatada}}</b>
            <span class="text-[12.5px] text-apagado">{{diaSemana}}</span>
        </span>
        {{acoesDia}}
    </header>

    <div class="divide-y divide-dashed divide-linha">
        {{itens}}
    </div>

    {{anotacao}}
</section>
