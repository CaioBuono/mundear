<dialog id="janela-link" class="m-auto w-[min(460px,calc(100vw-32px))] rounded-xl border border-linha bg-white p-6 text-tinta shadow-[0_24px_60px_rgba(15,29,54,.35)] backdrop:bg-[rgba(15,29,54,.55)]" aria-labelledby="titulo-janela-link" {{abrirAoCarregar}}>
    <div class="flex flex-col gap-3.5">
        <div class="flex items-center justify-between gap-3">
            <h2 id="titulo-janela-link" class="text-[19px] font-bold">Compartilhar roteiro</h2>
            <button type="button" class="grid cursor-pointer place-items-center rounded-md p-1.5 text-apagado hover:bg-alerta-suave hover:text-alerta" data-fechar-janela aria-label="Fechar">{{iconeFechar}}</button>
        </div>

        <p class="dica">Quem abrir o link vê o roteiro completo, mas não consegue editar nada.</p>

        <div class="flex gap-2">
            <input id="linkCompartilhamento" type="text" value="{{urlCompleta}}" readonly class="min-w-0 flex-1 rounded-lg border border-linha bg-fundo px-3 py-2.5 font-semibold outline-none" aria-label="Link de compartilhamento" onclick="this.select()">
            <button type="button" class="btn-destaque" data-copiar="#linkCompartilhamento" data-texto-copiado="Copiado!" title="Copiar link">Copiar</button>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-[#e6edfa] px-2.5 py-0.5 text-xs font-bold text-navy-3">Somente leitura</span>
            <span class="rounded-full bg-[#e6edfa] px-2.5 py-0.5 text-xs font-bold text-navy-3">{{expiracao}}</span>
        </div>

        <a href="{{urlRelativa}}" target="_blank" rel="noopener" class="btn-primario">{{iconeOlho}}Ver como o acompanhante vê</a>

        <form method="POST" action="/roteiros/revogar-link" class="flex flex-col" data-confirmar="Desativar o link? Quem tinha o link não vai mais conseguir ver o roteiro.">
            {{csrf}}
            <input type="hidden" name="idRoteiro" value="{{id}}">
            <button type="submit" class="btn-secundario">Desativar link</button>
        </form>
    </div>
</dialog>
