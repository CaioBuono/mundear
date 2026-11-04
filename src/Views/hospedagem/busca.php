{{bannerContexto}}

<div class="mb-6">
    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Hospedagem</h1>
    <p class="mt-1 text-slate-500">Hotéis, pousadas e hostels para o período da sua viagem.</p>
</div>

<form method="GET" action="/hospedagem" class="cartao">
    {{campoContexto}}

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-[1fr_170px_170px_120px]">
        <div>
            <label for="cidade" class="rotulo">Cidade</label>
            <input id="cidade" name="cidade" type="text" list="listaCidades" value="{{valorCidade}}" placeholder="Onde você vai ficar?" class="campo" required>
        </div>
        <div>
            <label for="dataInicio" class="rotulo">Entrada</label>
            <input id="dataInicio" name="dataInicio" type="date" value="{{valorDataInicio}}" min="{{dataMinima}}" class="campo" required>
        </div>
        <div>
            <label for="dataFim" class="rotulo">Saída</label>
            <input id="dataFim" name="dataFim" type="date" value="{{valorDataFim}}" min="{{dataMinima}}" class="campo" required>
        </div>
        <div>
            <label for="pessoas" class="rotulo">Hóspedes</label>
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
            <button type="submit" class="btn-primario w-full lg:w-auto">Buscar hospedagens</button>
        </div>
    </div>

    <datalist id="listaCidades">{{datalistCidades}}</datalist>
</form>

<p class="mt-3 text-xs text-slate-400">Resultados simulados — representam a resposta de APIs externas de redes hoteleiras parceiras.</p>

<div class="mt-6 empty:hidden">{{feedback}}</div>

<section class="mt-6">
    {{resultados}}
</section>
