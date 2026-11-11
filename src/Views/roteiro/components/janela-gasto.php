<dialog id="janela-gasto" class="m-auto w-[min(460px,calc(100vw-32px))] rounded-xl border border-linha bg-white p-6 text-tinta shadow-[0_24px_60px_rgba(15,29,54,.35)] backdrop:bg-[rgba(15,29,54,.55)]" aria-labelledby="titulo-janela-gasto">
    <form method="POST" action="/roteiros/gastos/adicionar" class="flex flex-col gap-3.5">
        {{csrf}}
        <input type="hidden" name="idRoteiro" value="{{id}}">

        <div class="flex items-center justify-between gap-3">
            <h2 id="titulo-janela-gasto" class="text-[19px] font-bold">Adicionar outro gasto</h2>
            <button type="button" class="grid cursor-pointer place-items-center rounded-md p-1.5 text-apagado hover:bg-alerta-suave hover:text-alerta" data-fechar-janela aria-label="Fechar">{{iconeFechar}}</button>
        </div>

        <p class="dica">Registre algo que você comprou ou reservou por fora, como um jantar, uma lembrancinha ou um ingresso, para o valor entrar no total do roteiro.</p>

        <div>
            <label for="gasto-descricao" class="rotulo">O que foi?</label>
            <input id="gasto-descricao" name="descricao" required maxlength="60" placeholder="Ex.: Jantar na Lapa" class="campo">
        </div>

        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="gasto-valor" class="rotulo">Valor total</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-apagado">R$</span>
                    <input id="gasto-valor" name="valor" type="number" min="0.01" step="0.01" required placeholder="0,00" inputmode="decimal" class="campo !pl-[38px]">
                </div>
            </div>
            <div>
                <label for="gasto-data" class="rotulo">Dia</label>
                <select id="gasto-data" name="data" class="campo">{{opcoesDias}}</select>
            </div>
        </div>

        <div>
            <label for="gasto-categoria" class="rotulo">Categoria</label>
            <select id="gasto-categoria" name="categoria" class="campo">{{opcoesCategorias}}</select>
        </div>

        <button type="submit" class="btn-destaque">{{iconeMais}}Adicionar ao roteiro</button>
    </form>
</dialog>
