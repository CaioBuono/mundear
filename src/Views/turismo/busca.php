{{bannerContexto}}

<div class="mb-6">
    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Turismo</h1>
    <p class="mt-1 text-slate-500">Guias turísticos locais e passeios para os dias livres da sua viagem.</p>
</div>

<form method="GET" action="/turismo" class="cartao">
    {{campoContexto}}

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-[1fr_1fr_170px_120px_auto] lg:items-end">
        <div>
            <label for="cidade" class="rotulo">Cidade</label>
            <input id="cidade" name="cidade" type="text" list="listaCidades" value="{{valorCidade}}" placeholder="Ex.: Salvador" class="campo" required>
        </div>
        <div>
            <label for="especialidade" class="rotulo">Especialidade <span class="font-normal text-slate-400">(opcional)</span></label>
            <input id="especialidade" name="especialidade" type="text" value="{{valorEspecialidade}}" placeholder="Ex.: gastronomia, trilhas" class="campo">
        </div>
        <div>
            <label for="data" class="rotulo">Data</label>
            <input id="data" name="data" type="date" value="{{valorData}}" min="{{dataMinima}}" class="campo">
        </div>
        <div>
            <label for="pessoas" class="rotulo">Pessoas</label>
            <input id="pessoas" name="pessoas" type="number" min="1" max="20" value="{{valorPessoas}}" class="campo" required>
        </div>
        <button type="submit" class="btn-primario w-full">Buscar</button>
    </div>

    <datalist id="listaCidades">{{datalistCidades}}</datalist>
</form>

<div class="mt-6 empty:hidden">{{feedback}}</div>

<div class="mt-6 empty:hidden">{{estadoInicial}}</div>

{{guias}}

{{passeios}}
