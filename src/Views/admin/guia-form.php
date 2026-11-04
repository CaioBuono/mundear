{{abas}}

<div class="mx-auto max-w-3xl">
    <nav class="mb-4 text-sm text-slate-500">
        <a href="/admin/guias" class="hover:text-blue-900">Guias turísticos</a>
        <span class="mx-1">/</span>
        <span class="text-slate-700">{{tituloPagina}}</span>
    </nav>

    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">{{tituloPagina}}</h1>

    <div class="mt-6 empty:hidden">{{feedback}}</div>

    <form method="POST" action="{{action}}" class="cartao mt-6 space-y-5">
        {{csrf}}
        <input type="hidden" name="idGuia" value="{{idGuia}}">

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="nome" class="rotulo">Nome</label>
                <input id="nome" name="nome" type="text" value="{{valorNome}}" required minlength="3" class="campo">
            </div>
            <div>
                <label for="telefone" class="rotulo">Telefone (com DDD)</label>
                <input id="telefone" name="telefone" type="tel" value="{{valorTelefone}}" placeholder="(71) 99999-9999" required class="campo">
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="especialidade" class="rotulo">Especialidade</label>
                <input id="especialidade" name="especialidade" type="text" value="{{valorEspecialidade}}" placeholder="Ex.: História e arquitetura" required class="campo">
            </div>
            <div>
                <label for="cidadeAtuacao" class="rotulo">Cidade de atuação</label>
                <select id="cidadeAtuacao" name="cidadeAtuacao" required class="campo">{{opcoesCidade}}</select>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="valorDiaria" class="rotulo">Valor da diária (R$)</label>
                <input id="valorDiaria" name="valorDiaria" type="text" inputmode="decimal" value="{{valorDiaria}}" placeholder="350,00" required class="campo">
            </div>
            <div>
                <label for="diaFolga" class="rotulo">Dia de folga</label>
                <select id="diaFolga" name="diaFolga" class="campo">{{opcoesFolga}}</select>
            </div>
        </div>

        <label for="ativo" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
            <input id="ativo" name="ativo" type="checkbox" value="1" class="h-4 w-4 accent-blue-900" {{marcadoAtivo}}>
            <span>
                <span class="block font-medium text-blue-950">Guia ativo</span>
                <span class="block text-xs text-slate-500">Guias inativos não aparecem nas buscas e não podem ser adicionados a roteiros.</span>
            </span>
        </label>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <a href="/admin/guias" class="btn-secundario">Cancelar</a>
            <button type="submit" class="btn-destaque">{{textoBotao}}</button>
        </div>
    </form>
</div>
