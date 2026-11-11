<form method="POST" action="/admin/usuarios/status" class="inline" data-confirmar="{{confirmacao}}">
    {{csrf}}
    <input type="hidden" name="idUsuario" value="{{id}}">
    <input type="hidden" name="ativo" value="{{novoStatus}}">
    <button type="submit" class="{{classeStatus}} !h-8 !px-3 !text-[13px]">{{textoStatus}}</button>
</form>
