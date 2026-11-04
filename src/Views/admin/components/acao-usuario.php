<form method="POST" action="/admin/usuarios/status" class="inline" data-confirmar="{{confirmacao}}">
    {{csrf}}
    <input type="hidden" name="idUsuario" value="{{id}}">
    <input type="hidden" name="ativo" value="{{novoStatus}}">
    <button type="submit" class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold {{classeStatus}}">{{textoStatus}}</button>
</form>
