# WGL Modelos

Galeria e painel de sites prontos da **WGL Software**, no ar em https://modelos.wglsoftware.com.

- **Galeria pública** (`index.php`): modelos por nicho, demonstrações e a seção de sistemas sob medida.
- **Painel** (`/admin`): cria um site a partir de um modelo, edita por formulário, sobe fotos (convertidas para WebP), publica como Prévia (link secreto que vence em 15 dias, com página de proposta), Portfólio, Demonstração ou Rascunho, duplica, exporta `.zip` para o domínio do cliente.

PHP 8.1+, **sem banco de dados**: cada site é uma ficha JSON em `_app/dados/fichas/`, e a pasta `_app/` é bloqueada para o navegador.

## Estrutura

```
index.php                galeria pública
admin/                   painel (login com senha definida no primeiro acesso)
assets/                  logo e telas dos sistemas
_app/nucleo.php          funções comuns, configuração, leitura/gravação de fichas
_app/auth.php            login, sessão e limite de tentativas
_app/render.php          gera o HTML estático de cada site a partir da ficha + modelo
_app/modelos/<id>/       um modelo = modelo.php + estilo.css + schema.json + exemplo.json
_app/dados/              DADOS DO SERVIDOR (fora do Git, ver abaixo)
demo-*/                  demonstrações geradas a partir de _app/dados/fichas/demo-*.json
```

Modelos: beleza, serviços técnicos, alimentação, saúde e bem-estar, pet shop, academia, advocacia e profissional liberal.

O modelo **advocacia** (`_app/modelos/advocacia/`) segue o Provimento 205/2021 da OAB: sem preço, sem promessa de resultado e sem depoimentos, com o número da OAB visível. Tem áreas de atuação em abas, formulário que monta a mensagem e abre no WhatsApp (nada é gravado), eventos `clique_whatsapp` e `envio_formulario` para o Google Tag Manager, que só carrega depois do aceite de cookies. Demonstração: `demo-advogada/`.

Um modelo pode esconder seções comuns que não servem ao nicho com `"ocultar_comuns"` no `schema.json` (e `$M['ocultar']` no `modelo.php`), e pode incluir um bloco antes do endereço em `$B['contato']`.

## O que fica fora do Git

O `.gitignore` deixa de fora tudo o que é gerado ou guardado no servidor:

- `_app/dados/config.json` (hash da senha do painel, WhatsApp, textos da galeria)
- `_app/dados/instalacao.json` e `tentativas.json`
- `_app/dados/fichas/*` (prévias de leads e sites de clientes), menos as fichas `demo-*`
- `_app/dados/lixeira/`
- as pastas dos sites criados no painel (cada uma na raiz), menos `demo-*`

Sem `config.json`, o painel usa os valores padrão e pede a criação da senha no primeiro acesso.

## Publicação pela Hostinger (Git)

O deploy pelo Git da Hostinger sobrescreve os arquivos versionados e não apaga os que estão fora do Git. Mas **a conexão inicial exige a pasta vazia**. Antes de conectar:

1. No gerenciador de arquivos, compacte e baixe `_app/dados/` e as pastas dos sites de clientes/prévias (todas as pastas da raiz que não sejam `_app`, `admin`, `assets` e `demo-*`).
2. Esvazie a pasta do site e conecte o repositório (branch `main`).
3. Envie de volta e descompacte o que foi baixado no passo 1.

Depois disso, cada `git push` na `main` publica sozinho, sem tocar na senha nem nos sites dos clientes.
