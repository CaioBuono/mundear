<div class="mx-auto max-w-3xl">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="/roteiros" class="hover:text-blue-900">Meus roteiros</a>
        <span class="mx-1">/</span>
        <span class="text-slate-700">Novo roteiro</span>
    </nav>

    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Novo roteiro</h1>
    <p class="mt-1 text-slate-500">Comece pelos dados iniciais da viagem. Depois é só adicionar transporte, hospedagem, guias e passeios.</p>

    <div class="mt-6 empty:hidden">{{feedback}}</div>

    <form method="POST" action="/roteiros/novo" class="cartao mt-6 space-y-6">
        {{csrf}}

        <div>
            <label for="titulo" class="rotulo">Nome da viagem</label>
            <input id="titulo" name="titulo" type="text" value="{{valorTitulo}}" maxlength="80" placeholder="Ex.: Férias em família no Nordeste" required class="campo">
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="dataInicio" class="rotulo">Data de ida</label>
                <input id="dataInicio" name="dataInicio" type="date" value="{{valorDataInicio}}" min="{{dataMinima}}" required class="campo">
            </div>
            <div>
                <label for="dataFim" class="rotulo">Data de volta</label>
                <input id="dataFim" name="dataFim" type="date" value="{{valorDataFim}}" min="{{dataMinima}}" required class="campo">
            </div>
        </div>

        <fieldset>
            <legend class="rotulo">Destinos</legend>
            <p class="-mt-1 mb-3 text-sm text-slate-500">Selecione uma ou mais cidades. Os guias turísticos precisam atuar em um desses destinos.</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {{opcoesDestinos}}
            </div>
        </fieldset>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="/roteiros" class="btn-secundario">Cancelar</a>
            <button type="submit" class="btn-destaque">Criar roteiro</button>
        </div>
    </form>
</div>
