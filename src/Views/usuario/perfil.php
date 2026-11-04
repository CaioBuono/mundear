<div class="mb-6">
    <h1 class="text-2xl font-bold text-blue-950 sm:text-3xl">Meu perfil</h1>
    <p class="mt-1 text-slate-500">Atualize seus dados de acesso.</p>
</div>

<div class="grid gap-6 lg:grid-cols-[320px_1fr]">
    <!-- RESUMO -->
    <aside class="cartao h-fit">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-xl font-bold text-blue-950">
                {{iniciais}}
            </div>
            <div class="min-w-0">
                <p class="truncate text-lg font-semibold text-blue-950">{{nome}}</p>
                <p class="truncate text-sm text-slate-500">{{email}}</p>
            </div>
        </div>

        <dl class="mt-6 divide-y divide-slate-100 text-sm">
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-slate-500">Perfil</dt>
                <dd class="font-medium text-blue-950">{{perfil}}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-slate-500">Membro desde</dt>
                <dd class="font-medium text-blue-950">{{dataCadastro}}</dd>
            </div>
            <div class="flex justify-between gap-4 py-3">
                <dt class="text-slate-500">Roteiros</dt>
                <dd class="font-medium text-blue-950">{{totalRoteiros}}</dd>
            </div>
            {{infoAdmin}}
        </dl>
    </aside>

    <div class="space-y-6">
        <!-- DADOS -->
        <form method="POST" action="/perfil" class="cartao space-y-5">
            {{csrf}}
            <div>
                <h2 class="text-lg font-semibold text-blue-950">Dados pessoais</h2>
                <p class="text-sm text-slate-500">Esse e-mail também é usado para entrar no Mundear.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="nome" class="rotulo">Nome</label>
                    <input id="nome" name="nome" type="text" value="{{nome}}" autocomplete="name" required minlength="3" class="campo">
                </div>
                <div>
                    <label for="email" class="rotulo">E-mail</label>
                    <input id="email" name="email" type="email" value="{{email}}" autocomplete="email" required class="campo">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primario">Salvar alterações</button>
            </div>
        </form>

        <!-- SENHA -->
        <form method="POST" action="/perfil/senha" class="cartao space-y-5">
            {{csrf}}
            <div>
                <h2 class="text-lg font-semibold text-blue-950">Alterar senha</h2>
                <p class="text-sm text-slate-500">A nova senha deve ter pelo menos {{tamanhoSenha}} caracteres.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="senhaAtual" class="rotulo">Senha atual</label>
                    <input id="senhaAtual" name="senhaAtual" type="password" autocomplete="current-password" required class="campo">
                </div>
                <div>
                    <label for="novaSenha" class="rotulo">Nova senha</label>
                    <input id="novaSenha" name="novaSenha" type="password" autocomplete="new-password" required minlength="{{tamanhoSenha}}" class="campo">
                </div>
                <div>
                    <label for="confirmarSenha" class="rotulo">Confirmar nova senha</label>
                    <input id="confirmarSenha" name="confirmarSenha" type="password" autocomplete="new-password" required class="campo">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-secundario">Alterar senha</button>
            </div>
        </form>
    </div>
</div>
