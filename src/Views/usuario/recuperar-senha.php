<main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
    <a href="/login" class="mb-8">
        <img src="/public/images/logo/logo-mundear.png" alt="Mundear" class="w-52">
    </a>

    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl sm:p-10">
        <h1 class="text-2xl font-bold text-blue-950">Esqueceu a senha?</h1>
        <p class="mt-2 text-slate-500">Informe o e-mail da sua conta e enviaremos um link para você criar uma nova senha.</p>

        <div class="mt-6 empty:hidden">{{feedback}}</div>

        <form method="POST" action="/recuperar-senha" class="mt-6 space-y-5" novalidate>
            {{csrf}}
            <div>
                <label for="email" class="rotulo">E-mail</label>
                <input id="email" name="email" type="email" value="{{valorEmail}}" placeholder="seu@email.com" autocomplete="email" required class="campo h-12">
            </div>

            <button type="submit" class="btn-primario h-12 w-full">Enviar link de recuperação</button>
        </form>

        <a href="/login" class="mt-6 block text-center text-sm font-medium text-blue-700 hover:underline">Voltar para o login</a>
    </div>
</main>
