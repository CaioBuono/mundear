<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
        <a href="/login"><img src="/public/images/logo/logo-mundear.png" alt="Mundear" class="w-36"></a>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-900 ring-1 ring-blue-200">
            {{iconeOlho}} Somente leitura
        </span>
    </div>
</header>

<main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
    <p class="text-sm font-semibold uppercase tracking-wide text-orange-500">Roteiro compartilhado por {{autor}}</p>
    <h1 class="mt-1 text-2xl font-bold text-blue-950 sm:text-3xl">{{titulo}}</h1>
    <p class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-slate-600">
        <span class="inline-flex items-center gap-1.5">{{iconePin}} {{destinos}}</span>
        <span class="inline-flex items-center gap-1.5">{{iconeCalendario}} {{periodo}} · {{dias}}</span>
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <section class="min-w-0 space-y-4">
            {{cronograma}}
        </section>

        <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
            <div class="cartao">
                <p class="text-sm text-slate-500">Valor total estimado</p>
                <p class="mt-1 text-3xl font-bold text-blue-950">{{total}}</p>
                <dl class="mt-4 divide-y divide-slate-100 border-t border-slate-100 text-sm">
                    {{totaisPorTipo}}
                </dl>
            </div>

            <div class="rounded-2xl bg-blue-950 p-5 text-white">
                <p class="font-semibold">Planeje a sua também</p>
                <p class="mt-1 text-sm text-white/80">Monte roteiros com transporte, hospedagem e guias em um só lugar.</p>
                <a href="/login" class="btn-destaque mt-4 w-full">Conhecer o Mundear</a>
            </div>

            <p class="text-xs text-slate-500">{{validade}} · atualizado em {{atualizacao}}. Os valores são estimativas e podem variar.</p>
        </aside>
    </div>
</main>
