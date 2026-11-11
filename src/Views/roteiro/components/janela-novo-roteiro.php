<dialog id="janela-novo-roteiro" class="m-auto w-[min(620px,calc(100vw-32px))] rounded-xl border border-linha bg-white p-6 text-tinta shadow-[0_24px_60px_rgba(15,29,54,.35)] backdrop:bg-[rgba(15,29,54,.55)]" aria-labelledby="titulo-janela-novo" {{abrirAoCarregar}}>
    <form method="POST" action="/roteiros/novo" class="flex flex-col gap-4" novalidate>
        {{csrf}}
        <input type="hidden" name="origemFormulario" value="janela">
        <input type="hidden" name="voltar" value="{{voltar}}">

        <div class="flex items-center justify-between gap-3">
            <h2 id="titulo-janela-novo" class="text-[19px] font-bold">Novo roteiro</h2>
            <button type="button" class="grid cursor-pointer place-items-center rounded-md p-1.5 text-apagado hover:bg-alerta-suave hover:text-alerta" data-fechar-janela aria-label="Fechar">{{iconeFechar}}</button>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-6">
            <div class="col-span-2 md:col-span-6">
                <label for="novo-titulo" class="rotulo">Título do roteiro</label>
                <input id="novo-titulo" name="titulo" value="{{valorTitulo}}" maxlength="80" placeholder="Ex.: Férias em Salvador" class="campo">
            </div>
            <div class="col-span-2 md:col-span-3">
                <label for="novo-origem" class="rotulo">Origem</label>
                <input id="novo-origem" name="origem" value="{{valorOrigem}}" maxlength="80" placeholder="Ex.: Birigui - SP" class="campo">
            </div>
            <div class="col-span-2 md:col-span-3">
                <label for="novo-destino" class="rotulo">Destino</label>
                <input id="novo-destino" name="destino" value="{{valorDestino}}" list="listaDestinosNovo" placeholder="Ex.: Salvador - BA" autocomplete="off" class="campo">
                <datalist id="listaDestinosNovo">{{datalistDestinos}}</datalist>
            </div>
            <div class="md:col-span-2">
                <label for="novo-ida" class="rotulo">Ida</label>
                <input id="novo-ida" name="dataInicio" type="date" value="{{valorDataInicio}}" min="{{dataMinima}}" class="campo">
            </div>
            <div class="md:col-span-2">
                <label for="novo-volta" class="rotulo">Volta</label>
                <input id="novo-volta" name="dataFim" type="date" value="{{valorDataFim}}" min="{{dataMinima}}" class="campo">
            </div>
            <div class="col-span-2 md:col-span-2">
                <label for="novo-pessoas" class="rotulo">Pessoas</label>
                <div class="flex h-11 overflow-hidden rounded-lg border border-linha bg-white focus-within:border-navy focus-within:ring-3 focus-within:ring-suave">
                    <button type="button" data-passo="novo-pessoas:-1" class="w-[38px] flex-none cursor-pointer bg-suave text-lg font-extrabold text-navy hover:bg-linha" aria-label="Menos uma pessoa">−</button>
                    <input id="novo-pessoas" name="quantidadePessoas" type="number" min="1" max="20" value="{{valorPessoas}}" class="w-full min-w-0 border-0 text-center font-bold outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                    <button type="button" data-passo="novo-pessoas:1" class="w-[38px] flex-none cursor-pointer bg-suave text-lg font-extrabold text-navy hover:bg-linha" aria-label="Mais uma pessoa">+</button>
                </div>
            </div>
            <div class="col-span-2 md:col-span-6">
                <label for="novo-orcamento" class="rotulo">Quanto quer gastar? (opcional)</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-apagado">R$</span>
                    <input id="novo-orcamento" name="orcamento" type="number" min="0" step="50" inputmode="decimal" placeholder="Ex.: 2500" value="{{valorOrcamento}}" class="campo !pl-[38px]">
                </div>
            </div>
        </div>

        {{erros}}

        <div class="flex flex-wrap items-center justify-between gap-3">
            <span class="dica">Título, destino e datas são obrigatórios.</span>
            <button type="submit" class="btn-destaque">Criar roteiro {{iconeSeta}}</button>
        </div>
    </form>
</dialog>
