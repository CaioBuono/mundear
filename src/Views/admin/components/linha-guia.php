<tr class="{{classeLinha}}">
    <td class="border-b border-linha px-4 py-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="grid h-10 w-10 flex-none place-items-center rounded-full bg-ok-suave text-[13px] font-extrabold text-ok">{{iniciais}}</span>
            <div class="min-w-0">
                <b class="block">{{nome}}</b>
                <span class="text-[12.5px] text-apagado">{{especialidade}}</span>
            </div>
        </div>
    </td>
    <td class="border-b border-linha px-4 py-3">{{cidade}}</td>
    <td class="border-b border-linha px-4 py-3">{{telefone}}</td>
    <td class="border-b border-linha px-4 py-3">{{valorDiaria}}<br><span class="dica">por {{unidade}}</span></td>
    <td class="border-b border-linha px-4 py-3">{{status}}</td>
    <td class="border-b border-linha px-4 py-3">
        <div class="flex justify-end gap-2">
            <a href="/admin/guias/editar?id={{id}}" class="btn-secundario !h-8 !px-3 !text-[13px]">Editar</a>

            <form method="POST" action="/admin/guias/status" data-confirmar="{{confirmacao}}">
                {{csrf}}
                <input type="hidden" name="idGuia" value="{{id}}">
                <input type="hidden" name="ativo" value="{{novoStatus}}">
                <button type="submit" class="{{classeStatus}} !h-8 !px-3 !text-[13px]">{{textoStatus}}</button>
            </form>

            <form method="POST" action="/admin/guias/excluir" data-confirmar="Excluir este guia definitivamente? Guias que já estão em roteiros só podem ser inativados.">
                {{csrf}}
                <input type="hidden" name="idGuia" value="{{id}}">
                <button type="submit" class="btn-perigo !h-8 !border-alerta !bg-alerta !px-3 !text-[13px] !text-white">Excluir</button>
            </form>
        </div>
    </td>
</tr>
