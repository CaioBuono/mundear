<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
{{tema}}
    <title>{{titulo}} · Mundear</title>
</head>
<body class="flex min-h-screen flex-col">
<header class="sticky top-0 z-40 bg-navy text-[#dbe3f1]">
    <div class="container-app flex flex-wrap items-center gap-x-6 gap-y-1.5 py-2 xl:h-20 xl:flex-nowrap xl:py-0">
        <a href="/home" class="flex-none">
            <img src="/public/img/logo-mundear-claro.png" alt="Mundear" class="h-[46px] w-auto xl:h-[58px]">
        </a>

        <nav class="order-3 -mx-1 flex w-full gap-1 overflow-x-auto pb-1 xl:order-none xl:mx-0 xl:w-auto xl:gap-1.5 xl:overflow-visible xl:pb-0" aria-label="Menu principal">
            <a href="/home" class="{{navInicio}}">{{iconeInicio}}<span>Início</span></a>
            <a href="/transporte" class="{{navTransporte}}">{{iconeTransporte}}<span>Transporte</span></a>
            <a href="/hospedagem" class="{{navHospedagem}}">{{iconeHospedagem}}<span>Hospedagem</span></a>
            <a href="/turismo" class="{{navTurismo}}">{{iconeTurismo}}<span>Turismo</span></a>
            <a href="/roteiros" class="{{navRoteiros}}">{{iconeRoteiros}}<span>Meus roteiros</span></a>
            <a href="/admin" class="{{navAdmin}}">{{iconeAdmin}}<span>Admin</span></a>
        </nav>

        {{perfilUsuario}}
    </div>
</header>
{{cabecalhoPagina}}
<main class="container-app flex-1 pb-14 pt-7">
    <div id="areaFeedback" class="mb-6 empty:hidden">{{flash}}</div>
