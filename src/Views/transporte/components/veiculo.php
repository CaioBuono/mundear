<form method="POST" action="/roteiros/itens/veiculo" class="cartao grid overflow-hidden border-l-4 border-l-(--mc) !p-0 md:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]" style="--mc:#7046c9;--ms:#efe9fb" data-estimativa>
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{idRoteiro}}">
    <input type="hidden" name="voltar" value="{{voltar}}">

    <div class="grid grid-cols-1 gap-4 border-linha p-[22px] sm:grid-cols-2 md:border-r">
        <div><label for="tipoVeiculo" class="rotulo">Tipo de veículo</label><select id="tipoVeiculo" name="tipoVeiculo" class="campo">{{opcoesVeiculo}}</select></div>
        <div><label for="combustivel" class="rotulo">Combustível</label><select id="combustivel" name="combustivel" class="campo">{{opcoesCombustivel}}</select></div>
        <div><label for="distancia" class="rotulo">Distância (ida, em km)</label><input id="distancia" name="distancia" type="number" value="958" min="1" inputmode="numeric" class="campo"></div>
        <div><label for="consumo" class="rotulo">Consumo do veículo (km/l)</label><input id="consumo" name="consumo" type="number" value="12" min="1" step="0.5" inputmode="decimal" class="campo"></div>
        <div><label for="precoCombustivel" class="rotulo">Preço do combustível (R$/l)</label><input id="precoCombustivel" name="precoCombustivel" type="number" value="6.29" min="0" step="0.01" inputmode="decimal" class="campo"></div>
        <div><label for="pedagios" class="rotulo">Pedágios na ida (R$)</label><input id="pedagios" name="pedagios" type="number" value="152.40" min="0" step="0.1" inputmode="decimal" class="campo"></div>
        <label class="flex items-start gap-2.5 text-sm font-semibold sm:col-span-2"><input type="checkbox" name="idaVolta" value="1" checked class="mt-0.5 h-[17px] w-[17px] flex-none accent-laranja"> Calcular ida e volta</label>
    </div>

    <div class="flex flex-col gap-2.5 bg-(--ms) p-[22px]">
        <span class="text-[11.5px] font-bold uppercase tracking-[.07em] text-apagado" data-saida="titulo">Custo estimado</span>
        <div class="flex flex-col gap-2 text-sm">
            <div class="flex justify-between gap-3"><span class="text-apagado">Distância total</span><span data-saida="km"></span></div>
            <div class="flex justify-between gap-3"><span class="text-apagado">Combustível</span><span data-saida="litros"></span></div>
            <div class="flex justify-between gap-3"><span class="text-apagado">Gasto com combustível</span><span data-saida="combustivel"></span></div>
            <div class="flex justify-between gap-3"><span class="text-apagado">Pedágios</span><span data-saida="pedagios"></span></div>
        </div>
        <div class="mt-1 flex items-baseline justify-between gap-3 border-t border-linha pt-3"><span class="text-[11.5px] font-bold uppercase tracking-[.07em] text-apagado">Total estimado</span><b class="font-display text-[26px] font-extrabold text-(--mc)" data-saida="total"></b></div>
        <span class="dica">Valor estimado. Pode variar com o trânsito e o preço do combustível.</span>
        <button type="submit" class="btn-destaque">{{iconeMais}}Adicionar ao roteiro</button>
    </div>
</form>
