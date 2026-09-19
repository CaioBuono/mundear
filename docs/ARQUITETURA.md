# Arquitetura atual

Este documento descreve apenas a arquitetura existente no repositório. O Mundear ainda está no começo do desenvolvimento e implementa um MVC leve, sem framework e sem contêiner de dependências.

## Visão geral

```text
Requisição HTTP
      │
      ▼
  router.php ── arquivo estático existente ──► resposta direta
      │
      ▼
   index.php
      │
      ▼
 routes/web.php
      │
      ▼
UsuarioController
   │          │
   ▼          ▼
Usuario    ViewRenderer
   │          │
   ▼          ▼
 JSON     Layout + View + Componente
```

## Entrada e roteamento

### `router.php`

É usado com o servidor embutido do PHP. Ele extrai o caminho da URL e:

- retorna `false` quando o caminho corresponde a um arquivo existente, permitindo que o PHP sirva imagens e outros arquivos estáticos;
- inclui `index.php` para qualquer outro caminho.

### `index.php`

É o bootstrap atual. Carrega o autoload e, em seguida, o arquivo de rotas. Também declara a constante `PATH_ESTRUTURA_PADRAO`, mas ela ainda não é utilizada. Como a constante é definida depois da inclusão das rotas, uma resposta gerada durante o roteamento não depende dela.

### `routes/web.php`

O roteador é composto por condicionais do método HTTP e `switch` do caminho. Há somente duas combinações registradas:

- `GET /login` chama `UsuarioController::getLogin()`;
- `POST /login` chama `UsuarioController::login()`.

Não existe abstração de rota, parâmetros de URL, middleware, resposta 404 ou resposta 405.

## Autoload e namespaces

`autoload.php` registra uma função que transforma o nome completo da classe em um caminho sob `src/`:

```text
Controllers\UsuarioController
              ↓
src/Controllers/UsuarioController.php
```

Isso exige que namespace, nome da classe e caminho do arquivo permaneçam alinhados. O mecanismo se parece com o mapeamento PSR-4, mas é uma implementação própria e não usa Composer.

## Camada de controller

`Controllers\UsuarioController` coordena o caso de uso de autenticação e seleciona os arquivos de apresentação.

### Exibição do formulário

`getLogin()` define os diretórios do layout de autenticação e da view de usuário. Depois pede ao `ViewRenderer` para montar `usuario/login.php`. O placeholder `{{errorAuth}}` começa vazio.

### Envio do formulário

`login()` lê diretamente `$_POST['email']` e `$_POST['senha']` e chama `Usuario::autenticar()`.

- Na falha, renderiza `erro-login.php`, injeta seu HTML em `{{errorAuth}}` e retorna a tela completa.
- No sucesso, o método termina sem retorno. Ainda falta definir a sessão e a próxima página do usuário.

O acesso direto às chaves de `$_POST` pressupõe que ambas estejam presentes. Requisições incompletas podem gerar avisos do PHP, mesmo que os campos sejam obrigatórios no HTML.

## Camada de modelo e dados

`Models\Usuario` representa um usuário e concentra a leitura e validação das credenciais.

Seus atributos são privados:

- `idUsuario`;
- `nome`;
- `email`;
- `senha`;
- `dataCadastro`.

A senha não possui getter público. Os demais dados podem ser consultados depois que o objeto é criado.

O método `autenticar()`:

1. carrega e decodifica `src/BD/table_usuario.json`;
2. procura o índice do e-mail com `array_find_key()`;
3. cria um objeto `Usuario` com o registro encontrado;
4. valida a senha com `password_verify()`;
5. devolve o usuário no sucesso ou `false` na falha.

O arquivo JSON é lido a cada tentativa. Não há tratamento explícito para arquivo ausente, JSON inválido ou registros com campos incompletos.

## Camada de apresentação

### `Views\ViewRenderer`

O renderizador trabalha com strings e arquivos estáticos. Ele oferece duas operações públicas:

- `getLayout()` lê uma view, substitui placeholders e envolve o resultado com o cabeçalho e o rodapé escolhidos;
- `getComponente()` lê um fragmento e substitui seus placeholders.

Os placeholders seguem o formato `{{nome}}` e são substituídos com `str_replace`. Não há engine de templates, condicionais, loops ou escape HTML automático.

### Layouts

- `layouts/auth/` contém a estrutura HTML usada no login.
- `layouts/default/` é uma estrutura inicial, ainda não ligada a nenhuma rota.

Os dois cabeçalhos carregam Tailwind CSS 4 pelo CDN. Isso mantém o protótipo sem etapa de build, mas torna a aparência dependente de uma conexão externa.

### Views de usuário

- `usuario/login.php` contém o formulário e a apresentação responsiva.
- `usuario/erro-login.php` contém o alerta de credenciais inválidas.

Embora tenham extensão `.php`, esses arquivos atualmente contêm apenas HTML e placeholders textuais.

## Fluxos observáveis

### Carregamento da tela

```text
GET /login
  → UsuarioController::getLogin()
  → ViewRenderer::getLayout()
  → auth/header.php + usuario/login.php + auth/footer.php
  → HTML
```

### Credenciais inválidas

```text
POST /login
  → UsuarioController::login()
  → Usuario::autenticar()
  → table_usuario.json + password_verify()
  → false
  → erro-login.php inserido em login.php
  → HTML com alerta
```

### Credenciais válidas

```text
POST /login
  → UsuarioController::login()
  → Usuario::autenticar()
  → objeto Usuario
  → nenhuma sessão, resposta ou navegação definida ainda
```

## Decisões e limitações atuais

| Área | Escolha atual | Consequência |
| --- | --- | --- |
| Framework | PHP puro | Poucas dependências, mas infraestrutura HTTP precisa ser construída no projeto. |
| Dados | Arquivo JSON | Simples para prototipar; inadequado para concorrência, consultas e produção. |
| Templates | Substituição de strings | Fácil de entender; não oferece escape automático ou recursos de template. |
| Estilos | Tailwind via CDN | Dispensa build local; exige internet e não otimiza CSS para produção. |
| Autenticação | `password_verify` | A comparação de hashes é apropriada, mas ainda faltam sessão e controles de segurança. |
| Rotas | Condicionais e `switch` | Suficiente para duas rotas; tende a crescer de forma difícil de manter. |

## Pontos de atenção para evolução

- Introduzir um objeto de requisição/resposta ou, no mínimo, centralizar validação e códigos HTTP.
- Evitar que o controller dependa diretamente de `$_POST`.
- Separar acesso a dados da entidade `Usuario` quando um banco for introduzido.
- Escapar valores dinâmicos antes de inseri-los em HTML.
- Definir o contrato do login bem-sucedido antes de criar rotas protegidas.
- Configurar sessões com cookies `HttpOnly`, `Secure` em HTTPS e política `SameSite` adequada.
- Mover segredos e configuração de ambiente para fora do repositório.
- Adicionar tratamento de erros de leitura e decodificação do armazenamento.
