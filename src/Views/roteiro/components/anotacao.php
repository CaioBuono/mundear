<form method="POST" action="/roteiros/anotacao" class="px-[18px] pb-3.5 pt-1" data-anotacao>
    {{csrf}}
    <input type="hidden" name="idRoteiro" value="{{idRoteiro}}">
    <input type="hidden" name="data" value="{{data}}">

    <textarea name="texto" rows="2" maxlength="{{maximo}}" aria-label="Anotações do dia {{numeroDia}}" placeholder="Anotações do dia (opcional)" class="block min-h-11 w-full resize-y rounded-lg border border-linha bg-[#fbfcfe] px-3 py-2 text-[13.5px] outline-none transition placeholder:text-apagado/70 focus:border-navy focus:ring-3 focus:ring-suave">{{texto}}</textarea>
    <span class="mt-1 block text-xs text-apagado empty:hidden" data-status-anotacao aria-live="polite"></span>
</form>
