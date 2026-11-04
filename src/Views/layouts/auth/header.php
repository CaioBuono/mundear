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
            .btn-primario { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-950 px-5 font-semibold text-white transition hover:bg-blue-900; }
            .btn-destaque { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 font-semibold text-white transition hover:bg-orange-600; }
            .btn-secundario { @apply inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl border border-blue-800 bg-white px-5 font-semibold text-blue-800 transition hover:bg-blue-50; }
        }
    </style>
    <title>{{titulo}} · Mundear</title>
</head>
<body class="bg-slate-50 text-slate-900">
