document.querySelector('#btnCadastroUsuario').addEventListener('click', function () {
   document.querySelector('#boxNomeUsuario').innerHTML = getInputNomeUsuario();
   document.querySelector('#boxConfirmarSenha').innerHTML = getInputConfirmarSenha();
   document.querySelector('#btnAcessar').innerHTML = 'Cadastrar';

   if(document.querySelector('#boxErroAuth')){
       document.querySelector('#boxErroAuth').remove();
   }
});

function getInputNomeUsuario()
{
    return `<label for="nome" class="mb-2 block text-sm font-semibold text-blue-950">
                Nome
            </label>

            <input
                    id="nome"
                    name="nome"
                    type="text"
                    placeholder="Seu nome"
                    required
                    class="h-13 w-full rounded-xl border border-slate-300 px-4 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-900 focus:ring-4 focus:ring-blue-100"
            >`;
}

function getInputConfirmarSenha()
{
    return `<label for="confirm" class="mb-2 block text-sm font-semibold text-blue-950">
                Confirmar Senha
            </label>

            <input
                    id="confirm"
                    name="confirm"
                    type="password"
                    placeholder="Confirme a senha"
                    required
                    class="h-13 w-full rounded-xl border border-slate-300 px-4 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-900 focus:ring-4 focus:ring-blue-100"
            >`;
}