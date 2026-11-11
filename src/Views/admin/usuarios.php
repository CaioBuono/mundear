{{abas}}

<div class="flex flex-col gap-4">
    <div>
        <h2 class="text-[19px] font-bold">Usuários</h2>
        <p class="dica mt-0.5">Usuários inativados não conseguem entrar nem acessar ou modificar seus roteiros.</p>
    </div>

    <div class="cartao overflow-x-auto !p-0">
        <table class="w-full min-w-[720px] border-collapse text-left text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-[11.5px] font-bold uppercase tracking-[.06em] text-apagado">
                    <th class="border-b border-linha px-4 py-3">Usuário</th>
                    <th class="border-b border-linha px-4 py-3">Perfil</th>
                    <th class="border-b border-linha px-4 py-3">Cadastro</th>
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
