<form method="POST" action="/roteiros/itens/adicionar" class="flex w-full flex-col gap-2 sm:flex-row sm:items-end">
    {{csrf}}
    {{camposOcultos}}
    <input type="hidden" name="voltar" value="{{voltar}}">

    <label class="flex-1">
        <span class="mb-1 block text-xs font-semibold text-slate-500">Adicionar ao roteiro</span>
        <select name="idRoteiro" class="campo h-10 text-sm" required>
            {{opcoesRoteiros}}
        </select>
    </label>

    <button type="submit" class="btn-destaque h-10 shrink-0 px-4 text-sm">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Adicionar
    </button>
</form>
