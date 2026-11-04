<form method="POST" action="/roteiros/itens/quantidade" class="flex items-center gap-2" data-form-quantidade>
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{idRoteiro}}">
    <input type="hidden" name="idItem" value="{{idItem}}">

    <label for="quantidade-{{idItem}}" class="text-xs text-slate-500">Pessoas</label>
    <input
        id="quantidade-{{idItem}}"
        type="number"
        name="quantidade"
        min="1"
        max="{{maximo}}"
        value="{{quantidade}}"
        class="campo h-9 w-16 px-2 text-center"
        data-quantidade="{{idItem}}"
        data-valor-unitario="{{valorUnitario}}"
    >
</form>
