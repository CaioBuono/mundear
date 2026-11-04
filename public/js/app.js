// Comportamentos comuns da área logada do Mundear.

// Confirmação antes de ações destrutivas: <form data-confirmar="Mensagem">
document.addEventListener('submit', function (evento) {
    const mensagem = evento.target.dataset.confirmar;
    if (mensagem && !window.confirm(mensagem)) {
        evento.preventDefault();
    }
});

// Copiar texto de um campo: <button data-copiar="#idDoCampo">
document.querySelectorAll('[data-copiar]').forEach(function (botao) {
    botao.addEventListener('click', function () {
        const campo = document.querySelector(botao.dataset.copiar);
        campo.select();

        const confirmarCopia = function () {
            const titulo = botao.getAttribute('title');
            botao.setAttribute('title', 'Copiado!');
            botao.classList.add('bg-emerald-50');
            setTimeout(function () {
                botao.setAttribute('title', titulo);
                botao.classList.remove('bg-emerald-50');
            }, 1500);
        };

        if (navigator.clipboard) {
            navigator.clipboard.writeText(campo.value).then(confirmarCopia, function () {
                document.execCommand('copy');
                confirmarCopia();
            });
        } else {
            document.execCommand('copy');
            confirmarCopia();
        }
    });
});

// Fecha o menu do perfil ao clicar fora dele
document.addEventListener('click', function (evento) {
    document.querySelectorAll('details[data-menu-perfil][open]').forEach(function (menu) {
        if (!menu.contains(evento.target)) {
            menu.removeAttribute('open');
        }
    });
});

// RN07: ao mudar a quantidade de pessoas, subtotal e total são atualizados na
// hora; ao sair do campo o formulário é enviado e o servidor recalcula e salva.
(function () {
    const campos = document.querySelectorAll('[data-quantidade]');
    const total = document.querySelector('[data-total-roteiro]');
    if (!campos.length || !total) return;

    const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const paraNumero = function (texto) {
        return parseFloat(texto.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
    };

    campos.forEach(function (campo) {
        const subtotal = document.querySelector('[data-subtotal="' + campo.dataset.quantidade + '"]');
        let valorAnterior = paraNumero(subtotal.textContent);

        campo.addEventListener('input', function () {
            const quantidade = parseInt(campo.value, 10);
            if (!quantidade || quantidade < 1 || quantidade > parseInt(campo.max, 10)) return;

            const novoSubtotal = quantidade * parseFloat(campo.dataset.valorUnitario);
            total.textContent = moeda.format(paraNumero(total.textContent) - valorAnterior + novoSubtotal);
            subtotal.textContent = moeda.format(novoSubtotal);
            valorAnterior = novoSubtotal;
        });

        campo.addEventListener('change', function () {
            campo.form.submit();
        });
    });
})();
