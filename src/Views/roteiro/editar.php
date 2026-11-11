<div class="flex flex-col gap-2">
    <nav class="flex flex-wrap items-center gap-1.5 text-[13px] font-semibold text-apagado" aria-label="Você está em">
        <a href="/roteiros" class="hover:text-navy">Meus roteiros</a>
        <span>/</span>
        <span class="text-tinta">{{titulo}}</span>
    </nav>

    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-[30px] font-extrabold leading-tight tracking-[-.01em]">{{titulo}}</h1>
        {{status}}
        <button type="button" class="btn-secundario ml-auto" data-abrir-janela="janela-novo-roteiro">{{iconeMais}}Novo roteiro</button>
    </div>

    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[13.5px] text-apagado">
        <span class="inline-flex items-center gap-1.5">{{iconePin}}{{trajeto}}</span>
        <span class="inline-flex items-center gap-1.5">{{iconeCalendario}}{{periodo}} · {{dias}}</span>
        <span class="inline-flex items-center gap-1.5">{{iconePessoas}}{{pessoas}}</span>
    </div>
</div>

<div class="mt-6 grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
    <div class="flex min-w-0 flex-col gap-[18px]">
        <section class="cartao">
            <h2 class="text-lg font-bold">Adicionar serviços</h2>
            <p class="dica mt-0.5">Pesquise e adicione opções a este roteiro, ou registre um gasto que você fez por fora. O valor total é recalculado a cada inclusão ou remoção.</p>

            <div class="mt-3.5 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                <a href="{{urlTransporte}}" class="flex flex-col items-center gap-2.5 rounded-xl border border-linha bg-white px-2 py-[18px] text-center font-bold transition hover:border-navy hover:bg-[#f8fafc]">
                    <span class="icone-servico cor-transporte">{{iconeAviao}}</span>
                    Transporte
                </a>
                <a href="{{urlHospedagem}}" class="flex flex-col items-center gap-2.5 rounded-xl border border-linha bg-white px-2 py-[18px] text-center font-bold transition hover:border-navy hover:bg-[#f8fafc]">
                    <span class="icone-servico cor-hospedagem">{{iconeCama}}</span>
                    Hospedagem
                </a>
                <a href="{{urlGuias}}" class="flex flex-col items-center gap-2.5 rounded-xl border border-linha bg-white px-2 py-[18px] text-center font-bold transition hover:border-navy hover:bg-[#f8fafc]">
                    <span class="icone-servico cor-guia">{{iconeGuia}}</span>
                    Guias
                </a>
                <a href="{{urlPasseios}}" class="flex flex-col items-center gap-2.5 rounded-xl border border-linha bg-white px-2 py-[18px] text-center font-bold transition hover:border-navy hover:bg-[#f8fafc]">
                    <span class="icone-servico cor-passeio">{{iconeIngresso}}</span>
                    Passeios
                </a>
                <button type="button" data-abrir-janela="janela-gasto" class="flex cursor-pointer flex-col items-center gap-2.5 rounded-xl border border-linha bg-white px-2 py-[18px] text-center font-bold transition hover:border-navy hover:bg-[#f8fafc]">
                    <span class="icone-servico cor-extra">{{iconeCarteira}}</span>
                    Outro gasto
                </button>
            </div>
        </section>

        <div>
            <h2 class="text-lg font-bold">Cronograma</h2>
            <p class="dica mt-0.5">{{quantidadeItens}}</p>
        </div>

        <div class="flex flex-col gap-3.5">
            {{cronograma}}
        </div>

        <details class="cartao group !p-0">
            <summary class="flex cursor-pointer list-none items-center justify-between px-[22px] py-[18px] font-display text-base font-bold [&::-webkit-details-marker]:hidden">
                Editar dados da viagem
                {{iconeBaixo}}
            </summary>

            <form method="POST" action="/roteiros/editar" class="grid grid-cols-2 gap-4 px-[22px] pb-[22px] md:grid-cols-6">
                {{csrf}}
                <input type="hidden" name="idRoteiro" value="{{id}}">

                <div class="col-span-2 md:col-span-6">
                    <label for="titulo" class="rotulo">Título do roteiro</label>
                    <input id="titulo" name="titulo" value="{{valorTitulo}}" maxlength="80" class="campo">
                </div>
                <div class="col-span-2 md:col-span-3">
                    <label for="origem" class="rotulo">Origem</label>
                    <input id="origem" name="origem" value="{{valorOrigem}}" maxlength="80" placeholder="Ex.: Birigui - SP" class="campo">
                </div>
                <div class="col-span-2 md:col-span-3">
                    <label for="destino" class="rotulo">Destino</label>
                    <input id="destino" name="destino" value="{{valorDestino}}" list="listaDestinos" placeholder="Ex.: Salvador - BA" autocomplete="off" class="campo">
                    <datalist id="listaDestinos">{{datalistDestinos}}</datalist>
                </div>
                <div class="md:col-span-2">
                    <label for="dataInicio" class="rotulo">Ida</label>
                    <input id="dataInicio" name="dataInicio" type="date" value="{{valorDataInicio}}" class="campo">
                </div>
                <div class="md:col-span-2">
                    <label for="dataFim" class="rotulo">Volta</label>
                    <input id="dataFim" name="dataFim" type="date" value="{{valorDataFim}}" class="campo">
                </div>
                <div class="col-span-2 md:col-span-2">
                    <label for="quantidadePessoas" class="rotulo">Pessoas</label>
                    <div class="flex h-11 overflow-hidden rounded-lg border border-linha bg-white focus-within:border-navy focus-within:ring-3 focus-within:ring-suave">
                        <button type="button" data-passo="quantidadePessoas:-1" class="w-[38px] flex-none cursor-pointer bg-suave text-lg font-extrabold text-navy hover:bg-linha" aria-label="Menos uma pessoa">−</button>
                        <input id="quantidadePessoas" name="quantidadePessoas" type="number" min="1" max="20" value="{{valorPessoas}}" class="w-full min-w-0 border-0 text-center font-bold outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                        <button type="button" data-passo="quantidadePessoas:1" class="w-[38px] flex-none cursor-pointer bg-suave text-lg font-extrabold text-navy hover:bg-linha" aria-label="Mais uma pessoa">+</button>
                    </div>
                </div>
                <div class="col-span-2 md:col-span-6">
                    <label for="orcamento" class="rotulo">Quanto quer gastar? (orçamento total)</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-apagado">R$</span>
                        <input id="orcamento" name="orcamento" type="number" min="0" step="50" inputmode="decimal" value="{{valorOrcamento}}" class="campo !pl-[38px]">
                    </div>
                </div>

                <div class="col-span-2 flex flex-wrap items-center justify-between gap-3 md:col-span-6">
                    <span class="dica">Os serviços já adicionados precisam continuar dentro das novas datas.</span>
                    <button type="submit" class="btn-primario">Salvar dados</button>
                </div>
            </form>
        </details>
    </div>

    <aside class="flex flex-col gap-3.5 lg:sticky lg:top-[100px]">
        <section class="cartao flex flex-col gap-2.5">
            <span class="dica font-semibold">Valor total estimado</span>
            <b class="font-display text-[32px] font-extrabold leading-tight text-navy" data-total-roteiro>{{total}}</b>
            <span class="dica">{{porPessoa}} por pessoa</span>
            <div class="flex flex-col">{{totaisPorTipo}}</div>
            {{orcamento}}
        </section>

        {{painelCompartilhamento}}

        <div>
            <button type="button" class="btn-perigo w-full aria-expanded:hidden" data-alternar="confirmar-exclusao" aria-expanded="false">Excluir roteiro</button>

            <div id="confirmar-exclusao" class="flex flex-col gap-2.5 rounded-xl border border-[#f1b9a4] bg-alerta-suave p-3.5" hidden>
                <b class="text-sm text-alerta">Excluir "{{titulo}}"?</b>
                <span class="dica">O roteiro e todos os serviços dele serão apagados. Essa ação não pode ser desfeita.</span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="btn-secundario !h-9 !px-3 !text-[13px]" data-alternar="confirmar-exclusao">Cancelar</button>
                    <form method="POST" action="/roteiros/excluir">
                        {{csrf}}
                        <input type="hidden" name="idRoteiro" value="{{id}}">
                        <button type="submit" class="btn-perigo !h-9 !border-alerta !bg-alerta !px-3 !text-[13px] !text-white">Sim, excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>

{{janelaGasto}}
{{janelaNovo}}
