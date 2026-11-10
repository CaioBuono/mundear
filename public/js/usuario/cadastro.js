(function () {
    const pagina = document.querySelector('#paginaLogin');
    const textos = {
        login: {
            titulo: 'Bem-vindo de volta!',
            subtitulo: 'Faça login para continuar planejando suas aventuras.',
            botao: 'Entrar',
            alternar: 'Ainda não tem conta?',
            botaoAlternar: 'Cadastre-se',
            autocompleteSenha: 'current-password'
        },
        cadastro: {
            titulo: 'Crie sua conta',
            subtitulo: 'Cadastre-se gratuitamente e comece a montar seus roteiros.',
            botao: 'Cadastrar',
            alternar: 'Já tem uma conta?',
            botaoAlternar: 'Entrar com minha conta',
            autocompleteSenha: 'new-password'
        }
    };

    function definirModo(modo, limparFeedback) {
        const cadastro = modo === 'cadastro';
        const texto = textos[modo];

        document.querySelector('#tituloForm').textContent = texto.titulo;
        document.querySelector('#subtituloForm').textContent = texto.subtitulo;
        document.querySelector('#btnAcessar').textContent = texto.botao;
        document.querySelector('#textoAlternar').textContent = texto.alternar;
        document.querySelector('#btnCadastroUsuario').textContent = texto.botaoAlternar;
        document.querySelector('#senha').setAttribute('autocomplete', texto.autocompleteSenha);

        ['#boxNomeUsuario', '#boxConfirmarSenha'].forEach(function (seletor) {
            const box = document.querySelector(seletor);
            box.classList.toggle('hidden', !cadastro);
            box.querySelector('input').disabled = !cadastro;
        });

        document.querySelector('#boxOpcoesLogin').classList.toggle('hidden', cadastro);
        pagina.dataset.modo = modo;

        if (limparFeedback) {
            document.querySelectorAll('#boxErroAuth, #paginaLogin [role="alert"]').forEach(function (alerta) {
                alerta.remove();
            });
        }
    }

    document.querySelector('#btnCadastroUsuario').addEventListener('click', function () {
        definirModo(pagina.dataset.modo === 'cadastro' ? 'login' : 'cadastro', true);
        document.querySelector(pagina.dataset.modo === 'cadastro' ? '#nome' : '#email').focus();
    });

    definirModo(pagina.dataset.modo === 'cadastro' ? 'cadastro' : 'login', false);
})();
