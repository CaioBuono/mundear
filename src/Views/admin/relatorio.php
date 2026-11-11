{{abas}}

<div class="flex flex-col gap-[22px]">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 class="text-[19px] font-bold">Olá, {{nomeAdmin}}</h2>
            <p class="dica mt-0.5">Visão geral da plataforma.</p>
        </div>
        <span class="dica">Credencial {{credencial}}</span>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="cartao flex flex-col gap-1 !px-5 !py-[18px]">
            <span class="text-[13.5px] font-semibold text-apagado">Usuários cadastrados</span>
            <b class="font-display text-[30px] font-extrabold leading-tight text-navy">{{usuariosTotal}}</b>
            <small class="text-[12.5px] text-apagado">{{usuariosInativos}} inativo(s)</small>
        </div>
        <div class="cartao flex flex-col gap-1 !px-5 !py-[18px]">
            <span class="text-[13.5px] font-semibold text-apagado">Roteiros criados</span>
            <b class="font-display text-[30px] font-extrabold leading-tight text-navy">{{roteirosTotal}}</b>
            <small class="text-[12.5px] text-apagado">{{roteirosFinalizados}} concluído(s) · {{linksAtivos}} link(s) ativo(s)</small>
        </div>
        <div class="cartao flex flex-col gap-1 !px-5 !py-[18px]">
            <span class="text-[13.5px] font-semibold text-apagado">Valor total planejado</span>
            <b class="font-display text-[30px] font-extrabold leading-tight text-laranja-escuro">{{valorPlanejado}}</b>
            <small class="text-[12.5px] text-apagado">soma de todos os roteiros</small>
        </div>
        <div class="cartao flex flex-col gap-1 !px-5 !py-[18px]">
            <span class="text-[13.5px] font-semibold text-apagado">Guias ativos</span>
            <b class="font-display text-[30px] font-extrabold leading-tight text-navy">{{guiasAtivos}}</b>
            <small class="text-[12.5px] font-bold text-ok">aparecem na aba Turismo</small>
        </div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2">
        <div class="cartao">
            <h3 class="text-[17px] font-bold">Destinos mais planejados</h3>
            <ul class="mt-4 flex flex-col gap-4">
                {{destinosPopulares}}
            </ul>
        </div>

        <div class="cartao">
            <h3 class="text-[17px] font-bold">Serviços vinculados aos roteiros</h3>
            <div class="mt-3 flex flex-col">
                {{itensPorTipo}}
            </div>
        </div>
    </section>

    <section class="cartao">
        <h3 class="text-[17px] font-bold">Resumo do relatório</h3>
        <p class="dica mt-2 !text-sm leading-relaxed">{{resumo}}</p>
        <p class="dica mt-2">Guias inativos: {{guiasInativos}}.</p>
    </section>
</div>
