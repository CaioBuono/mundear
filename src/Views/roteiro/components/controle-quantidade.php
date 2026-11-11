<form method="POST" action="/roteiros/itens/quantidade" class="flex items-center gap-1.5" data-form-quantidade>
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{idRoteiro}}">
    <input type="hidden" name="idItem" value="{{idItem}}">

    <label for="quantidade-{{idItem}}" class="text-xs text-apagado">Pessoas</label>
    <input
        id="quantidade-{{idItem}}"
        type="number"
        name="quantidade"
        min="1"
        max="{{maximo}}"
        value="{{quantidade}}"
        class="h-8 w-14 rounded-md border border-linha bg-white px-1.5 text-center text-sm font-bold outline-none focus:border-navy focus:ring-3 focus:ring-suave"
        data-quantidade="{{idItem}}"
        data-valor-unitario="{{valorUnitario}}"
    >
</form>
