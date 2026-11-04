{{abas}}

<div class="mb-6">
    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Usuários</h1>
    <p class="mt-1 text-slate-500">Usuários inativados não conseguem entrar nem acessar ou modificar seus roteiros.</p>
</div>

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full min-w-[720px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-5 py-3 font-semibold">Usuário</th>
                <th class="px-5 py-3 font-semibold">Perfil</th>
                <th class="px-5 py-3 font-semibold">Cadastro</th>
                <th class="px-5 py-3 font-semibold">Situação</th>
                <th class="px-5 py-3 text-right font-semibold">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            {{linhas}}
        </tbody>
    </table>
</div>
