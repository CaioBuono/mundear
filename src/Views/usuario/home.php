<div class="flex flex-col gap-[22px]">
    <section class="relative grid items-center gap-7 overflow-hidden rounded-[18px] bg-navy p-7 text-[#dbe3f1] md:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] md:p-10">
        <img src="/public/img/capa-viagem.jpg" alt="" class="absolute inset-0 h-full w-full object-cover object-[center_46%]">
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(19,40,77,.94)_0%,rgba(19,40,77,.78)_40%,rgba(19,40,77,.3)_75%,rgba(19,40,77,.15)_100%)]"></div>

        <div class="relative z-10">
            <span class="text-xs font-extrabold uppercase tracking-[.08em] text-laranja-claro">Olá, {{primeiroNome}}!</span>
            <h1 class="mt-2 text-[clamp(28px,3.4vw,40px)] font-extrabold tracking-[-.02em] !text-white [text-shadow:0_2px_12px_rgba(15,29,54,.5)]">Para onde vamos <em class="not-italic text-laranja-claro">agora</em>?</h1>
            <p class="mb-[22px] mt-2.5 max-w-[52ch] text-[15.5px] [text-shadow:0_2px_12px_rgba(15,29,54,.5)]">Monte seu roteiro em um só lugar: transporte, hospedagem, guias locais e passeios, com o custo total sempre atualizado.</p>
            <a href="/roteiros" class="btn-vidro btn-lg">Ver meus roteiros</a>
        </div>

        <div class="relative z-10">
            {{proximaViagem}}
        </div>
    </section>

    <form method="POST" action="/roteiros/novo" class="cartao flex flex-col gap-4" novalidate>
        {{csrf}}
        <input type="hidden" name="origemFormulario" value="inicio">

        <div>
            <h2 class="text-lg font-bold">Planeje sua próxima viagem</h2>
            <p class="dica mt-0.5">Preencha os dados e o Mundear cria o roteiro para você adicionar transporte, hospedagem e passeios.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="col-span-2 md:col-span-4">
                <label for="titulo" class="rotulo">Título do roteiro</label>
                <input id="titulo" name="titulo" value="{{valorTitulo}}" maxlength="80" placeholder="Ex.: Férias em Salvador" class="campo">
            </div>
            <div class="col-span-2">
                <label for="origem" class="rotulo">Origem</label>
                <input id="origem" name="origem" value="{{valorOrigem}}" maxlength="80" placeholder="Ex.: Birigui - SP" class="campo">
            </div>
            <div class="col-span-2">
                <label for="destino" class="rotulo">Destino</label>
                <input id="destino" name="destino" value="{{valorDestino}}" list="listaDestinos" placeholder="Ex.: Salvador - BA" autocomplete="off" class="campo">
                <datalist id="listaDestinos">{{datalistDestinos}}</datalist>
            </div>
            <div>
                <label for="dataInicio" class="rotulo">Ida</label>
                <input id="dataInicio" name="dataInicio" type="date" value="{{valorDataInicio}}" min="{{dataMinima}}" class="campo">
            </div>
            <div>
                <label for="dataFim" class="rotulo">Volta</label>
                <input id="dataFim" name="dataFim" type="date" value="{{valorDataFim}}" min="{{dataMinima}}" class="campo">
            </div>
            <div>
                <label for="quantidadePessoas" class="rotulo">Pessoas</label>
                <div class="flex h-11 overflow-hidden rounded-lg border border-linha bg-white focus-within:border-navy focus-within:ring-3 focus-within:ring-suave">
                    <button type="button" data-passo="quantidadePessoas:-1" class="w-[38px] flex-none bg-suave text-lg font-extrabold text-navy hover:bg-linha" aria-label="Menos uma pessoa">−</button>
                    <input id="quantidadePessoas" name="quantidadePessoas" type="number" min="1" max="20" value="{{valorPessoas}}" class="w-full min-w-0 border-0 text-center font-bold outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                    <button type="button" data-passo="quantidadePessoas:1" class="w-[38px] flex-none bg-suave text-lg font-extrabold text-navy hover:bg-linha" aria-label="Mais uma pessoa">+</button>
                </div>
            </div>
            <div>
                <label for="orcamento" class="rotulo">Quanto quer gastar?</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-apagado">R$</span>
                    <input id="orcamento" name="orcamento" type="number" min="0" step="50" inputmode="decimal" value="{{valorOrcamento}}" class="campo !pl-[38px]">
                </div>
            </div>
        </div>

        {{feedbackPlano}}

        <div class="flex flex-wrap items-center justify-between gap-3">
            <span class="dica">Título, destino e datas são obrigatórios. O valor que você quer gastar é opcional e vira o orçamento do roteiro.</span>
            <button type="submit" class="btn-destaque btn-lg">Criar roteiro {{iconeSeta}}</button>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        <a href="/roteiros?status=planejamento" class="flex items-center justify-between gap-3.5 rounded-xl border border-[#f6cfb4] bg-[linear-gradient(135deg,#fff3ea_0%,#ffe6d5_100%)] px-5 py-[18px] shadow-cartao transition hover:-translate-y-px">
            <span class="flex min-w-0 items-center gap-3">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-laranja text-white">{{iconeLista}}</span>
                <span><b class="block font-display text-[15.5px]">Em planejamento</b><small class="block text-[12.5px] font-medium text-[#9a5a33]">Você começou a montar e ainda não terminou</small></span>
            </span>
            <b class="font-display text-[34px] font-extrabold leading-none text-laranja-escuro">{{totalPlanejamento}}</b>
        </a>
        <a href="/roteiros?status=prontos" class="flex items-center justify-between gap-3.5 rounded-xl border border-[#c3d3ef] bg-[linear-gradient(135deg,#eef3fc_0%,#dde7f8_100%)] px-5 py-[18px] shadow-cartao transition hover:-translate-y-px">
            <span class="flex min-w-0 items-center gap-3">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-[#2b4c8c] text-white">{{iconeCheck}}</span>
                <span><b class="block font-display text-[15.5px]">Prontos para viajar</b><small class="block text-[12.5px] font-medium text-[#4b5f86]">Tudo planejado, só falta chegar o dia</small></span>
            </span>
            <b class="font-display text-[34px] font-extrabold leading-none text-navy-3">{{totalProntos}}</b>
        </a>
        <a href="/roteiros?status=realizadas" class="flex items-center justify-between gap-3.5 rounded-xl border border-[#b9e2cb] bg-[linear-gradient(135deg,#ebf8f1_0%,#d8f0e3_100%)] px-5 py-[18px] shadow-cartao transition hover:-translate-y-px">
            <span class="flex min-w-0 items-center gap-3">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-ok text-white">{{iconeMapa}}</span>
                <span><b class="block font-display text-[15.5px]">Viagens realizadas</b><small class="block text-[12.5px] font-medium text-[#3f7259]">Viagens que você já fez</small></span>
            </span>
            <b class="font-display text-[34px] font-extrabold leading-none text-[#1a6f49]">{{totalRealizadas}}</b>
        </a>
    </div>
</div>
