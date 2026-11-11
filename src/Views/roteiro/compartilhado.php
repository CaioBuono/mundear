<header class="bg-navy">
    <div class="container-app flex h-20 items-center gap-4">
        <a href="/?site"><img src="/public/img/logo-mundear-claro.png" alt="Mundear" class="h-[58px] w-auto"></a>
        {{voltar}}
    </div>
</header>

<main class="container-app flex flex-col gap-6 pb-14 pt-7">
    <div class="flex items-center gap-2.5 rounded-lg bg-suave px-3.5 py-2.5 text-[13.5px] font-semibold text-navy">
        {{iconeOlho}}
        <span><b>{{autor}}</b> compartilhou este roteiro com você. Você pode ver tudo, mas não pode editar.</span>
    </div>

    <div class="flex flex-col gap-2">
        <h1 class="text-[30px] font-extrabold leading-tight tracking-[-.01em]">{{titulo}}</h1>
        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[13.5px] text-apagado">
            <span class="inline-flex items-center gap-1.5">{{iconePin}}{{trajeto}}</span>
            <span class="inline-flex items-center gap-1.5">{{iconeCalendario}}{{periodo}} · {{dias}}</span>
            <span class="inline-flex items-center gap-1.5">{{iconePessoas}}{{pessoas}}</span>
        </div>
    </div>

    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
        <section class="flex min-w-0 flex-col gap-3.5">
            <h2 class="text-lg font-bold">Cronograma</h2>
            {{cronograma}}
        </section>

        <aside class="flex flex-col gap-3.5 lg:sticky lg:top-6">
            <div class="cartao flex flex-col gap-2.5">
                <span class="dica font-semibold">Custo total da viagem</span>
                <b class="font-display text-[32px] font-extrabold leading-tight text-navy">{{total}}</b>
                <span class="dica">{{porPessoa}} por pessoa</span>
                <div class="flex flex-col">{{totaisPorTipo}}</div>
            </div>

            <div class="flex flex-col gap-3 rounded-xl bg-navy p-[22px] text-[#dbe3f1]">
                <h3 class="text-lg font-bold text-white">Também vai viajar?</h3>
                <span class="text-sm">Monte o seu roteiro no Mundear e compare preços de transporte, hospedagem e passeios em um só lugar.</span>
                <a href="/?site#criar-conta" class="btn-destaque">Criar minha conta grátis</a>
            </div>

            <span class="dica text-center">{{validade}}</span>
        </aside>
    </div>
</main>
