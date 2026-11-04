<article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-lg font-bold text-emerald-700">
            {{iniciais}}
        </div>
        <div class="min-w-0">
            <h3 class="font-semibold text-blue-950">{{nome}}</h3>
            <p class="text-sm text-emerald-700">{{especialidade}}</p>
            <div class="mt-1">{{disponibilidade}}</div>
        </div>
    </div>

    <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
        <li class="flex items-center gap-2">{{iconePin}} {{cidade}}</li>
        <li class="flex items-center gap-2">{{iconeTelefone}} <a href="tel:{{telefoneLink}}" class="hover:text-blue-800 hover:underline">{{telefone}}</a></li>
        <li class="flex items-center gap-2">{{iconeCalendario}} {{folga}}</li>
    </ul>

    <div class="mt-auto pt-5">
        <div class="flex items-baseline justify-between border-t border-slate-100 pt-4">
            <span class="text-sm text-slate-500">Diária</span>
            <span class="text-xl font-bold text-orange-500">{{valorDiaria}}</span>
        </div>
        <div class="mt-4">
            {{formAdicionar}}
        </div>
    </div>
</article>
