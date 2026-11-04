<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @layer components {
            .campo { @apply h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-900 focus:ring-4 focus:ring-blue-100; }
            .rotulo { @apply mb-1.5 block text-sm font-semibold text-blue-950; }
            .cartao { @apply rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6; }
            .btn-primario { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-950 px-5 font-semibold text-white transition hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-50; }
            .btn-destaque { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 font-semibold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50; }
            .btn-secundario { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border border-blue-800 bg-white px-5 font-semibold text-blue-800 transition hover:bg-blue-50; }
            .btn-perigo { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-5 font-semibold text-red-600 transition hover:bg-red-50; }
        }
    </style>
    <title>{{titulo}} · Mundear</title>
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:h-18">
        <!-- LOGO -->
        <a href="/home" class="flex shrink-0 items-center">
            <img
                src="/public/images/logo/logo-mundear.png"
                alt="Mundear"
                class="w-36 lg:w-44"
            >
        </a>

        <!-- MENU (DESKTOP) -->
        <nav class="hidden items-center gap-6 lg:flex xl:gap-8" aria-label="Menu principal">
            <a href="/home" class="{{navInicio}}">
                <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M341.8 72.6C329.5 61.2 310.5 61.2 298.3 72.6L74.3 280.6C64.7 289.6 61.5 303.5 66.3 315.7C71.1 327.9 82.8 336 96 336L112 336L112 512C112 547.3 140.7 576 176 576L464 576C499.3 576 528 547.3 528 512L528 336L544 336C557.2 336 569 327.9 573.8 315.7C578.6 303.5 575.4 289.5 565.8 280.6L341.8 72.6zM304 384L336 384C362.5 384 384 405.5 384 432L384 528L256 528L256 432C256 405.5 277.5 384 304 384z"/></svg>
                Início
            </a>

            <a href="/transporte" class="{{navTransporte}}">
                <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M404 207.9L204.7 104.2C196.7 100.1 187.4 99.4 179 102.5L137.9 117.5C127.6 121.2 124.1 133.9 130.8 142.5L232.3 270.4L132.1 306.8L72 270.2C65.8 266.4 58.2 265.7 51.3 268.1L35 274.1C25.6 277.5 21.6 288.6 26.7 297.2L80.3 389C95.9 415.7 128.4 427.4 157.4 416.8L170.3 412.1L170.3 412.1L568.7 267.1C597.8 256.5 612.7 224.4 602.2 195.3C591.7 166.2 559.5 151.3 530.4 161.8L404 207.9zM64.2 512C46.5 512 32.2 526.3 32.2 544C32.2 561.7 46.5 576 64.2 576L576.2 576C593.9 576 608.2 561.7 608.2 544C608.2 526.3 593.9 512 576.2 512L64.2 512z"/></svg>
                Transporte
            </a>

            <a href="/hospedagem" class="{{navHospedagem}}">
                <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Pro v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2026 Fonticons, Inc.--><path d="M64 96C81.7 96 96 110.3 96 128L96 352L320 352L320 224C320 206.3 334.3 192 352 192L512 192C565 192 608 235 608 288L608 512C608 529.7 593.7 544 576 544C558.3 544 544 529.7 544 512L544 448L96 448L96 512C96 529.7 81.7 544 64 544C46.3 544 32 529.7 32 512L32 128C32 110.3 46.3 96 64 96zM144 256C144 220.7 172.7 192 208 192C243.3 192 272 220.7 272 256C272 291.3 243.3 320 208 320C172.7 320 144 291.3 144 256z"/></svg>
                Hospedagem
            </a>

            <a href="/turismo" class="{{navTurismo}}">
                <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M576 112C576 100.9 570.3 90.6 560.8 84.8C551.3 79 539.6 78.4 529.7 83.4L413.5 141.5L234.1 81.6C226 78.9 217.3 79.5 209.7 83.3L81.7 147.3C70.8 152.8 64 163.9 64 176L64 528C64 539.1 69.7 549.4 79.2 555.2C88.7 561 100.4 561.6 110.3 556.6L226.4 498.5L405.8 558.3C413.9 561 422.6 560.4 430.2 556.6L558.2 492.6C569 487.2 575.9 476.1 575.9 464L575.9 112zM256 440.9L256 156.4L384 199.1L384 483.6L256 440.9z"/></svg>
                Turismo
            </a>

            <a href="/roteiros" class="{{navRoteiros}}">
                <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Pro v7.3.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2026 Fonticons, Inc.--><path d="M480 576L192 576C139 576 96 533 96 480L96 160C96 107 139 64 192 64L496 64C522.5 64 544 85.5 544 112L544 400C544 420.9 530.6 438.7 512 445.3L512 512C529.7 512 544 526.3 544 544C544 561.7 529.7 576 512 576L480 576zM192 448C174.3 448 160 462.3 160 480C160 497.7 174.3 512 192 512L448 512L448 448L192 448zM224 216C224 229.3 234.7 240 248 240L424 240C437.3 240 448 229.3 448 216C448 202.7 437.3 192 424 192L248 192C234.7 192 224 202.7 224 216zM248 288C234.7 288 224 298.7 224 312C224 325.3 234.7 336 248 336L424 336C437.3 336 448 325.3 448 312C448 298.7 437.3 288 424 288L248 288z"/></svg>
                Meus roteiros
            </a>

            <a href="/admin" class="{{navAdmin}}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/></svg>
                Admin
            </a>
        </nav>

        {{perfilUsuario}}
    </div>
</header>

<main class="mx-auto w-full max-w-7xl flex-1 px-4 pb-28 pt-6 sm:px-6 lg:pb-12 lg:pt-8">
    <div id="areaFeedback" class="empty:hidden mb-6">{{flash}}</div>
