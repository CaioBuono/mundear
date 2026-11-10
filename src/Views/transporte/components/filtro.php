<form method="GET" action="/transporte" class="flex flex-wrap items-center gap-x-[18px] gap-y-2 text-[13.5px]" data-filtro-preco>
    <input type="hidden" name="roteiro" value="{{idRoteiro}}">
    <input type="hidden" name="modo" value="{{modo}}">
    <span class="text-[11.5px] font-bold uppercase tracking-[.07em] text-apagado">Ordenado por menor preço</span>
    <div class="flex flex-wrap items-center gap-2.5">
        <label for="precoMaximo">Preço por pessoa: <b data-texto-preco>{{textoFiltro}}</b></label>
        <input type="range" id="precoMaximo" name="precoMaximo" min="50" max="{{maximoFiltro}}" step="10" value="{{valorFiltro}}" class="w-[180px] max-w-full accent-laranja">
        <span class="dica">R$ 50 a R$ 1.000 ou mais</span>
    </div>
    <span class="ml-auto text-apagado">{{contagem}}</span>
</form>
