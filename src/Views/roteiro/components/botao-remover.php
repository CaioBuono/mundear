<form method="POST" action="/roteiros/itens/remover" data-confirmar="Remover este item do roteiro?">
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{idRoteiro}}">
    <input type="hidden" name="idItem" value="{{idItem}}">

    <button type="submit" class="cursor-pointer rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Remover item" aria-label="Remover item">
        {{iconeLixeira}}
    </button>
</form>
