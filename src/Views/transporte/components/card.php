<article class="cartao grid items-center gap-5 border-l-4 border-l-(--mc) !px-[18px] !py-4 md:grid-cols-[150px_minmax(0,1fr)_auto]" style="{{cores}}">
    <div class="flex h-[72px] min-w-0 flex-col items-center justify-center gap-1.5 rounded-[10px] border border-linha bg-white px-3.5 py-2.5" style="{{estiloLogo}}">
        <img src="{{logo}}" alt="{{empresa}}" class="block h-auto max-h-11 w-auto max-w-full">
        {{motorista}}
    </div>

    <div class="min-w-0">
        {{etiquetas}}
        <div class="flex items-center gap-3 font-display text-xl font-extrabold">
            {{horaPartida}}
            <span class="relative grid h-0.5 flex-[0_1_130px] place-items-center bg-[repeating-linear-gradient(90deg,var(--mb)_0_6px,transparent_6px_10px)]">{{icone}}</span>
            <span>{{horaChegada}}{{diasDepois}}</span>
            <span class="font-sans text-xs font-bold text-apagado">{{duracao}}</span>
        </div>
        <p class="mt-1 text-[13px] text-apagado">{{detalhes}}</p>
    </div>

    <div class="flex flex-col items-start gap-2 md:items-end">
        <span class="font-display text-[21px] font-extrabold leading-tight text-(--mc)">{{total}}</span>
        <span class="text-xs text-apagado md:text-right">{{porPessoa}}</span>
        <div class="flex flex-wrap gap-2 md:justify-end">
            <a href="{{linkSite}}" target="_blank" rel="noopener" class="btn-secundario">{{iconeSite}}Ver no site</a>
            {{botao}}
        </div>
    </div>
</article>
