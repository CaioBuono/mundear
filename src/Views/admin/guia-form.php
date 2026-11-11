{{abas}}

<div class="mx-auto max-w-3xl">
    <nav class="mb-1.5 flex items-center gap-1.5 text-[13px] font-semibold text-apagado">
        <a href="/admin/guias" class="hover:text-navy">Guias turísticos</a>
        <span>/</span>
        <span class="text-tinta">{{tituloPagina}}</span>
    </nav>

    <h2 class="text-[22px] font-bold">{{tituloPagina}}</h2>

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
                <label for="valorDiaria" class="rotulo">Valor (R$)</label>
                <input id="valorDiaria" name="valorDiaria" type="text" inputmode="decimal" value="{{valorDiaria}}" placeholder="150,00" required class="campo">
            </div>
            <div>
                <label for="unidadeValor" class="rotulo">Cobrado por</label>
                <select id="unidadeValor" name="unidadeValor" class="campo">{{opcoesUnidade}}</select>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="idiomas" class="rotulo">Idiomas</label>
                <input id="idiomas" name="idiomas" type="text" value="{{valorIdiomas}}" placeholder="Ex.: Português, inglês" class="campo">
            </div>
            <div>
                <label for="regioes" class="rotulo">Regiões que atende</label>
                <input id="regioes" name="regioes" type="text" value="{{valorRegioes}}" placeholder="Ex.: Centro, Lapa, Santa Teresa" class="campo">
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="apresentacao" class="rotulo">Apresentação</label>
                <input id="apresentacao" name="apresentacao" type="text" maxlength="160" value="{{valorApresentacao}}" placeholder="Uma frase sobre a experiência do guia" class="campo">
            </div>
            <div>
                <label for="diaFolga" class="rotulo">Dia de folga</label>
                <select id="diaFolga" name="diaFolga" class="campo">{{opcoesFolga}}</select>
            </div>
        </div>

        <label for="ativo" class="flex items-start gap-2.5 rounded-lg border border-linha px-4 py-3">
            <input id="ativo" name="ativo" type="checkbox" value="1" class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja" {{marcadoAtivo}}>
            <span>
                <span class="block text-sm font-semibold">Guia ativo</span>
                <span class="dica block">Guias inativos não aparecem nas buscas e não podem ser adicionados a roteiros.</span>
            </span>
        </label>

        <div class="flex flex-col-reverse gap-3 border-t border-linha pt-5 sm:flex-row sm:justify-end">
            <a href="/admin/guias" class="btn-secundario">Cancelar</a>
            <button type="submit" class="btn-destaque">{{textoBotao}}</button>
        </div>
    </form>
</div>
