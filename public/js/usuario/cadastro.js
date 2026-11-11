(function () {
    const pagina = document.querySelector('#paginaLogin');
    const textos = {
        login: {
            titulo: 'Bem-vindo(a) de volta',
            subtitulo: 'Entre para continuar planejando suas viagens.',
            botao: 'Entrar',
            autocompleteSenha: 'current-password'
        },
        cadastro: {
            titulo: 'Crie sua conta grátis',
            subtitulo: 'Leva menos de um minuto.',
            botao: 'Criar conta',
            autocompleteSenha: 'new-password'
        }
    };

    function definirModo(modo, limparFeedback) {
        const cadastro = modo === 'cadastro';
        const texto = textos[modo];
        const botao = document.querySelector('#btnAcessar');

        document.querySelector('#tituloForm').textContent = texto.titulo;
        document.querySelector('#subtituloForm').textContent = texto.subtitulo;
        botao.textContent = texto.botao;
        botao.classList.toggle('btn-primario', !cadastro);
        botao.classList.toggle('btn-destaque', cadastro);
        document.querySelector('#senha').setAttribute('autocomplete', texto.autocompleteSenha);
        document.querySelector('#senha').setAttribute('placeholder', cadastro ? 'Mínimo de 8 caracteres' : '');

        ['#boxNomeUsuario', '#boxConfirmarSenha'].forEach(function (seletor) {
            const box = document.querySelector(seletor);
            box.classList.toggle('hidden', !cadastro);
            box.querySelector('input').disabled = !cadastro;
        });

        document.querySelector('#boxOpcoesLogin').classList.toggle('hidden', cadastro);
        document.querySelector('#linkEsqueci').classList.toggle('hidden', cadastro);
        document.querySelectorAll('[data-aba-auth]').forEach(function (aba) {
            aba.setAttribute('aria-selected', String(aba.dataset.abaAuth === modo));
        });
        pagina.dataset.modo = modo;

        if (limparFeedback) {
            document.querySelectorAll('#boxErroAuth, #paginaLogin [role="alert"]').forEach(function (alerta) {
                alerta.remove();
            });
        }
    }

    document.querySelectorAll('[data-aba-auth]').forEach(function (aba) {
        aba.addEventListener('click', function () {
            if (aba.dataset.abaAuth === pagina.dataset.modo) return;
            definirModo(aba.dataset.abaAuth, true);
            document.querySelector(aba.dataset.abaAuth === 'cadastro' ? '#nome' : '#email').focus();
        });
    });

    definirModo(pagina.dataset.modo === 'cadastro' ? 'cadastro' : 'login', false);
})();
