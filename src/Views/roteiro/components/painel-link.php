<div class="flex items-center justify-between gap-3">
    <h2 class="text-lg font-semibold text-blue-950">Link de compartilhamento</h2>
    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Ativo</span>
</div>
<p class="mt-1 text-sm text-slate-500">Qualquer pessoa com este link pode ver o roteiro em modo somente leitura.</p>

<div class="mt-4 flex gap-2">
    <input id="linkCompartilhamento" type="text" value="{{urlCompleta}}" readonly class="campo min-w-0 text-sm" aria-label="Link de compartilhamento" onclick="this.select()">
    <button type="button" class="btn-secundario shrink-0 px-3" data-copiar="#linkCompartilhamento" title="Copiar link" aria-label="Copiar link">
        {{iconeCopiar}}
    </button>
</div>
<p class="mt-2 text-xs text-slate-500">{{expiracao}}</p>

<div class="mt-4 grid grid-cols-2 gap-2">
    <a href="{{urlRelativa}}" target="_blank" rel="noopener" class="btn-secundario h-10 text-sm">
        {{iconeOlho}}
        Visualizar
    </a>

    <form method="POST" action="/roteiros/revogar-link" data-confirmar="Revogar o link? Quem tiver o endereço não conseguirá mais ver o roteiro.">
        {{csrf}}
        <input type="hidden" name="idRoteiro" value="{{id}}">
        <button type="submit" class="btn-perigo h-10 w-full text-sm">Revogar</button>
    </form>
</div>
