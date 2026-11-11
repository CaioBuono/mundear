<form method="POST" action="{{acao}}" data-confirmar="Remover &quot;{{descricao}}&quot; do roteiro?">
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{idRoteiro}}">
    <input type="hidden" name="{{nomeCampo}}" value="{{id}}">

    <button type="submit" class="grid cursor-pointer place-items-center rounded-md p-1.5 text-apagado transition hover:bg-alerta-suave hover:text-alerta" aria-label="Remover {{descricao}}" title="Remover">
        {{iconeLixeira}}
    </button>
</form>
