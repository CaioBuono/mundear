<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
        <!-- EMPRESA -->
        <div class="flex items-center gap-3 lg:w-56">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{corIcone}}">{{icone}}</span>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{tipo}}</p>
                <p class="truncate font-semibold text-blue-950">{{empresa}}</p>
                <p class="text-xs text-slate-400">Cód. {{codigo}}</p>
            </div>
        </div>

        <!-- TRAJETO -->
        <div class="flex flex-1 items-center gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-950">{{horaPartida}}</p>
                <p class="max-w-28 truncate text-sm text-slate-500">{{origem}}</p>
            </div>

            <div class="flex flex-1 flex-col items-center">
                <p class="text-xs text-slate-500">{{duracao}}</p>
                <div class="my-1 h-px w-full bg-slate-300"></div>
                <p class="text-xs text-slate-500">{{assentos}}</p>
            </div>

            <div class="text-center">
                <p class="text-2xl font-bold text-blue-950">{{horaChegada}}{{diasDepois}}</p>
                <p class="max-w-28 truncate text-sm text-slate-500">{{destino}}</p>
            </div>
        </div>

        <!-- PREÇO -->
        <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 lg:w-80 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
            <div class="flex items-baseline justify-between gap-2 lg:block">
                <p class="text-2xl font-bold text-orange-500">{{preco}}</p>
                <p class="text-xs text-slate-500">{{total}}</p>
            </div>
            {{formAdicionar}}
        </div>
    </div>
</article>
