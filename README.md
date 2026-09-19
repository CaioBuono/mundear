# Mundear

Mundear é uma aplicação web, ainda em estágio inicial, voltada ao planejamento e compartilhamento de viagens. O que está implementado atualmente é a base de uma aplicação PHP com arquitetura MVC simplificada e uma tela responsiva de autenticação.

## Estado atual

O projeto já contém:

- tela de login responsiva;
- rota `GET /login` para exibir o formulário;
- rota `POST /login` para validar e-mail e senha;
- mensagem visual para credenciais inválidas;
- modelo de usuário com verificação de senha por `password_verify`;
- armazenamento provisório de usuários em um arquivo JSON;
- autoload de classes próprio;
- renderizador simples de layouts e componentes HTML;
- arquivos de layout separados para páginas autenticáveis e páginas padrão.

Ainda não estão implementados:

- sessão ou persistência do estado de autenticação;
- resposta, redirecionamento ou página de destino após um login válido;
- cadastro de usuário;
- recuperação de senha;
- comportamento para a opção “Lembrar de mim”;
- logout;
- banco de dados;
- página inicial ou painel;
- respostas HTTP para rotas não encontradas;
- testes automatizados.

> O projeto é um protótipo em desenvolvimento e não está pronto para uso em produção.

## Requisitos

- PHP **8.4 ou superior**. O modelo usa `array_find_key`, função disponível a partir do PHP 8.4.
- Acesso à internet no navegador para carregar o Tailwind CSS pelo CDN.

Não há dependências gerenciadas por Composer, Node.js ou processo de build neste momento.

## Executando localmente

Na raiz do repositório, execute:

```bash
php -S localhost:8000 router.php
```

Depois acesse:

```text
http://localhost:8000/login
```

O arquivo `router.php` permite que o servidor embutido do PHP entregue os arquivos estáticos de `public/` diretamente e encaminhe as demais requisições para `index.php`.

## Rotas disponíveis

| Método | Caminho | Implementação atual |
| --- | --- | --- |
| `GET` | `/login` | Renderiza a página de login. |
| `POST` | `/login` | Valida `email` e `senha`; em caso de falha, renderiza a página com um alerta. Em caso de sucesso, ainda não produz uma resposta. |

Outros caminhos não têm tratamento ou página 404 e, atualmente, retornam uma resposta vazia.

## Estrutura do projeto

```text
mundear/
├── autoload.php                    # Autoload simples dos namespaces em src/
├── index.php                       # Bootstrap da aplicação
├── router.php                      # Roteador do servidor embutido do PHP
├── routes/
│   └── web.php                     # Mapeamento de rotas HTTP
├── public/
│   └── images/                     # Imagens da interface
└── src/
    ├── BD/
    │   └── table_usuario.json      # Armazenamento provisório de usuários
    ├── Controllers/
    │   └── UsuarioController.php   # Fluxo da página e autenticação
    ├── Models/
    │   └── Usuario.php             # Entidade e regras de autenticação
    └── Views/
        ├── ViewRenderer.php         # Composição de layouts e placeholders
        ├── layouts/
        │   ├── auth/                # Estrutura usada pela tela de login
        │   └── default/             # Estrutura geral ainda não utilizada
        └── usuario/                 # Tela de login e alerta de erro
```

Uma descrição mais detalhada dos componentes e do fluxo interno está em [docs/ARQUITETURA.md](docs/ARQUITETURA.md).

## Como a autenticação funciona hoje

1. O formulário envia `email` e `senha` via `POST /login`.
2. `UsuarioController::login()` encaminha os dados ao modelo.
3. `Usuario::autenticar()` procura o e-mail em `src/BD/table_usuario.json`.
4. A senha informada é comparada ao hash salvo usando `password_verify()`.
5. Credenciais inválidas fazem o controller montar novamente a tela de login com o componente de erro.
6. Para credenciais válidas, o modelo retorna um objeto `Usuario`, mas o controller ainda não inicia uma sessão nem retorna/redireciona o usuário.

O JSON funciona apenas como uma fonte temporária de dados. Não coloque credenciais reais nele e não o use como solução de persistência em produção.

## Interface

A página usa Tailwind CSS 4 por meio do script oficial distribuído via CDN. Não existe configuração local do Tailwind. Em telas grandes, a interface mostra uma área visual de apresentação ao lado do formulário; em telas menores, apenas o formulário é exibido.

Os links “Esqueceu a senha?” e “Cadastre-se”, assim como a opção “Lembrar de mim”, são apenas elementos visuais por enquanto.

## Verificações de desenvolvimento

Para checar a sintaxe de todos os arquivos PHP:

```bash
find . -type f -name '*.php' -not -path './.git/*' -print0 \
  | xargs -0 -n1 php -l
```

O repositório ainda não possui suíte de testes nem ferramentas configuradas de análise estática ou padronização de código.

## Próximos passos sugeridos

1. Validar e normalizar os dados recebidos pelo controller.
2. Criar sessão e redirecionamento depois do login válido.
3. Adicionar logout e proteção de rotas autenticadas.
4. Migrar os usuários do JSON para um banco de dados.
5. Implementar cadastro e recuperação de senha ou remover temporariamente seus links.
6. Tratar rotas desconhecidas com status `404` e métodos não permitidos com `405`.
7. Adicionar proteção CSRF e controles contra tentativas repetidas de login.
8. Criar testes unitários e de integração para modelo, renderizador e rotas.

## Observações de segurança

- O formulário não possui token CSRF.
- Não há limitação de tentativas de autenticação.
- Não há sessão autenticada nem cookies de autenticação.
- O arquivo JSON está dentro do código-fonte e deve conter somente dados fictícios durante o desenvolvimento.
- O Tailwind é carregado de um serviço externo, sem pipeline local de assets.
- Entradas e valores inseridos pelo renderizador não recebem escape HTML automático.

## Licença

O repositório ainda não declara uma licença.
