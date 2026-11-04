<article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700">{{iconeIngresso}}</span>
        <div class="min-w-0">
            <h3 class="font-semibold text-blue-950">{{nome}}</h3>
            <p class="text-sm text-slate-500">{{cidade}}</p>
            <div class="mt-1">{{disponibilidade}}</div>
        </div>
    </div>

    <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
        <li class="flex items-start gap-2">{{iconePin}} {{localizacao}}</li>
        <li class="flex items-center gap-2">{{iconeRelogio}} Duração aprox. {{duracao}} · {{funcionamento}}</li>
    </ul>

    <div class="mt-auto pt-5">
        <div class="flex items-baseline justify-between border-t border-slate-100 pt-4">
            <span class="text-sm text-slate-500">{{detalhePreco}}</span>
            <span class="text-xl font-bold text-orange-500">{{preco}}</span>
        </div>
        <div class="mt-4">
            {{formAdicionar}}
        </div>
    </div>
</article>
