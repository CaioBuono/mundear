{{abas}}

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Guias turísticos</h1>
        <p class="mt-1 text-slate-500">Cadastre, altere, inative ou exclua os guias exibidos para os viajantes.</p>
    </div>
    <a href="/admin/guias/novo" class="btn-destaque">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Novo guia
    </a>
</div>

<form method="GET" action="/admin/guias" class="cartao mb-6 grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
    <div>
        <label for="cidade" class="rotulo">Cidade de atuação</label>
        <select id="cidade" name="cidade" class="campo">{{opcoesCidade}}</select>
    </div>
    <div>
        <label for="status" class="rotulo">Situação</label>
        <select id="status" name="status" class="campo">{{opcoesStatus}}</select>
    </div>
    <button type="submit" class="btn-primario">Filtrar</button>
</form>

<p class="mb-3 text-sm text-slate-500">{{quantidade}}</p>

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full min-w-[760px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-5 py-3 font-semibold">Guia</th>
                <th class="px-5 py-3 font-semibold">Cidade</th>
                <th class="px-5 py-3 font-semibold">Telefone</th>
                <th class="px-5 py-3 font-semibold">Diária</th>
                <th class="px-5 py-3 font-semibold">Situação</th>
                <th class="px-5 py-3 text-right font-semibold">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            {{linhas}}
        </tbody>
    </table>
</div>
