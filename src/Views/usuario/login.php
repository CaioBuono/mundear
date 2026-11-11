<main class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]" data-modo="{{modo}}" id="paginaLogin">
    <section class="relative hidden flex-col justify-end overflow-hidden bg-navy px-14 py-14 text-white lg:flex">
        <img src="/public/img/capa-viagem.jpg" alt="" class="absolute inset-0 h-full w-full object-cover object-[center_82%]">
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(15,29,54,.72)_0%,rgba(15,29,54,.42)_45%,rgba(15,29,54,.08)_80%,rgba(15,29,54,0)_100%),linear-gradient(0deg,rgba(15,29,54,.55)_0%,rgba(15,29,54,0)_45%)]"></div>

        <div class="relative [text-shadow:0_2px_12px_rgba(15,29,54,.55)]">
            <h1 class="max-w-[12ch] text-[clamp(34px,4vw,50px)] font-extrabold leading-[1.08] tracking-[-.02em] text-white">Explore o mundo, colecione <em class="not-italic text-laranja-claro">histórias</em></h1>
            <p class="mb-[34px] mt-4 max-w-[40ch] text-[17px] text-[#dbe3f1]">Planeje, organize e compartilhe seus roteiros de viagem em um só lugar, com o custo total sempre à vista.</p>

            <ul class="flex flex-col gap-[18px]">
                <li class="flex items-center gap-3.5">
                    <span class="grid h-[46px] w-[46px] flex-none place-items-center rounded-[10px] bg-white/15 backdrop-blur-sm">{{iconeComparar}}</span>
                    <div><b class="block font-display text-base">Compare</b><span class="text-[13.5px] text-menu">passagens, carona, hotéis em vários sites e até o custo de ir de carro</span></div>
                </li>
                <li class="flex items-center gap-3.5">
                    <span class="grid h-[46px] w-[46px] flex-none place-items-center rounded-[10px] bg-white/15 backdrop-blur-sm">{{iconeOrganizar}}</span>
                    <div><b class="block font-display text-base">Organize</b><span class="text-[13.5px] text-menu">transporte, hospedagem e passeios em um roteiro dia a dia</span></div>
                </li>
                <li class="flex items-center gap-3.5">
                    <span class="grid h-[46px] w-[46px] flex-none place-items-center rounded-[10px] bg-white/15 backdrop-blur-sm">{{iconeCompartilhar}}</span>
                    <div><b class="block font-display text-base">Compartilhe</b><span class="text-[13.5px] text-menu">o roteiro com quem vai junto, por um link somente leitura</span></div>
                </li>
            </ul>
        </div>
    </section>

    <section class="grid min-h-screen place-items-center bg-fundo px-6 py-10">
        <div class="cartao flex w-[min(420px,100%)] flex-col gap-[18px] !p-[34px]">
            <a href="/?site" class="self-center"><img src="/public/img/logo-mundear.png" alt="Mundear" class="h-24 w-auto"></a>

            <div class="flex gap-1 rounded-lg bg-suave p-1" role="tablist">
                <button type="button" role="tab" data-aba-auth="login" class="flex-1 cursor-pointer rounded-md p-[9px] font-bold text-apagado aria-selected:bg-white aria-selected:text-navy aria-selected:shadow-cartao">Entrar</button>
                <button type="button" role="tab" data-aba-auth="cadastro" class="flex-1 cursor-pointer rounded-md p-[9px] font-bold text-apagado aria-selected:bg-white aria-selected:text-navy aria-selected:shadow-cartao">Criar conta</button>
            </div>

            <div>
                <h2 class="text-[22px] font-bold" id="tituloForm">Bem-vindo(a) de volta</h2>
                <p class="dica mt-1" id="subtituloForm">Entre para continuar planejando suas viagens.</p>
            </div>

            {{feedback}}

            <form action="/login" class="flex flex-col gap-[18px]" method="POST" novalidate>
                {{csrf}}

                <div id="boxNomeUsuario" class="hidden">
                    <label for="nome" class="rotulo">Nome completo</label>
                    <input id="nome" name="nome" type="text" autocomplete="name" value="{{valorNome}}" required disabled class="campo">
                </div>

                <div>
                    <label for="email" class="rotulo">E-mail</label>
                    <input id="email" name="email" type="email" placeholder="seu@email.com" autocomplete="email" value="{{valorEmail}}" required class="campo">
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label for="senha" class="rotulo">Senha</label>
                        <a href="/recuperar-senha" class="mb-1.5 text-[13.5px] font-bold text-laranja-escuro hover:underline" id="linkEsqueci">Esqueci minha senha</a>
                    </div>
                    <input id="senha" name="senha" type="password" autocomplete="current-password" required class="campo">
                </div>

                <div id="boxConfirmarSenha" class="hidden">
                    <label for="confirm" class="rotulo">Confirmar senha</label>
                    <input id="confirm" name="confirm" type="password" placeholder="Repita a senha" autocomplete="new-password" required disabled class="campo">
                </div>

                <label for="lembrar" class="flex items-start gap-2.5 text-sm font-semibold" id="boxOpcoesLogin">
                    <input type="checkbox" name="lembrar" id="lembrar" value="1" class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja">
                    Lembrar de mim
                </label>

                <button type="submit" id="btnAcessar" class="btn-primario btn-lg">Entrar</button>
            </form>

            <a href="/?site" class="self-center text-[13.5px] font-bold text-apagado hover:underline">← Voltar para a página inicial</a>
        </div>
    </section>
    <script src="/public/js/usuario/cadastro.js"></script>
</main>
