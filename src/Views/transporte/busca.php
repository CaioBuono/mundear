{{bannerContexto}}

<div class="mb-6">
    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Transporte</h1>
    <p class="mt-1 text-slate-500">Compare voos e ônibus e adicione a melhor opção ao seu roteiro.</p>
</div>

<form method="GET" action="/transporte" class="cartao">
    {{campoContexto}}

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-[1fr_1fr_170px_120px]">
        <div>
            <label for="origem" class="rotulo">Origem</label>
            <input id="origem" name="origem" type="text" list="listaCidades" value="{{valorOrigem}}" placeholder="De onde você sai?" class="campo" required>
        </div>
        <div>
            <label for="destino" class="rotulo">Destino</label>
            <input id="destino" name="destino" type="text" list="listaCidades" value="{{valorDestino}}" placeholder="Para onde vai?" class="campo" required>
        </div>
        <div>
            <label for="data" class="rotulo">Data</label>
            <input id="data" name="data" type="date" value="{{valorData}}" min="{{dataMinima}}" class="campo" required>
        </div>
        <div>
            <label for="pessoas" class="rotulo">Passageiros</label>
            <input id="pessoas" name="pessoas" type="number" min="1" max="20" value="{{valorPessoas}}" class="campo" required>
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-[220px_220px_1fr] lg:items-end">
        <div>
            <label for="tipo" class="rotulo">Tipo</label>
            <select id="tipo" name="tipo" class="campo">{{opcoesTipo}}</select>
        </div>
        <div>
            <label for="ordem" class="rotulo">Ordenar por</label>
            <select id="ordem" name="ordem" class="campo">{{opcoesOrdem}}</select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="btn-primario w-full lg:w-auto">Buscar passagens</button>
        </div>
    </div>

    <datalist id="listaCidades">{{datalistCidades}}</datalist>
</form>

<p class="mt-3 text-xs text-slate-400">Resultados simulados — representam a resposta de APIs externas de companhias parceiras.</p>

<div class="mt-6 empty:hidden">{{feedback}}</div>

<section class="mt-6">
    {{resultados}}
</section>
