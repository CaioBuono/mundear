<tr class="align-middle">
    <td class="px-5 py-4">
        <p class="font-semibold text-blue-950">{{nome}}</p>
        <p class="text-xs text-slate-500">{{especialidade}}</p>
    </td>
    <td class="px-5 py-4 text-slate-600">{{cidade}}</td>
    <td class="px-5 py-4 text-slate-600">{{telefone}}</td>
    <td class="px-5 py-4 font-medium text-blue-950">{{valorDiaria}}</td>
    <td class="px-5 py-4">{{status}}</td>
    <td class="px-5 py-4">
        <div class="flex justify-end gap-1">
            <a href="/admin/guias/editar?id={{id}}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Editar</a>

            <form method="POST" action="/admin/guias/status" data-confirmar="{{confirmacao}}">
                {{csrf}}
                <input type="hidden" name="idGuia" value="{{id}}">
                <input type="hidden" name="ativo" value="{{novoStatus}}">
                <button type="submit" class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold {{classeStatus}}">{{textoStatus}}</button>
            </form>

            <form method="POST" action="/admin/guias/excluir" data-confirmar="Excluir este guia definitivamente? Guias que já estão em roteiros só podem ser inativados.">
                {{csrf}}
                <input type="hidden" name="idGuia" value="{{id}}">
                <button type="submit" class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Excluir</button>
            </form>
        </div>
    </td>
</tr>
