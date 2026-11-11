<main class="grid min-h-screen place-items-center bg-fundo px-6 py-10">
    <div class="cartao flex w-[min(420px,100%)] flex-col gap-[18px] !p-[34px]">
        <a href="/login" class="self-center"><img src="/public/img/logo-mundear.png" alt="Mundear" class="h-24 w-auto"></a>

        <div>
            <h1 class="text-[22px] font-bold">Recuperar senha</h1>
            <p class="dica mt-1">Informe o e-mail da sua conta. Vamos enviar um link para você criar uma nova senha.</p>
        </div>

        <div class="empty:hidden">{{feedback}}</div>

        <form method="POST" action="/recuperar-senha" class="flex flex-col gap-[18px]" novalidate>
            {{csrf}}
            <div>
                <label for="email" class="rotulo">E-mail</label>
                <input id="email" name="email" type="email" value="{{valorEmail}}" placeholder="seu@email.com" autocomplete="email" required class="campo">
            </div>

            <button type="submit" class="btn-primario btn-lg">Enviar link</button>
        </form>

        <a href="/login" class="self-start text-[13.5px] font-bold text-laranja-escuro hover:underline">Voltar para o login</a>
    </div>
</main>
