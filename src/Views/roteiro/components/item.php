<article class="flex scroll-mt-24 flex-wrap items-center gap-x-3.5 gap-y-2 px-[18px] py-3" id="{{ancora}}">
    <span class="grid h-[38px] w-[38px] flex-none place-items-center rounded-[10px] {{corIcone}}">{{icone}}</span>

    <div class="min-w-0 flex-1 basis-[calc(100%-52px)] sm:basis-0">
        <b class="block text-[14.5px]">{{descricao}}</b>
        <span class="text-[13px] text-apagado">{{detalhes}}</span>
        <span class="block text-xs text-apagado">{{quantidade}}</span>
    </div>

    <div class="ml-auto flex items-center gap-3">
        {{controleQuantidade}}
        <span class="whitespace-nowrap font-bold" data-subtotal="{{idSubtotal}}">{{subtotal}}</span>
        {{botaoRemover}}
    </div>
</article>
