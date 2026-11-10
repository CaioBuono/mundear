document.addEventListener('submit', function (evento) {
    const mensagem = evento.target.dataset.confirmar;
    if (mensagem && !window.confirm(mensagem)) {
        evento.preventDefault();
    }
});

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

document.addEventListener('click', function (evento) {
    document.querySelectorAll('details[data-menu-perfil][open]').forEach(function (menu) {
        if (!menu.contains(evento.target)) {
            menu.removeAttribute('open');
        }
    });
});

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

document.addEventListener('click', function (evento) {
    const botao = evento.target.closest('[data-passo]');
    if (!botao) {
        return;
    }

    const [idCampo, passo] = botao.dataset.passo.split(':');
    const campo = document.getElementById(idCampo);
    const minimo = Number(campo.min || 1);
    const maximo = Number(campo.max || 99);
    const valor = (Number(campo.value) || minimo) + Number(passo);

    campo.value = Math.min(maximo, Math.max(minimo, valor));
});

const formatarMoeda = valor => valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

document.querySelectorAll('[data-filtro-preco]').forEach(function (formulario) {
    const faixa = formulario.querySelector('input[type=range]');
    const texto = formulario.querySelector('[data-texto-preco]');

    faixa.addEventListener('input', function () {
        const valor = Number(faixa.value);
        texto.textContent = valor >= Number(faixa.max) ? 'qualquer valor' : 'até ' + formatarMoeda(valor);
    });

    faixa.addEventListener('change', function () {
        formulario.submit();
    });
});

const VEICULOS = {
    'Carro': { consumo: 12, fatorPedagio: 1 },
    'Moto': { consumo: 30, fatorPedagio: 0.5 },
    'SUV ou picape': { consumo: 9, fatorPedagio: 1 },
    'Van': { consumo: 7.5, fatorPedagio: 1 }
};

const COMBUSTIVEIS = {
    'Gasolina': { preco: 6.29, fatorConsumo: 1 },
    'Etanol': { preco: 4.39, fatorConsumo: 0.7 },
    'Diesel': { preco: 6.09, fatorConsumo: 1 }
};

const PEDAGIO_BASE = 152.40;

document.querySelectorAll('[data-estimativa]').forEach(function (formulario) {
    const campo = nome => formulario.elements[nome];
    const saida = nome => formulario.querySelector('[data-saida="' + nome + '"]');

    function calcular() {
        const trechos = campo('idaVolta').checked ? 2 : 1;
        const distancia = Math.max(0, Number(campo('distancia').value) || 0) * trechos;
        const consumo = Math.max(0.1, Number(campo('consumo').value) || 0.1);
        const litros = distancia / consumo;
        const gastoCombustivel = litros * Math.max(0, Number(campo('precoCombustivel').value) || 0);
        const pedagios = Math.max(0, Number(campo('pedagios').value) || 0) * trechos;

        saida('titulo').textContent = 'Custo estimado · ' + campo('tipoVeiculo').value.toLowerCase() + ' a ' + campo('combustivel').value.toLowerCase();
        saida('km').textContent = distancia.toLocaleString('pt-BR') + ' km';
        saida('litros').textContent = litros.toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + ' litros';
        saida('combustivel').textContent = formatarMoeda(gastoCombustivel);
        saida('pedagios').textContent = formatarMoeda(pedagios);
        saida('total').textContent = formatarMoeda(gastoCombustivel + pedagios);
    }

    function aplicarPadroes() {
        const veiculo = VEICULOS[campo('tipoVeiculo').value];
        const combustivel = COMBUSTIVEIS[campo('combustivel').value];

        campo('consumo').value = Number((veiculo.consumo * combustivel.fatorConsumo).toFixed(1));
        campo('precoCombustivel').value = combustivel.preco.toFixed(2);
        campo('pedagios').value = (PEDAGIO_BASE * veiculo.fatorPedagio).toFixed(2);
        calcular();
    }

    campo('tipoVeiculo').addEventListener('change', aplicarPadroes);
    campo('combustivel').addEventListener('change', aplicarPadroes);
    ['distancia', 'consumo', 'precoCombustivel', 'pedagios', 'idaVolta'].forEach(nome => campo(nome).addEventListener('input', calcular));
    calcular();
});

document.addEventListener('click', function (evento) {
    const botaoAlternar = evento.target.closest('[data-alternar]');
    if (botaoAlternar) {
        const painel = document.getElementById(botaoAlternar.dataset.alternar);
        painel.hidden = !painel.hidden;
        botaoAlternar.setAttribute('aria-expanded', String(!painel.hidden));
        return;
    }

    const botaoAbrir = evento.target.closest('[data-abrir-janela]');
    if (botaoAbrir) {
        document.getElementById(botaoAbrir.dataset.abrirJanela).showModal();
        return;
    }

    const botaoFechar = evento.target.closest('[data-fechar-janela]');
    if (botaoFechar) {
        botaoFechar.closest('dialog').close();
        return;
    }

    if (evento.target.matches('dialog')) {
        evento.target.close();
        return;
    }

    const botaoGaleria = evento.target.closest('[data-galeria-passo]');
    if (botaoGaleria) {
        const galeria = botaoGaleria.closest('[data-galeria]');
        const fotos = JSON.parse(galeria.dataset.galeria);
        const atual = Number(galeria.dataset.indice || 0);
        const novo = (atual + Number(botaoGaleria.dataset.galeriaPasso) + fotos.length) % fotos.length;

        galeria.dataset.indice = novo;
        galeria.querySelector('img').src = fotos[novo];
        galeria.querySelector('[data-galeria-contagem]').textContent = (novo + 1) + '/' + fotos.length;
        galeria.querySelectorAll('[data-galeria-pontos] i').forEach(function (ponto, indice) {
            ponto.className = 'h-1.5 rounded-full bg-white ' + (indice === novo ? 'w-4' : 'w-1.5 opacity-55');
        });
        return;
    }

    const fotoAmpliada = evento.target.closest('[data-foto-ampliada]');
    if (fotoAmpliada) {
        fotoAmpliada.remove();
        return;
    }

    const foto = evento.target.closest('img[data-ampliar]');
    if (foto) {
        const fundo = document.createElement('div');
        fundo.dataset.fotoAmpliada = '';
        fundo.className = 'fixed inset-0 z-50 flex cursor-zoom-out flex-col items-center justify-center gap-3.5 bg-[rgb(8,15,30)] p-6';

        const imagem = document.createElement('img');
        imagem.src = foto.src;
        imagem.alt = foto.alt;
        imagem.className = 'max-h-[calc(100vh-120px)] max-w-full rounded-lg object-contain';

        const legenda = document.createElement('span');
        legenda.className = 'text-[15px] font-bold text-white';
        legenda.textContent = foto.alt.replace('. Clique para ampliar', '');

        fundo.append(imagem, legenda);
        document.body.append(fundo);
    }
});

document.addEventListener('keydown', function (evento) {
    const fotoAmpliada = document.querySelector('[data-foto-ampliada]');
    if (evento.key === 'Escape' && fotoAmpliada) {
        fotoAmpliada.remove();
    }
});
