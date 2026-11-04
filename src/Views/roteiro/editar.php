<nav class="mb-4 text-sm text-slate-500">
    <a href="/roteiros" class="hover:text-blue-900">Meus roteiros</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700">{{titulo}}</span>
</nav>

<!-- CABEÇALHO -->
<div class="flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">{{titulo}}</h1>
    {{status}}
</div>
<p class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-slate-600">
    <span class="inline-flex items-center gap-1.5">{{iconePin}} {{destinos}}</span>
    <span class="inline-flex items-center gap-1.5">{{iconeCalendario}} {{periodo}} · {{dias}}</span>
</p>

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
    <div class="min-w-0 space-y-6">
        <!-- ADICIONAR SERVIÇOS -->
        <section class="cartao">
            <h2 class="text-lg font-semibold text-blue-950">Adicionar serviços</h2>
            <p class="text-sm text-slate-500">Pesquise e vincule opções a este roteiro. O valor total é recalculado a cada inclusão ou remoção.</p>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <a href="/transporte?roteiro={{id}}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 px-3 py-4 text-center text-sm font-semibold text-blue-950 transition hover:border-blue-300 hover:bg-blue-50">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-900">{{iconeAviao}}</span>
                    Transporte
                </a>
                <a href="/hospedagem?roteiro={{id}}" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 px-3 py-4 text-center text-sm font-semibold text-blue-950 transition hover:border-orange-300 hover:bg-orange-50">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-600">{{iconeCama}}</span>
                    Hospedagem
                </a>
                <a href="/turismo?roteiro={{id}}#guias" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 px-3 py-4 text-center text-sm font-semibold text-blue-950 transition hover:border-emerald-300 hover:bg-emerald-50">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">{{iconeGuia}}</span>
                    Guias
                </a>
                <a href="/turismo?roteiro={{id}}#passeios" class="flex flex-col items-center gap-2 rounded-xl border border-slate-200 px-3 py-4 text-center text-sm font-semibold text-blue-950 transition hover:border-violet-300 hover:bg-violet-50">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-700">{{iconeIngresso}}</span>
                    Passeios
                </a>
            </div>
        </section>

        <!-- CRONOGRAMA -->
        <section>
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-blue-950">Cronograma</h2>
                    <p class="text-sm text-slate-500">{{quantidadeItens}} no roteiro</p>
                </div>
            </div>

            <div class="space-y-4">
                {{cronograma}}
            </div>
        </section>

        <!-- EDITAR DADOS DA VIAGEM -->
        <details class="cartao group">
            <summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-blue-950">
                Editar dados da viagem
                <span class="text-slate-400 transition group-open:rotate-180">▾</span>
            </summary>

            <form method="POST" action="/roteiros/editar" class="mt-5 space-y-5">
                {{csrf}}
                <input type="hidden" name="idRoteiro" value="{{id}}">

                <div>
                    <label for="titulo" class="rotulo">Nome da viagem</label>
                    <input id="titulo" name="titulo" type="text" value="{{valorTitulo}}" maxlength="80" required class="campo">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="dataInicio" class="rotulo">Data de ida</label>
                        <input id="dataInicio" name="dataInicio" type="date" value="{{valorDataInicio}}" required class="campo">
                    </div>
                    <div>
                        <label for="dataFim" class="rotulo">Data de volta</label>
                        <input id="dataFim" name="dataFim" type="date" value="{{valorDataFim}}" required class="campo">
                    </div>
                </div>

                <fieldset>
                    <legend class="rotulo">Destinos</legend>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {{opcoesDestinos}}
                    </div>
                </fieldset>

                <p class="text-xs text-slate-500">Os serviços já adicionados precisam continuar dentro do novo período e os guias precisam atuar em um dos destinos.</p>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primario">Salvar dados</button>
                </div>
            </form>
        </details>
    </div>

    <!-- RESUMO / CARRINHO -->
    <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
        <section class="cartao">
            <p class="text-sm text-slate-500">Valor total estimado</p>
            <p class="mt-1 text-3xl font-bold text-blue-950" data-total-roteiro>{{total}}</p>

            <dl class="mt-4 divide-y divide-slate-100 border-t border-slate-100 text-sm">
                {{totaisPorTipo}}
            </dl>
        </section>

        <section class="cartao scroll-mt-24" id="compartilhar">
            {{painelCompartilhamento}}
        </section>

        <form method="POST" action="/roteiros/excluir" data-confirmar="Excluir este roteiro definitivamente? Todos os itens e o link de compartilhamento serão apagados.">
            {{csrf}}
            <input type="hidden" name="idRoteiro" value="{{id}}">
            <button type="submit" class="btn-perigo w-full">Excluir roteiro</button>
        </form>
    </aside>
</div>
