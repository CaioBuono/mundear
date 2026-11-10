<article class="cartao border-l-4 border-l-(--mc) !p-0" style="{{cores}}">
    <div class="grid items-center gap-5 py-3.5 pl-[18px] pr-[18px] md:grid-cols-[64px_minmax(0,1fr)_auto]">
        <span class="grid h-16 w-16 place-items-center rounded-full bg-(--ms) font-display text-xl font-extrabold text-(--mc)">{{foto}}</span>

        <div class="min-w-0">
            <h3 class="text-[16.5px] font-bold">{{nome}}</h3>
            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[13px] text-apagado">{{especialidade}} · <span class="text-[13px] font-bold text-laranja-escuro">★ {{avaliacao}}</span></div>
            <div class="mt-2 flex flex-wrap gap-1.5">{{idiomas}}</div>
        </div>

        <div class="flex flex-col items-start gap-2 md:items-end">
            <span class="font-display text-[21px] font-extrabold leading-tight text-(--mc)">{{valor}}</span>
            <span class="text-xs text-apagado md:text-right">por {{unidade}} · até 4 pessoas</span>
            <div class="flex flex-wrap gap-2 md:justify-end">
                <button type="button" class="btn-secundario" data-abrir-janela="{{idPerfil}}">Ver perfil</button>
                {{botao}}
            </div>
        </div>
    </div>

    <dialog id="{{idPerfil}}" class="m-auto w-[min(480px,calc(100vw-32px))] rounded-xl border border-linha bg-white p-[22px] text-tinta shadow-[0_24px_60px_rgba(15,29,54,.35)] backdrop:bg-[rgba(15,29,54,.55)]">
        <div class="flex flex-col gap-3.5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-16 w-16 flex-none place-items-center rounded-full bg-(--ms) font-display text-xl font-extrabold text-(--mc)">{{foto}}</span>
                    <div><h2 class="text-xl font-bold">{{nome}}</h2><span class="text-[13px] font-bold text-laranja-escuro">★ {{avaliacao}} · guia credenciado</span></div>
                </div>
                <button type="button" class="grid place-items-center rounded-md p-1.5 text-apagado hover:bg-alerta-suave hover:text-alerta" data-fechar-janela aria-label="Fechar">{{iconeFechar}}</button>
            </div>
            <p class="dica">{{apresentacao}}</p>
            <dl class="grid grid-cols-[auto_minmax(0,1fr)] gap-x-4 gap-y-2 text-sm">
                <dt class="text-apagado">Especialidade</dt><dd class="font-semibold">{{especialidade}}</dd>
                <dt class="text-apagado">Regiões</dt><dd class="font-semibold">{{regioes}}</dd>
                <dt class="text-apagado">Idiomas</dt><dd class="font-semibold">{{idiomasTexto}}</dd>
                <dt class="text-apagado">Contato</dt><dd class="font-semibold">{{contato}}</dd>
                <dt class="text-apagado">Valor</dt><dd class="font-semibold">{{valor}} por {{unidade}}</dd>
            </dl>
            {{formPerfil}}
        </div>
    </dialog>
</article>
