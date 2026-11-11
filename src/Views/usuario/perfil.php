<div class="grid items-start gap-5 lg:grid-cols-[280px_minmax(0,1fr)]">
    <aside class="cartao flex flex-col items-center gap-2.5 !px-[22px] !py-[26px] text-center">
        <span class="grid h-[88px] w-[88px] place-items-center rounded-full bg-laranja font-display text-[28px] font-extrabold text-white">{{iniciais}}</span>
        <h2 class="text-[19px] font-bold">{{nome}}</h2>
        <span class="dica break-all">{{email}}</span>
        <div class="mt-1.5 flex w-full flex-col gap-2 border-t border-linha pt-3.5 text-left text-sm">
            {{infos}}
        </div>
    </aside>

    <div class="flex min-w-0 flex-col gap-4">
        <form method="POST" action="/perfil" class="cartao flex flex-col gap-4" novalidate>
            {{csrf}}
            <h2 class="text-lg font-bold">Dados pessoais</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="nome" class="rotulo">Nome completo</label>
                    <input id="nome" name="nome" value="{{nome}}" autocomplete="name" class="campo">
                </div>
                <div>
                    <label for="email" class="rotulo">E-mail</label>
                    <input id="email" name="email" type="email" value="{{email}}" autocomplete="email" class="campo">
                </div>
                <div>
                    <label for="telefone" class="rotulo">Celular</label>
                    <input id="telefone" name="telefone" type="tel" value="{{telefone}}" placeholder="(18) 99000-0000" autocomplete="tel" class="campo">
                </div>
                <div>
                    <label for="cidade" class="rotulo">Cidade</label>
                    <input id="cidade" name="cidade" value="{{cidade}}" maxlength="80" placeholder="Ex.: Birigui - SP" class="campo">
                </div>
                <div>
                    <label for="dataNascimento" class="rotulo">Data de nascimento</label>
                    <input id="dataNascimento" name="dataNascimento" type="date" value="{{dataNascimento}}" max="{{dataMaxima}}" class="campo">
                </div>
            </div>

            <fieldset>
                <legend class="rotulo">Como você costuma viajar?</legend>
                <div class="flex flex-wrap gap-2">{{estilos}}</div>
                <span class="dica mt-1.5 block">Usamos isso para sugerir destinos e roteiros prontos.</span>
            </fieldset>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="dica">Alterações não salvas são perdidas ao sair da página.</span>
                <button type="submit" class="btn-destaque">Salvar alterações</button>
            </div>
        </form>

        <form method="POST" action="/perfil/comunicacoes" class="cartao flex flex-col gap-3.5" data-salvar-automatico>
            {{csrf}}
            <div>
                <h2 class="text-lg font-bold">Comunicações</h2>
                <p class="dica mt-0.5">Você pode mudar isso quando quiser.</p>
            </div>

            <label class="flex items-start gap-2.5 text-sm font-semibold">
                <input type="checkbox" name="dicas" value="1" class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja" {{marcadoDicas}}>
                <span>E-mails com dicas de viagem e roteiros prontos<small class="block text-[12.5px] font-medium text-apagado">No máximo um por semana.</small></span>
            </label>
            <label class="flex items-start gap-2.5 text-sm font-semibold">
                <input type="checkbox" name="alertasPreco" value="1" class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja" {{marcadoAlertas}}>
                <span>Alertas quando o preço cair<small class="block text-[12.5px] font-medium text-apagado">Para passagens e hospedagens dos seus roteiros.</small></span>
            </label>
            <label class="flex items-start gap-2.5 text-sm font-semibold">
                <input type="checkbox" name="promocoes" value="1" class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja" {{marcadoPromocoes}}>
                <span>Promoções de parceiros<small class="block text-[12.5px] font-medium text-apagado">Ofertas de hotéis, guias e empresas de turismo.</small></span>
            </label>

            <span class="text-xs text-apagado empty:hidden" data-status-salvamento aria-live="polite"></span>
            <noscript><button type="submit" class="btn-secundario self-start">Salvar preferências</button></noscript>
        </form>

        <form method="POST" action="/perfil/senha" class="cartao flex flex-col gap-4" novalidate>
            {{csrf}}
            <div>
                <h2 class="text-lg font-bold">Alterar senha</h2>
                <p class="dica mt-0.5">A nova senha deve ter pelo menos {{tamanhoSenha}} caracteres.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="senhaAtual" class="rotulo">Senha atual</label>
                    <input id="senhaAtual" name="senhaAtual" type="password" autocomplete="current-password" class="campo">
                </div>
                <div>
                    <label for="novaSenha" class="rotulo">Nova senha</label>
                    <input id="novaSenha" name="novaSenha" type="password" placeholder="Mínimo de {{tamanhoSenha}} caracteres" autocomplete="new-password" class="campo">
                </div>
                <div>
                    <label for="confirmarSenha" class="rotulo">Confirmar nova senha</label>
                    <input id="confirmarSenha" name="confirmarSenha" type="password" autocomplete="new-password" class="campo">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primario">Alterar senha</button>
            </div>
        </form>
    </div>
</div>
