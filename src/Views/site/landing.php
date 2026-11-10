<div class="scroll-smooth bg-white">
    <header class="sticky top-0 z-40 bg-navy">
        <div class="container-app flex h-16 items-center gap-7 md:h-[76px]">
            <img src="/public/img/logo-mundear-claro.png" alt="Mundear" class="h-11 w-auto md:h-[54px]">
            <nav class="ml-auto hidden gap-1.5 md:flex" aria-label="Seções da página">
                <a href="#beneficios" class="rounded-lg px-3 py-2 text-[14.5px] font-bold text-menu transition hover:bg-navy-2 hover:text-white">Benefícios</a>
                <a href="#como-funciona" class="rounded-lg px-3 py-2 text-[14.5px] font-bold text-menu transition hover:bg-navy-2 hover:text-white">Como funciona</a>
                <a href="#depoimentos" class="rounded-lg px-3 py-2 text-[14.5px] font-bold text-menu transition hover:bg-navy-2 hover:text-white">Depoimentos</a>
                <a href="#duvidas" class="rounded-lg px-3 py-2 text-[14.5px] font-bold text-menu transition hover:bg-navy-2 hover:text-white">Dúvidas</a>
            </nav>
            <div class="ml-auto flex flex-none gap-2.5 md:ml-0">
                <a href="/login" class="btn-vidro">Entrar</a>
                <a href="#criar-conta" class="btn-destaque">Criar conta</a>
            </div>
        </div>
    </header>

    <section class="relative overflow-hidden bg-navy text-white">
        <img src="/public/img/capa-viagem.jpg" alt="" class="absolute inset-0 h-full w-full object-cover object-[center_46%]">
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(15,29,54,.9)_0%,rgba(15,29,54,.7)_45%,rgba(15,29,54,.2)_100%)]"></div>
        <div class="container-app relative z-10 grid items-center gap-10 py-12 md:grid-cols-[minmax(0,1.2fr)_minmax(0,.8fr)] md:pb-20 md:pt-[72px]">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[.06em]"><i class="h-[7px] w-[7px] rounded-full bg-laranja-claro"></i>Planejador de viagens 100% gratuito</span>
                <h1 class="mt-[18px] text-[clamp(34px,4.4vw,56px)] font-extrabold leading-[1.06] tracking-[-.02em] !text-white [text-shadow:0_2px_14px_rgba(15,29,54,.5)]">Explore o mundo, colecione <em class="not-italic text-laranja-claro">histórias</em></h1>
                <p class="mb-7 mt-4 max-w-[46ch] text-lg text-[#dbe3f1] [text-shadow:0_2px_10px_rgba(15,29,54,.5)]">Planeje a viagem do seu jeito, em um só lugar. Compare passagens, carona e o custo de ir de carro, encontre hospedagens e guias locais, sem dezenas de abas abertas.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="#criar-conta" class="btn-destaque btn-lg">Começar meu roteiro grátis {{iconeSeta}}</a>
                    <a href="#como-funciona" class="btn-vidro btn-lg">Ver como funciona</a>
                </div>
                <p class="mt-3 text-[13px] text-menu-claro">Sem cartão de crédito · Crie sua conta em menos de 1 minuto</p>
            </div>

            <div class="flex flex-col gap-2.5 rounded-xl bg-white p-5 text-tinta shadow-[0_24px_50px_rgba(0,0,0,.35)]" aria-label="Exemplo de roteiro">
                <div class="flex items-baseline justify-between gap-2.5"><b class="font-display text-[17px]">Férias no Rio</b><span class="dica">2 pessoas</span></div>
                <span class="dica">11/12/2026 – 14/12/2026</span>
                <div class="flex items-center gap-2.5 text-sm"><span class="icone-servico cor-transporte !h-8 !w-8 !rounded-lg">{{iconeOnibus}}</span>Ônibus · Buser<span class="ml-auto font-bold">R$ 489,80</span></div>
                <div class="flex items-center gap-2.5 text-sm"><span class="icone-servico cor-hospedagem !h-8 !w-8 !rounded-lg">{{iconeCama}}</span>Hotel · 3 noites<span class="ml-auto font-bold">R$ 959,70</span></div>
                <div class="flex items-center gap-2.5 text-sm"><span class="icone-servico cor-guia !h-8 !w-8 !rounded-lg">{{iconePessoas}}</span>Guia local<span class="ml-auto font-bold">R$ 150,00</span></div>
                <div class="flex items-baseline justify-between border-t border-linha pt-2.5"><span class="text-[11.5px] font-bold uppercase tracking-[.07em] text-apagado">Valor total</span><b class="font-display text-2xl text-laranja-escuro">R$ 1.599,50</b></div>
                <span class="dica !text-[11.5px]">Exemplo de roteiro com valores ilustrativos</span>
            </div>
        </div>
    </section>

    <section id="beneficios" class="scroll-mt-20 bg-fundo py-[52px] md:py-[72px]">
        <div class="container-app">
            <p class="sobretitulo text-center">Por que usar o Mundear</p>
            <h2 class="mx-auto mb-2.5 mt-2 max-w-[22ch] text-center text-[clamp(26px,3vw,36px)] font-extrabold tracking-[-.01em]">Feito para quem viaja por conta própria</h2>
            <p class="mx-auto mb-9 max-w-[56ch] text-center text-apagado">Você escolhe cada parte da viagem e o Mundear junta tudo num roteiro só, com o custo sempre à vista.</p>
            <div class="grid gap-[18px] md:grid-cols-3">
                <div class="cartao flex flex-col gap-2.5 !p-[26px]"><span class="icone-servico cor-transporte !h-[52px] !w-[52px] !rounded-[14px]">{{iconeAviaoGrande}}</span><h3 class="text-lg font-bold">Autonomia total</h3><p class="text-[14.5px] text-apagado">Você no comando: escolha o transporte, a hospedagem e os passeios que combinam com você.</p></div>
                <div class="cartao flex flex-col gap-2.5 !p-[26px]"><span class="icone-servico cor-hospedagem !h-[52px] !w-[52px] !rounded-[14px]">{{iconeCamaGrande}}</span><h3 class="text-lg font-bold">Tudo em uma tela</h3><p class="text-[14.5px] text-apagado">Avião, ônibus, carona, hotéis em vários sites e guias locais, lado a lado e com preços.</p></div>
                <div class="cartao flex flex-col gap-2.5 !p-[26px]"><span class="icone-servico cor-guia !h-[52px] !w-[52px] !rounded-[14px]">{{iconeCarteiraGrande}}</span><h3 class="text-lg font-bold">Controle de orçamento</h3><p class="text-[14.5px] text-apagado">O valor total é calculado na hora, e você vê quanto ainda cabe no que quer gastar.</p></div>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="scroll-mt-20 py-[52px] md:py-[72px]">
        <div class="container-app">
            <p class="sobretitulo text-center">Demonstração</p>
            <h2 class="mx-auto mb-2.5 mt-2 max-w-[22ch] text-center text-[clamp(26px,3vw,36px)] font-extrabold tracking-[-.01em]">Veja como funciona</h2>
            <p class="mx-auto mb-9 max-w-[56ch] text-center text-apagado">Esta é a tela real do roteiro: cada dia com seus serviços, o total da viagem e o orçamento.</p>
            <div class="mx-auto max-w-[980px] overflow-hidden rounded-[14px] border border-linha shadow-[0_30px_60px_-20px_rgba(19,40,77,.45)]">
                <div class="flex items-center gap-[7px] bg-navy px-3.5 py-2.5"><i class="h-2.5 w-2.5 rounded-full bg-laranja"></i><i class="h-2.5 w-2.5 rounded-full bg-[#f5b041]"></i><i class="h-2.5 w-2.5 rounded-full bg-[#8fa3c6]"></i><span class="ml-2.5 rounded-md bg-white/10 px-3 py-0.5 text-xs text-menu">mundear.com.br/roteiro</span></div>
                <img src="/public/img/demo-roteiro.jpg" alt="Tela do roteiro Férias no Rio no Mundear, com o cronograma por dia e o valor total de R$ 1.599,50" class="block h-auto w-full">
            </div>
            <ol class="mt-9 grid gap-[22px] md:grid-cols-3">
                <li class="flex gap-3.5"><span class="grid h-[38px] w-[38px] flex-none place-items-center rounded-full bg-laranja font-display font-extrabold text-white">1</span><div><b class="block font-display text-base">Escolha destino e datas</b><span class="text-sm text-apagado">Informe de onde sai, para onde vai, quantas pessoas e quanto quer gastar.</span></div></li>
                <li class="flex gap-3.5"><span class="grid h-[38px] w-[38px] flex-none place-items-center rounded-full bg-laranja font-display font-extrabold text-white">2</span><div><b class="block font-display text-base">Monte transporte, hospedagem e passeios</b><span class="text-sm text-apagado">Compare as opções com preços e adicione ao roteiro com um clique.</span></div></li>
                <li class="flex gap-3.5"><span class="grid h-[38px] w-[38px] flex-none place-items-center rounded-full bg-laranja font-display font-extrabold text-white">3</span><div><b class="block font-display text-base">Confira o resumo e compartilhe</b><span class="text-sm text-apagado">O total é calculado na hora e o roteiro vira um link para quem vai junto.</span></div></li>
            </ol>
        </div>
    </section>

    <section id="depoimentos" class="scroll-mt-20 bg-fundo py-[52px] md:py-[72px]">
        <div class="container-app">
            <p class="sobretitulo text-center">Depoimentos</p>
            <h2 class="mx-auto mb-2.5 mt-2 max-w-[22ch] text-center text-[clamp(26px,3vw,36px)] font-extrabold tracking-[-.01em]">Quem já planejou, aprova</h2>
            <div class="mx-auto mt-7 grid max-w-[880px] gap-[18px] md:grid-cols-2">
                <div class="cartao flex flex-col gap-3.5 !p-[26px]">
                    <span class="tracking-[2px] text-[#f5a623]" aria-label="5 estrelas">★★★★★</span>
                    <q class="text-base leading-[1.55]">Sempre me perdia nas planilhas, mas montei meu mochilão em 10 minutos pelo celular!</q>
                    <div class="mt-auto flex items-center gap-2.5"><span class="grid h-[38px] w-[38px] place-items-center rounded-full bg-navy font-display text-sm font-extrabold text-white">L</span><div><b class="block">Lucas</b><span class="text-[13px] text-apagado">23 anos</span></div></div>
                </div>
                <div class="cartao flex flex-col gap-3.5 !p-[26px]">
                    <span class="tracking-[2px] text-[#f5a623]" aria-label="5 estrelas">★★★★★</span>
                    <q class="text-base leading-[1.55]">Consegui organizar a viagem da família e compartilhar o link com o meu marido sem medo de ele apagar as reservas.</q>
                    <div class="mt-auto flex items-center gap-2.5"><span class="grid h-[38px] w-[38px] place-items-center rounded-full bg-navy font-display text-sm font-extrabold text-white">C</span><div><b class="block">Camila</b><span class="text-[13px] text-apagado">34 anos</span></div></div>
                </div>
            </div>
        </div>
    </section>

    <section id="duvidas" class="scroll-mt-20 py-[52px] md:py-[72px]">
        <div class="container-app grid items-start gap-10 md:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)]">
            <div>
                <p class="sobretitulo">FAQ</p>
                <h2 class="mt-2 text-[clamp(26px,3vw,34px)] font-extrabold">Ficou com alguma dúvida?</h2>
                <p class="dica !text-[15px]">As respostas mais procuradas por quem está montando o primeiro roteiro.</p>
            </div>
            <div class="flex flex-col gap-3">
                <details open class="group rounded-xl border border-linha bg-white px-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 py-[18px] font-bold [&::-webkit-details-marker]:hidden">O Mundear cobra taxas?<span class="text-laranja transition group-open:rotate-180">{{iconeBaixo}}</span></summary><p class="mb-[18px] text-apagado">Não. A ferramenta de planejamento é 100% gratuita.</p></details>
                <details class="group rounded-xl border border-linha bg-white px-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 py-[18px] font-bold [&::-webkit-details-marker]:hidden">Como funciona o compartilhamento?<span class="text-laranja transition group-open:rotate-180">{{iconeBaixo}}</span></summary><p class="mb-[18px] text-apagado">Geramos um link de "somente leitura" para você enviar a quem vai viajar junto. Quem abre vê tudo, mas não consegue editar nada.</p></details>
                <details class="group rounded-xl border border-linha bg-white px-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 py-[18px] font-bold [&::-webkit-details-marker]:hidden">Eu compro as passagens pelo Mundear?<span class="text-laranja transition group-open:rotate-180">{{iconeBaixo}}</span></summary><p class="mb-[18px] text-apagado">Não. O Mundear compara os preços e leva você ao site do parceiro para concluir a compra.</p></details>
                <details class="group rounded-xl border border-linha bg-white px-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 py-[18px] font-bold [&::-webkit-details-marker]:hidden">Posso usar pelo celular?<span class="text-laranja transition group-open:rotate-180">{{iconeBaixo}}</span></summary><p class="mb-[18px] text-apagado">Sim. O site funciona no computador e no celular, sem precisar instalar nada.</p></details>
            </div>
        </div>
    </section>

    <section id="criar-conta" class="scroll-mt-16 bg-navy text-[#dbe3f1]">
        <div class="container-app grid items-center gap-12 py-[52px] md:grid-cols-[minmax(0,1fr)_minmax(0,420px)] md:py-[72px]">
            <div>
                <h2 class="text-[clamp(28px,3.4vw,42px)] font-extrabold leading-[1.12] !text-white">Sua próxima grande história começa com um <em class="not-italic text-laranja-claro">bom roteiro.</em></h2>
                <ul class="mt-5 flex flex-col gap-2.5">
                    <li class="flex items-center gap-2.5"><span class="text-laranja-claro">{{iconeCheck}}</span>Grátis para sempre</li>
                    <li class="flex items-center gap-2.5"><span class="text-laranja-claro">{{iconeCheck}}</span>Compartilhe com quem quiser</li>
                    <li class="flex items-center gap-2.5"><span class="text-laranja-claro">{{iconeCheck}}</span>Seus dados protegidos conforme a LGPD</li>
                </ul>
            </div>

            <form method="POST" action="/cadastro" class="cartao flex flex-col gap-3.5 !p-7 text-tinta" novalidate>
                {{csrf}}
                <div><h3 class="text-xl font-bold">Crie sua conta grátis</h3><p class="dica mt-1">Leva menos de um minuto.</p></div>
                <div><label for="cadastro-nome" class="rotulo">Nome completo</label><input id="cadastro-nome" name="nome" value="{{valorNome}}" autocomplete="name" placeholder="Seu nome" class="campo"></div>
                <div><label for="cadastro-email" class="rotulo">E-mail</label><input id="cadastro-email" name="email" type="email" value="{{valorEmail}}" autocomplete="email" placeholder="seu@email.com" class="campo"></div>
                <div><label for="cadastro-senha" class="rotulo">Senha</label><input id="cadastro-senha" name="senha" type="password" autocomplete="new-password" placeholder="Mínimo de 8 caracteres" class="campo"></div>
                <label class="flex items-start gap-2.5 text-sm font-semibold"><input type="checkbox" name="aceiteLgpd" value="1" {{aceiteMarcado}} class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja"> Li e aceito os termos de uso e a política de privacidade.</label>
                {{erroCadastro}}
                <button type="submit" class="btn-destaque btn-lg">Criar minha conta agora {{iconeSeta}}</button>
                <p class="dica text-center">Já tem conta? <a href="/login" class="font-bold text-laranja-escuro">Entrar</a></p>
            </form>
        </div>
    </section>

    <footer class="bg-navy-escuro text-[13px] text-menu-claro">
        <div class="container-app flex flex-wrap items-center justify-between gap-4 py-[22px]">
            <img src="/public/img/logo-mundear-claro.png" alt="Mundear" class="h-[42px] w-auto">
            <span>Explore o <em class="not-italic text-laranja-claro">mundo</em>, colecione histórias.</span>
            <span>© 2026 Mundear</span>
        </div>
    </footer>
</div>
{{rolarParaCadastro}}
