<h2 class="text-lg font-semibold text-blue-950">Finalizar e compartilhar</h2>
<p class="mt-1 text-sm text-slate-500">O sistema salva o pacote e gera um link em modo somente leitura — quem receber pode ver o cronograma, mas não pode alterar nada.</p>

<form method="POST" action="/roteiros/finalizar" class="mt-4 space-y-3">
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{id}}">

    <div>
        <label for="validade" class="rotulo">Validade do link</label>
        <select id="validade" name="validade" class="campo">
            {{opcoesValidade}}
        </select>
    </div>

    <button type="submit" class="btn-destaque w-full">{{textoBotao}}</button>
</form>

{{aviso}}
