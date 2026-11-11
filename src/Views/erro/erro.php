<main class="grid min-h-screen place-items-center bg-fundo px-6 py-10 text-center">
    <div class="cartao flex w-[min(420px,100%)] flex-col items-center gap-3 !p-[34px]">
        <img src="/public/img/logo-mundear.png" alt="Mundear" class="mb-2 h-20 w-auto">
        <p class="font-display text-5xl font-extrabold text-laranja">{{status}}</p>
        <h1 class="text-[22px] font-bold">{{tituloErro}}</h1>
        <p class="dica">{{mensagem}}</p>
        <a href="{{linkVoltar}}" class="btn-primario btn-lg mt-3 w-full">{{textoVoltar}}</a>
    </div>
</main>
