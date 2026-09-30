
<main class="min-h-[360px] bg-[#0F223C] text-white">

    <section class="mx-auto max-w-[1500px] px-6 py-10 lg:px-10">

        <!-- TÍTULO -->
        <h1 class="text-3xl font-bold tracking-tight md:text-4xl">
            Planeje a sua próxima aventura com o Mundear
        </h1>

        <form action="/buscar" method="GET" class="mt-8">
            <!-- BARRA DE PESQUISA -->
            <div
                class="grid grid-cols-1 items-stretch gap-1 sm:grid-cols-2 xl:grid-cols-[1.2fr_1.2fr_1fr_1fr_1fr_100px]"
            >

                <!-- ORIGEM -->
                <div class="relative min-w-0">

                    <div
                        class="flex h-[76px] flex-col justify-center rounded-lg bg-white px-4 text-slate-900 transition focus-within:ring-2 focus-within:ring-blue-500 xl:rounded-r-none"
                    >

                        <label
                            for="origem"
                            class="text-sm font-semibold text-slate-600"
                        >
                            Origem
                        </label>

                        <input
                            type="text"
                            id="origem"
                            name="origem"
                            placeholder="País ou cidade"
                            autocomplete="off"
                            required
                            class="mt-1 w-full min-w-0 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500"
                        >

                    </div>


                    <!-- INVERTER -->
                    <button
                        type="button"
                        id="inverter"
                        aria-label="Inverter origem e destino"
                        class="absolute top-1/2 right-0 z-20 hidden h-10 w-10 translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-[3px] border-[#0F223C] bg-white text-[#0F223C] transition hover:bg-blue-50 xl:flex cursor-pointer"
                    >

                        <svg class="rotate-90" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" width="1rem" height="1rem"><path d="M.44 8.56a1.5 1.5 0 0 1 0-2.12l4.5-4.5a1.5 1.5 0 0 1 2.12 0l4.5 4.5a1.5 1.5 0 0 1-2.12 2.12L7.5 6.622V19.5a1.5 1.5 0 0 1-3 0V6.621l-1.94 1.94a1.5 1.5 0 0 1-2.12 0zm12 6.88a1.5 1.5 0 0 0 0 2.12l4.5 4.5a1.5 1.5 0 0 0 2.12 0l4.5-4.5a1.5 1.5 0 0 0-2.12-2.12l-1.94 1.939V4.5a1.5 1.5 0 0 0-3 0v12.879l-1.94-1.94a1.5 1.5 0 0 0-2.12 0z"></path></svg>

                    </button>

                </div>


                <!-- DESTINO -->
                <div
                    class="flex h-[76px] min-w-0 flex-col justify-center rounded-lg bg-white px-4 text-slate-900 transition focus-within:ring-2 focus-within:ring-blue-500 xl:rounded-none xl:pl-8"
                >

                    <label
                        for="destino"
                        class="text-sm font-semibold text-slate-600"
                    >
                        Destino
                    </label>

                    <input
                        type="text"
                        id="destino"
                        name="destino"
                        placeholder="País ou cidade"
                        autocomplete="off"
                        required
                        class="mt-1 w-full min-w-0 bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500"
                    >

                </div>


                <!-- DATA DE IDA -->
                <div
                    class="flex h-[76px] min-w-0 flex-col justify-center rounded-lg bg-white px-4 text-slate-900 transition focus-within:ring-2 focus-within:ring-blue-500 xl:rounded-none"
                >

                    <label
                        for="ida"
                        class="text-sm font-semibold text-slate-600"
                    >
                        Data de ida
                    </label>

                    <input
                        type="date"
                        id="ida"
                        name="ida"
                        required
                        class="mt-1 w-full min-w-0 bg-transparent text-sm text-slate-900 outline-none"
                    >

                </div>


                <!-- DATA DE VOLTA -->
                <div
                    id="campo_volta"
                    class="flex h-[76px] min-w-0 flex-col justify-center rounded-lg bg-white px-4 text-slate-900 transition focus-within:ring-2 focus-within:ring-blue-500 xl:rounded-none"
                >

                    <label
                        for="volta"
                        class="text-sm font-semibold text-slate-600"
                    >
                        Data de volta
                    </label>

                    <input
                        type="date"
                        id="volta"
                        name="volta"
                        required
                        class="mt-1 w-full min-w-0 bg-transparent text-sm text-slate-900 outline-none"
                    >

                </div>


                <!-- VIAJANTES -->
                <div
                    class="flex h-[76px] min-w-0 flex-col justify-center rounded-lg bg-white px-4 text-slate-900 transition focus-within:ring-2 focus-within:ring-blue-500 xl:rounded-l-none"
                >

                    <label
                        for="viajantes"
                        class="text-sm font-semibold text-slate-600"
                    >
                        Viajantes e classe de voo
                    </label>

                    <select
                        name="viajantes"
                        id="viajantes"
                        class="mt-1 w-full min-w-0 cursor-pointer bg-transparent text-sm text-slate-900 outline-none"
                    >
                        <option value="1">
                            1 Adulto, Econômica
                        </option>

                        <option value="2">
                            2 Adultos, Econômica
                        </option>

                        <option value="3">
                            3 Adultos, Econômica
                        </option>

                        <option value="4">
                            4 Adultos, Econômica
                        </option>
                    </select>

                </div>


                <!-- BOTÃO BUSCAR -->
                <button
                    type="submit"
                    class="flex h-[76px] items-center justify-center rounded-xl bg-[#3657D9] px-5 text-sm font-semibold text-white transition hover:bg-[#2945B8] sm:col-span-2 xl:col-span-1 xl:ml-2"
                >
                    Buscar
                </button>

            </div>

        </form>

    </section>

</main>