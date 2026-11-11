    <link rel="icon" type="image/png" href="/public/img/logo-mundear.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap">
    <style type="text/tailwindcss">
        @theme {
            --color-navy: #13284d;
            --color-navy-2: #1c3866;
            --color-navy-3: #24427a;
            --color-navy-escuro: #0d1d3a;
            --color-tinta: #0f1d36;
            --color-apagado: #5a6a84;
            --color-fundo: #f2f4f8;
            --color-linha: #dde3ec;
            --color-suave: #e9eef6;
            --color-menu: #c5d0e4;
            --color-menu-claro: #a9b8d3;
            --color-laranja: #f2681c;
            --color-laranja-escuro: #c64f0e;
            --color-laranja-suave: #fdeee4;
            --color-laranja-claro: #ff9a5a;
            --color-ok: #1f8a5b;
            --color-ok-suave: #e3f4ec;
            --color-alerta: #b4471f;
            --color-alerta-suave: #fbe9e1;
            --font-sans: "Manrope", "Segoe UI", system-ui, sans-serif;
            --font-display: "Plus Jakarta Sans", "Segoe UI", system-ui, sans-serif;
            --shadow-cartao: 0 1px 2px rgba(19,40,77,.06), 0 4px 14px rgba(19,40,77,.06);
        }

        @layer base {
            body { @apply bg-fundo font-sans text-[15px] leading-normal text-tinta antialiased; }
            h1, h2, h3 { @apply font-display text-tinta; text-wrap: balance; }
            :focus-visible { @apply outline-2 outline-offset-2 outline-laranja; }
        }

        @layer components {
            .container-app { @apply mx-auto w-full max-w-[1180px] px-4 sm:px-6; }
            .cartao { @apply rounded-xl border border-linha bg-white p-[22px] shadow-cartao; }
            .rotulo { @apply mb-1.5 block text-[13px] font-bold text-tinta; }
            .dica { @apply text-[13px] text-apagado; }
            .sobretitulo { @apply text-xs font-extrabold uppercase tracking-[.08em] text-laranja-escuro; }
            .campo { @apply h-11 w-full min-w-0 rounded-lg border border-linha bg-white px-3 text-tinta outline-none transition placeholder:text-apagado/70 focus:border-navy focus:ring-3 focus:ring-suave; }
            .btn-destaque { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-transparent px-4 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50 bg-laranja text-white hover:bg-laranja-escuro; }
            .btn-primario { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-transparent px-4 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50 bg-navy text-white hover:bg-navy-2; }
            .btn-secundario { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg border px-4 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50 border-linha bg-white text-navy hover:border-navy; }
            .btn-perigo { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg border px-4 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50 border-linha bg-white text-alerta hover:border-alerta; }
            .btn-vidro { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg border px-4 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50 border-white/45 bg-transparent text-white hover:border-white hover:bg-white/10; }
            .btn-lg { @apply h-12 px-5 text-[15px]; }
            .icone-servico { @apply grid h-11 w-11 flex-none place-items-center rounded-[10px]; }
            .cor-transporte { @apply bg-[#e6edfa] text-navy-3; }
            .cor-hospedagem { @apply bg-laranja-suave text-laranja-escuro; }
            .cor-guia { @apply bg-ok-suave text-[#1f7a52]; }
            .cor-passeio { @apply bg-[#efe9fb] text-[#6a3fc2]; }
            .cor-extra { @apply bg-[#fdeaf2] text-[#b4235f]; }
            .erro-campos { @apply flex items-start gap-2 rounded-lg bg-alerta-suave px-3 py-2.5 text-[13.5px] font-semibold text-alerta; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
