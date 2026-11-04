<main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
    <a href="/login" class="mb-8">
        <img src="/public/images/logo/logo-mundear.png" alt="Mundear" class="w-52">
    </a>

    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl sm:p-10">
        <h1 class="text-2xl font-bold text-blue-950">Crie uma nova senha</h1>
        <p class="mt-2 text-slate-500">Use pelo menos {{tamanhoSenha}} caracteres.</p>

        <div class="mt-6 empty:hidden">{{feedback}}</div>

        <form method="POST" action="/redefinir-senha" class="mt-6 space-y-5" novalidate>
            {{csrf}}
            <input type="hidden" name="token" value="{{token}}">

            <div>
                <label for="senha" class="rotulo">Nova senha</label>
                <input id="senha" name="senha" type="password" autocomplete="new-password" required class="campo h-12">
            </div>

            <div>
                <label for="confirm" class="rotulo">Confirmar nova senha</label>
                <input id="confirm" name="confirm" type="password" autocomplete="new-password" required class="campo h-12">
            </div>

            <button type="submit" class="btn-primario h-12 w-full">Salvar nova senha</button>
        </form>
    </div>
</main>
