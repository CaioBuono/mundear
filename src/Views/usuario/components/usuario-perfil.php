<details class="relative" data-menu-perfil>
    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-full p-1 pr-2 transition hover:bg-slate-100">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-950">
            {{icone}}
        </div>

        <span class="hidden max-w-40 truncate font-medium text-blue-950 sm:block">
            {{nomeUsuario}}
        </span>

        <span class="text-slate-400">
            ▾
        </span>
    </summary>

    <div class="absolute right-0 z-50 mt-3 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <a href="/perfil" class="block px-5 py-3 text-sm hover:bg-slate-50">
            Meu perfil
        </a>

        <a href="/roteiros" class="block px-5 py-3 text-sm hover:bg-slate-50">
            Meus roteiros
        </a>

        {{linkAdmin}}

        <hr class="border-slate-200">

        <a href="/logout" class="block px-5 py-3 text-sm text-red-600 hover:bg-red-50">
            Sair
        </a>
    </div>
</details>
