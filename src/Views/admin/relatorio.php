{{abas}}

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Olá, {{nomeAdmin}}</h1>
        <p class="mt-1 text-slate-500">Visão geral da plataforma.</p>
    </div>
    <p class="text-sm text-slate-500">Credencial {{credencial}}</p>
</div>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="cartao">
        <p class="text-sm text-slate-500">Usuários</p>
        <p class="mt-1 text-3xl font-bold text-blue-950">{{usuariosTotal}}</p>
        <p class="mt-1 text-xs text-slate-500">{{usuariosInativos}} inativo(s)</p>
    </div>
    <div class="cartao">
        <p class="text-sm text-slate-500">Roteiros</p>
        <p class="mt-1 text-3xl font-bold text-blue-950">{{roteirosTotal}}</p>
        <p class="mt-1 text-xs text-slate-500">{{roteirosFinalizados}} finalizado(s) · {{linksAtivos}} link(s) ativo(s)</p>
    </div>
    <div class="cartao">
        <p class="text-sm text-slate-500">Valor total planejado</p>
        <p class="mt-1 text-3xl font-bold text-orange-500">{{valorPlanejado}}</p>
        <p class="mt-1 text-xs text-slate-500">soma de todos os roteiros</p>
    </div>
    <div class="cartao">
        <p class="text-sm text-slate-500">Guias ativos</p>
        <p class="mt-1 text-3xl font-bold text-blue-950">{{guiasAtivos}}</p>
        <p class="mt-1 text-xs text-slate-500">{{guiasInativos}} inativo(s)</p>
    </div>
</section>

<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="cartao">
        <h2 class="font-semibold text-blue-950">Destinos mais planejados</h2>
        <ul class="mt-4 space-y-4">
            {{destinosPopulares}}
        </ul>
    </div>

    <div class="cartao">
        <h2 class="font-semibold text-blue-950">Serviços vinculados aos roteiros</h2>
        <dl class="mt-3 divide-y divide-slate-100 text-sm">
            {{itensPorTipo}}
        </dl>
    </div>
</section>

<section class="cartao mt-6">
    <h2 class="font-semibold text-blue-950">Resumo do relatório</h2>
    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{resumo}}</p>
</section>
