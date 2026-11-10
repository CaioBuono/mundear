<div class="flex flex-col gap-[22px]">
    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2" role="group" aria-label="Tipo de atividade">
        {{abas}}
    </div>

    <form method="GET" action="/turismo" class="flex h-12 items-center gap-2.5 rounded-xl border border-linha bg-white px-3.5 shadow-cartao focus-within:border-navy focus-within:ring-3 focus-within:ring-suave">
        <input type="hidden" name="roteiro" value="{{idRoteiro}}">
        <input type="hidden" name="modo" value="{{modo}}">
        {{campoData}}
        {{iconeBusca}}
        <label for="busca-turismo" class="sr-only">Buscar</label>
        <input id="busca-turismo" name="q" type="search" value="{{termo}}" placeholder="{{dicaBusca}}" autocomplete="off" class="h-full w-full min-w-0 border-0 bg-transparent outline-none placeholder:text-apagado/70">
        <button type="submit" class="btn-primario !h-9 !px-3 !text-[13px]">Buscar</button>
    </form>

    <div class="flex flex-wrap items-center gap-[18px] text-[13.5px]">
        <span class="text-[11.5px] font-bold uppercase tracking-[.07em] text-apagado">{{rotulo}}</span>
        <span class="ml-auto text-apagado">{{contagem}}</span>
    </div>

    <div class="flex flex-col gap-3">
        {{resultados}}
    </div>
</div>
