# Mundear

Mundear é uma aplicação web para planejar e compartilhar viagens, feita para a disciplina de Programação Orientada a Objetos. O viajante monta um roteiro dia a dia com transporte, hospedagem, guias, passeios e gastos extras, acompanha o custo total e o orçamento e compartilha o roteiro por um link somente leitura. O administrador gerencia os guias turísticos e os usuários.

O foco do trabalho é o fluxo dos objetos em PHP. A persistência é feita em arquivos JSON (arquitetura NoSQL), sem banco de dados.

## Requisitos

- PHP 8.2 ou superior.
- Acesso à internet no navegador, para carregar o Tailwind CSS e as fontes pelo CDN.

Não há dependências de Composer, Node.js nem processo de build.

## Executando localmente

Na raiz do repositório:

```bash
php -S localhost:8000 router.php
```

Depois acesse `http://localhost:8000`. O `router.php` entrega os arquivos de `public/` e encaminha as demais requisições para `index.php`.

### Contas de demonstração

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Viajante | `viajante@mundear.com` | `viajante123` |
| Administrador | `admin@mundear.com` | `admin123` |

### Restaurar os dados de exemplo

```bash
php seed.php
```

Copia os arquivos de `src/BD/seed/` para `src/BD/`, apagando tudo o que foi criado nos testes.

## Funcionalidades

- **Landing page** com cadastro e aceite da LGPD.
- **Login, cadastro, "lembrar de mim", recuperação e redefinição de senha.**
- **Início:** próxima viagem, formulário "Planeje sua próxima viagem" e atalhos por situação do roteiro.
- **Meus roteiros:** lista com filtro por situação (Em planejamento, Pronto para viajar, Viagem realizada) e janela "Novo roteiro".
- **Transporte:** avião, ônibus e carona com filtro de preço, e estimativa de custo do veículo próprio.
- **Hospedagem:** hotel, hostel, hotel fazenda e temporada, com comparação de preço entre sites.
- **Turismo:** guias locais e passeios, com busca e escolha do dia.
- **Roteiro:** cronograma por dia, gastos extras, anotações salvas automaticamente, edição dos dados da viagem, orçamento, conclusão e link de compartilhamento.
- **Página do acompanhante:** roteiro em modo somente leitura pelo link.
- **Meu perfil:** dados pessoais, estilo de viagem, comunicações e troca de senha.
- **Painel do administrador:** relatório, cadastro e edição de guias, ativação e inativação de guias e usuários.

## Estrutura do projeto

```text
mundear/
├── index.php, router.php, autoload.php   # Bootstrap, roteador do servidor embutido e autoload
├── seed.php                              # Restaura os dados de exemplo
├── routes/web.php                        # Rotas GET e POST, com validação de CSRF
├── public/                               # JavaScript e imagens
└── src/
    ├── BD/                               # TabelaJson e os arquivos table_*.json (seed/ guarda os originais)
    ├── Controllers/                      # Um controller por área; BuscaController é a base das telas de busca
    ├── Exceptions/                       # RegraNegocioException e as exceções de cada tipo de regra
    ├── Models/                           # Classes de domínio (Usuario, Administrador, Roteiro, ItemRoteiro...)
    ├── Services/                         # APIs e e-mail simulados
    ├── Session/                          # Sessão, mensagens e dados de formulário
    ├── Utils/                            # Formatador, Validador e Texto
    └── Views/                            # Layouts, telas e componentes com placeholders {{variavel}}
```

## Interface

O visual segue o protótipo do Mundear e usa Tailwind CSS 4 pelo CDN. As cores e fontes do tema ficam em `src/Views/layouts/tema.php`.

## Verificação de sintaxe

```bash
find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```
