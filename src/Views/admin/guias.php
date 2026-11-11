{{abas}}

<div class="flex flex-col gap-4">
    <form method="GET" action="/admin/guias" class="flex flex-wrap items-end gap-3">
        <h2 class="mr-auto self-center text-[19px] font-bold">Guias turísticos</h2>
        <div class="w-full sm:w-56">
            <label for="cidade" class="rotulo">Cidade de atuação</label>
            <select id="cidade" name="cidade" class="campo">{{opcoesCidade}}</select>
        </div>
        <div class="w-full sm:w-48">
            <label for="status" class="rotulo">Situação</label>
            <select id="status" name="status" class="campo">{{opcoesStatus}}</select>
        </div>
        <button type="submit" class="btn-primario">Filtrar</button>
    </form>

    <p class="dica">{{quantidade}}</p>

    <div class="cartao overflow-x-auto !p-0">
        <table class="w-full min-w-[760px] border-collapse text-left text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-[11.5px] font-bold uppercase tracking-[.06em] text-apagado">
                    <th class="border-b border-linha px-4 py-3">Guia</th>
                    <th class="border-b border-linha px-4 py-3">Cidade de atuação</th>
                    <th class="border-b border-linha px-4 py-3">Contato</th>
                    <th class="border-b border-linha px-4 py-3">Valor</th>
                    <th class="border-b border-linha px-4 py-3">Situação</th>
                    <th class="border-b border-linha px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="[&_tr:last-child_td]:border-b-0">
                {{linhas}}
            </tbody>
        </table>
    </div>
</div>
