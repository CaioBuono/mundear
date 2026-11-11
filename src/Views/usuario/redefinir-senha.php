<main class="grid min-h-screen place-items-center bg-fundo px-6 py-10">
    <div class="cartao flex w-[min(420px,100%)] flex-col gap-[18px] !p-[34px]">
        <a href="/login" class="self-center"><img src="/public/img/logo-mundear.png" alt="Mundear" class="h-24 w-auto"></a>

        <div>
            <h1 class="text-[22px] font-bold">Crie uma nova senha</h1>
            <p class="dica mt-1">Use pelo menos {{tamanhoSenha}} caracteres.</p>
        </div>

        <div class="empty:hidden">{{feedback}}</div>

        <form method="POST" action="/redefinir-senha" class="flex flex-col gap-[18px]" novalidate>
            {{csrf}}
            <input type="hidden" name="token" value="{{token}}">

            <div>
                <label for="senha" class="rotulo">Nova senha</label>
                <input id="senha" name="senha" type="password" autocomplete="new-password" required class="campo">
            </div>

            <div>
                <label for="confirm" class="rotulo">Confirmar nova senha</label>
                <input id="confirm" name="confirm" type="password" autocomplete="new-password" required class="campo">
            </div>

            <button type="submit" class="btn-primario btn-lg">Salvar nova senha</button>
        </form>
    </div>
</main>
