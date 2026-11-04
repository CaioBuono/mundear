<!-- DESTAQUE -->
<section class="relative overflow-hidden rounded-3xl bg-blue-950 text-white">
    <img
        src="/public/images/auth/background-login.png"
        alt=""
        class="absolute inset-0 h-full w-full object-cover opacity-40"
    >
    <div class="absolute inset-0 bg-gradient-to-r from-blue-950 via-blue-950/80 to-blue-950/20"></div>

    <div class="relative grid gap-8 p-6 sm:p-10 lg:grid-cols-[1fr_380px] lg:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-orange-300">Olá, {{primeiroNome}}!</p>
            <h1 class="mt-2 text-3xl font-bold leading-tight sm:text-4xl">
                Para onde vamos <span class="text-orange-400">agora</span>?
            </h1>
            <p class="mt-3 max-w-xl text-white/80">
                Monte seu roteiro em um só lugar: transporte, hospedagem, guias locais e passeios — com o custo total sempre atualizado.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="/roteiros/novo" class="btn-destaque">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Criar novo roteiro
                </a>
                <a href="/roteiros" class="inline-flex h-11 items-center justify-center rounded-xl border border-white/40 px-5 font-semibold text-white transition hover:bg-white/10">
                    Ver meus roteiros
                </a>
            </div>
        </div>

        <div>
            {{proximaViagem}}
        </div>
    </div>
</section>

<!-- INDICADORES -->
<section class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="cartao">
        <p class="text-sm text-slate-500">Roteiros criados</p>
        <p class="mt-1 text-3xl font-bold text-blue-950">{{totalRoteiros}}</p>
    </div>
    <div class="cartao">
        <p class="text-sm text-slate-500">Finalizados</p>
        <p class="mt-1 text-3xl font-bold text-blue-950">{{totalFinalizados}}</p>
    </div>
    <div class="cartao">
        <p class="text-sm text-slate-500">Valor total planejado</p>
        <p class="mt-1 text-3xl font-bold text-orange-500">{{valorPlanejado}}</p>
    </div>
</section>

<!-- ATALHOS DE BUSCA -->
<section class="mt-10">
    <h2 class="text-xl font-bold text-blue-950">Comece pela busca</h2>
    <p class="mt-1 text-slate-500">Encontre as opções e adicione direto ao seu roteiro.</p>

    <div class="mt-5 grid gap-4 md:grid-cols-3">
        <a href="/transporte" class="group cartao flex items-start gap-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-900">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 3.5a1.5 1.5 0 0 1 3 0V9l7 4v2l-7-2v5l2 1.5V21l-3.5-1L8.5 21v-1.5l2-1.5v-5l-7 2v-2l7-4z"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-blue-950 group-hover:text-blue-800">Voos e ônibus</span>
                <span class="mt-1 block text-sm text-slate-500">Compare horários e preços por data e destino.</span>
            </span>
        </a>

        <a href="/hospedagem" class="group cartao flex items-start gap-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18V6M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/><circle cx="7" cy="11" r="2"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-blue-950 group-hover:text-blue-800">Hospedagens</span>
                <span class="mt-1 block text-sm text-slate-500">Hotéis, pousadas e hostels por cidade e período.</span>
            </span>
        </a>

        <a href="/turismo" class="group cartao flex items-start gap-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-blue-950 group-hover:text-blue-800">Guias e passeios</span>
                <span class="mt-1 block text-sm text-slate-500">Contatos de guias locais e atrações do destino.</span>
            </span>
        </a>
    </div>
</section>

<!-- ROTEIROS RECENTES -->
<section class="mt-10">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-blue-950">Roteiros recentes</h2>
            <p class="mt-1 text-slate-500">Continue de onde parou.</p>
        </div>
        {{linkVerTodos}}
    </div>

    <div class="mt-5">
        {{roteirosRecentes}}
    </div>
</section>
