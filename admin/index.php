<?php
/**
 * Painel WGL Modelos — modelos.wglsoftware.com/admin
 * Um arquivo só: login, lista de sites, novo, editar, configurações e ações.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/_app/nucleo.php';
require APP . '/auth.php';

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

iniciar_sessao();
$cfg = config();
$acao = (string)($_POST['acao'] ?? $_GET['acao'] ?? '');
$p = (string)($_GET['p'] ?? 'lista');

// ---------------------------------------------------------------- helpers de página
function flash(?string $msg = null, string $tipo = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = [$msg, $tipo];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function ir(string $q = ''): never
{
    header('Location: ./' . ($q ? '?' . $q : ''));
    exit;
}

function json_resposta(array $d, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function topo(string $titulo, string $ativo = ''): void
{
    $logado = logado();
    $fl = flash(); ?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · Painel WGL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap">
<link rel="stylesheet" href="painel.css?v=<?= VERSAO ?>">
<link rel="icon" href="/assets/wgl-logo.svg" type="image/svg+xml">
</head>
<body>
<header class="barra">
  <div class="largura barra-linha">
    <a class="logo" href="./"><img src="/assets/wgl-logo.svg" alt="" width="40" height="21"><span>Painel <b>WGL Modelos</b></span></a>
    <?php if ($logado): ?>
    <nav class="nav" aria-label="Painel">
      <a href="./"<?= $ativo === 'lista' ? ' aria-current="page"' : '' ?>>Sites</a>
      <a href="./?p=novo"<?= $ativo === 'novo' ? ' aria-current="page"' : '' ?>>Novo site</a>
      <a href="./?p=config"<?= $ativo === 'config' ? ' aria-current="page"' : '' ?>>Configurações</a>
      <a href="../" target="_blank" rel="noopener">Ver galeria ↗</a>
      <form method="post" class="sair"><input type="hidden" name="acao" value="sair"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button type="submit">Sair</button></form>
    </nav>
    <?php endif; ?>
  </div>
</header>
<main class="largura conteudo">
<?php if ($fl): ?><p class="aviso aviso-<?= e($fl[1]) ?>" role="status"><?= e($fl[0]) ?></p><?php endif;
}

function fim(string $script = ''): void
{ ?>
</main>
<?= $script ?>
</body>
</html>
<?php
}

function chip_situacao(array $f): string
{
    $s = $f['situacao'] ?? 'rascunho';
    $txt = SITUACOES[$s] ?? $s;
    $extra = '';
    $d = previa_dias_restantes($f);
    if ($d !== null) {
        $extra = $d > 0 ? " · vence em $d " . ($d === 1 ? 'dia' : 'dias') : ' · vencida';
    }
    $classe = $d !== null && $d <= 0 ? 'vencida' : $s;
    return '<span class="chip chip-' . e($classe) . '">' . e($txt . $extra) . '</span>';
}

function data_br(?string $iso): string
{
    $t = $iso ? strtotime($iso) : false;
    return $t ? date('d/m/Y H:i', $t) : '—';
}

// ---------------------------------------------------------------- primeiro acesso
if (empty($cfg['senha_hash'])) {
    $inst = ler_json(DADOS . '/instalacao.json');
    $erro = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'instalar') {
        $codigo = trim((string)($_POST['codigo'] ?? ''));
        $s1 = (string)($_POST['senha'] ?? '');
        $s2 = (string)($_POST['senha2'] ?? '');
        if (bloqueado()) {
            $erro = 'Muitas tentativas. Espere 15 minutos.';
        } elseif (empty($inst['codigo_hash']) || !password_verify($codigo, $inst['codigo_hash'])) {
            registrar_falha();
            $erro = 'Código de instalação incorreto.';
        } elseif (mb_strlen($s1) < 10) {
            $erro = 'Use uma senha com pelo menos 10 caracteres.';
        } elseif ($s1 !== $s2) {
            $erro = 'As duas senhas estão diferentes.';
        } else {
            definir_senha($s1);
            @unlink(DADOS . '/instalacao.json');
            entrar($s1);
            flash('Senha criada. Bem-vindo ao painel.');
            ir();
        }
    }
    topo('Primeiro acesso'); ?>
  <section class="cartao estreito">
    <h1>Primeiro acesso</h1>
    <p class="suave">Crie a senha do painel. O código de instalação foi enviado junto com os arquivos.</p>
    <?php if ($erro): ?><p class="aviso aviso-erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
    <?php if (empty($inst['codigo_hash'])): ?>
      <p class="aviso aviso-erro">Falta o arquivo de instalação (_app/dados/instalacao.json). Envie de novo os arquivos do painel.</p>
    <?php else: ?>
    <form method="post" class="form-simples">
      <input type="hidden" name="acao" value="instalar">
      <label for="codigo">Código de instalação</label>
      <input id="codigo" name="codigo" autocomplete="off" required>
      <label for="senha">Nova senha (mínimo 10 caracteres)</label>
      <input id="senha" name="senha" type="password" autocomplete="new-password" minlength="10" required>
      <label for="senha2">Repita a senha</label>
      <input id="senha2" name="senha2" type="password" autocomplete="new-password" minlength="10" required>
      <button class="botao botao-principal" type="submit">Criar senha e entrar</button>
    </form>
    <?php endif; ?>
  </section>
<?php fim();
    exit;
}

// ---------------------------------------------------------------- login
if (!logado()) {
    $erro = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'entrar') {
        if (bloqueado()) {
            $erro = 'Muitas tentativas erradas. Espere 15 minutos.';
        } elseif (entrar((string)($_POST['senha'] ?? ''))) {
            ir();
        } else {
            $erro = bloqueado() ? 'Muitas tentativas erradas. Espere 15 minutos.' : 'Senha incorreta.';
        }
    }
    if ($acao === 'salvar' || $acao === 'foto' || $acao === 'remover_foto') {
        json_resposta(['ok' => false, 'erro' => 'Sua sessão expirou. Entre de novo em outra aba e salve outra vez.'], 401);
    }
    topo('Entrar'); ?>
  <section class="cartao estreito">
    <h1>Entrar no painel</h1>
    <?php if ($erro): ?><p class="aviso aviso-erro" role="alert"><?= e($erro) ?></p><?php endif; ?>
    <form method="post" class="form-simples">
      <input type="hidden" name="acao" value="entrar">
      <label for="senha">Senha</label>
      <input id="senha" name="senha" type="password" autocomplete="current-password" required autofocus>
      <button class="botao botao-principal" type="submit">Entrar</button>
    </form>
  </section>
<?php fim();
    exit;
}

// ---------------------------------------------------------------- ações (logado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $acao === 'exportar') {
    // pedidos JSON (salvar e remover foto) mandam o token no corpo
    $corpo = [];
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $bruto = (string)file_get_contents('php://input');
        if (strlen($bruto) > 2_000_000) json_resposta(['ok' => false, 'erro' => 'Ficha grande demais.'], 413);
        $corpo = json_decode($bruto, true) ?: [];
        $acao = (string)($corpo['acao'] ?? $acao);
    }
    $token = $corpo['csrf'] ?? $_POST['csrf'] ?? $_GET['csrf'] ?? null;
    if (!csrf_ok($token)) {
        if ($corpo || $acao === 'foto') json_resposta(['ok' => false, 'erro' => 'Página expirada. Recarregue e tente de novo.'], 403);
        flash('A página ficou aberta tempo demais. Tente de novo.', 'erro');
        ir();
    }

    switch ($acao) {
        case 'sair':
            sair();
            ir();

        case 'criar':
            $modelo = (string)($_POST['modelo'] ?? '');
            $nome = trim((string)($_POST['nome'] ?? ''));
            $slug = slugify((string)($_POST['slug'] ?? '') ?: $nome);
            $modelos = modelos_disponiveis();
            if (!isset($modelos[$modelo])) { flash('Escolha um modelo.', 'erro'); ir('p=novo'); }
            if ($nome === '') { flash('Informe o nome do negócio.', 'erro'); ir('p=novo'); }
            if (!slug_livre($slug)) { flash("O endereço \"$slug\" já está em uso ou não é permitido. Escolha outro.", 'erro'); ir('p=novo'); }
            $f = !empty($_POST['com_exemplo']) ? exemplo_modelo($modelo) : [];
            if (empty($_POST['com_exemplo'])) {
                $f = ['endereco' => ['cidade' => '', 'uf' => 'SP'], 'horarios' => [], 'faq' => [], 'seo' => ['titulo' => '', 'descricao' => '']];
            }
            if (!empty($_POST['com_exemplo'])) {
                // troca o nome do negócio fictício pelo nome do cliente em todos os textos de exemplo
                $trocas = array_filter([(string)($f['nome'] ?? '') => $nome, (string)($f['nome_curto'] ?? '') => $nome], fn($v, $k) => $k !== '', ARRAY_FILTER_USE_BOTH);
                array_walk_recursive($f, function (&$val) use ($trocas) { if (is_string($val)) $val = strtr($val, $trocas); });
            }
            $f['modelo'] = $modelo;
            $f['slug'] = $slug;
            $f['nome'] = $nome;
            $f['nome_curto'] = '';
            $f['situacao'] = in_array($_POST['situacao'] ?? '', array_keys(SITUACOES), true) ? $_POST['situacao'] : 'previa';
            $f['whatsapp'] = preg_replace('/\D/', '', (string)($_POST['whatsapp'] ?? '')) ?: ($f['whatsapp'] ?? '');
            $f['criado_em'] = date('c');
            $f['previa_desde'] = date('c');
            $f['fotos'] = [];
            if ($f['situacao'] === 'previa') {
                $f['proposta'] = ['ativa' => true, 'para' => '', 'valor' => '', 'condicao' => '', 'observacao' => ''];
            }
            if (!empty($_POST['com_exemplo'])) {
                $f['seo']['titulo'] = $nome;
            }
            ficha_salvar($f);
            publicar($f);
            flash('Site criado. Troque os textos de exemplo pelos dados do cliente e clique em Salvar e publicar.');
            ir('p=editar&slug=' . rawurlencode($slug));

        case 'salvar':
            $slug = (string)($corpo['slug'] ?? '');
            $atual = ficha_ler($slug);
            if (!$atual) json_resposta(['ok' => false, 'erro' => 'Site não encontrado.'], 404);
            $nova = $corpo['ficha'] ?? null;
            if (!is_array($nova)) json_resposta(['ok' => false, 'erro' => 'Dados inválidos.'], 400);
            if (trim((string)($nova['nome'] ?? '')) === '') json_resposta(['ok' => false, 'erro' => 'O nome do negócio não pode ficar vazio.'], 422);
            // campos que o formulário não pode trocar
            foreach (['slug', 'modelo', 'criado_em'] as $k) {
                $nova[$k] = $atual[$k] ?? null;
            }
            $nova['whatsapp'] = preg_replace('/\D/', '', (string)($nova['whatsapp'] ?? ''));
            if (!in_array($nova['situacao'] ?? '', array_keys(SITUACOES), true)) $nova['situacao'] = 'rascunho';
            if (($nova['situacao'] ?? '') === 'previa' && ($atual['situacao'] ?? '') !== 'previa') {
                $nova['previa_desde'] = date('c');
            } else {
                $nova['previa_desde'] = $atual['previa_desde'] ?? date('c');
            }
            try {
                ficha_salvar($nova);
                publicar(ficha_ler($slug));
            } catch (Throwable $ex) {
                json_resposta(['ok' => false, 'erro' => 'Salvo, mas não consegui publicar: ' . $ex->getMessage()], 500);
            }
            json_resposta(['ok' => true, 'quando' => date('H:i'), 'situacao' => chip_situacao(ficha_ler($slug))]);

        case 'foto':
            $slug = (string)($_POST['slug'] ?? '');
            if (!ficha_ler($slug)) json_resposta(['ok' => false, 'erro' => 'Site não encontrado.'], 404);
            try {
                $nome = salvar_foto($slug, $_FILES['foto'] ?? []);
            } catch (Throwable $ex) {
                json_resposta(['ok' => false, 'erro' => $ex->getMessage()], 422);
            }
            json_resposta(['ok' => true, 'arquivo' => $nome]);

        case 'remover_foto':
            $slug = (string)($corpo['slug'] ?? '');
            if (!ficha_ler($slug)) json_resposta(['ok' => false, 'erro' => 'Site não encontrado.'], 404);
            remover_foto($slug, (string)($corpo['arquivo'] ?? ''));
            json_resposta(['ok' => true]);

        case 'duplicar':
            $origem = ficha_ler((string)($_POST['slug'] ?? ''));
            if (!$origem) { flash('Site não encontrado.', 'erro'); ir(); }
            $base = $origem['slug'];
            $n = 2;
            do { $novo = substr($base, 0, 55) . '-' . $n++; } while (!slug_livre($novo));
            $f = $origem;
            $f['slug'] = $novo;
            $f['nome'] = $origem['nome'] . ' (cópia)';
            $f['situacao'] = 'rascunho';
            $f['criado_em'] = date('c');
            $f['previa_desde'] = date('c');
            ficha_salvar($f);
            $dirOrig = RAIZ . '/' . $base . '/fotos';
            if (is_dir($dirOrig)) {
                mkdir(RAIZ . "/$novo/fotos", 0755, true);
                foreach (glob("$dirOrig/*") ?: [] as $foto) copy($foto, RAIZ . "/$novo/fotos/" . basename($foto));
            }
            publicar($f);
            flash('Cópia criada como rascunho.');
            ir('p=editar&slug=' . rawurlencode($novo));

        case 'excluir':
            $f = ficha_ler((string)($_POST['slug'] ?? ''));
            if (!$f) { flash('Site não encontrado.', 'erro'); ir(); }
            if (($_POST['confirmo'] ?? '') !== $f['slug']) { flash('Para excluir, digite o endereço do site exatamente.', 'erro'); ir('p=excluir&slug=' . rawurlencode($f['slug'])); }
            rename(ficha_arquivo($f['slug']), LIXEIRA . '/' . $f['slug'] . '-' . date('Ymd-His') . '.json');
            remover_dir(RAIZ . '/' . $f['slug']);
            flash('Site "' . $f['nome'] . '" excluído. A ficha foi guardada na lixeira do servidor.');
            ir();

        case 'exportar':
            $f = ficha_ler((string)($_GET['slug'] ?? ''));
            if (!$f) { flash('Site não encontrado.', 'erro'); ir(); }
            if (empty($f['dominio'])) { flash('Preencha "Domínio do cliente" na seção Google e domínio antes de exportar.', 'erro'); ir('p=editar&slug=' . rawurlencode($f['slug']) . '#sec-google'); }
            try { $zip = exportar_zip($f); } catch (Throwable $ex) { flash($ex->getMessage(), 'erro'); ir(); }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $f['slug'] . '.zip"');
            header('Content-Length: ' . filesize($zip));
            readfile($zip);
            unlink($zip);
            exit;

        case 'publicar_todos':
            $n = 0;
            foreach (fichas_listar() as $f) { try { publicar($f); $n++; } catch (Throwable) {} }
            flash("$n sites publicados de novo com a versão atual dos modelos.");
            ir();

        case 'config':
            $c = config();
            $c['wgl_whatsapp'] = preg_replace('/\D/', '', (string)($_POST['wgl_whatsapp'] ?? ''));
            $site = trim((string)($_POST['wgl_site'] ?? ''));
            $c['wgl_site'] = preg_match('#^https?://#', $site) ? $site : 'https://wglsoftware.com';
            $c['nome_galeria'] = trim((string)($_POST['nome_galeria'] ?? '')) ?: 'Modelos de site';
            $c['texto_galeria'] = trim((string)($_POST['texto_galeria'] ?? ''));
            $valor = str_replace(',', '.', trim((string)($_POST['prop_valor'] ?? '')));
            $c['proposta'] = [
                'plano'      => trim((string)($_POST['prop_plano'] ?? '')),
                'valor'      => is_numeric($valor) ? (float)$valor : '',
                'condicao'   => trim((string)($_POST['prop_condicao'] ?? '')),
                'inclui'     => array_values(array_filter(array_map('trim', explode("\n", (string)($_POST['prop_inclui'] ?? ''))), fn($x) => $x !== '')),
                'observacao' => trim((string)($_POST['prop_observacao'] ?? '')),
            ];
            salvar_config($c);
            $nova = (string)($_POST['nova_senha'] ?? '');
            if ($nova !== '') {
                if (!password_verify((string)($_POST['senha_atual'] ?? ''), $c['senha_hash'])) { flash('Senha atual incorreta. As outras configurações foram salvas.', 'erro'); ir('p=config'); }
                if (mb_strlen($nova) < 10) { flash('A nova senha precisa de pelo menos 10 caracteres.', 'erro'); ir('p=config'); }
                definir_senha($nova);
                flash('Configurações e senha atualizadas.');
                ir('p=config');
            }
            flash('Configurações salvas.');
            ir('p=config');
    }
    flash('Ação desconhecida.', 'erro');
    ir();
}

// ---------------------------------------------------------------- páginas
$modelos = modelos_disponiveis();

if ($p === 'novo') {
    topo('Novo site', 'novo'); ?>
  <div class="cab-pagina"><h1>Novo site</h1></div>
  <form method="post" class="cartao form-novo" id="form-novo">
    <input type="hidden" name="acao" value="criar">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <fieldset>
      <legend>1. Modelo</legend>
      <div class="escolhas">
        <?php $i = 0; foreach ($modelos as $m): ?>
        <label class="escolha">
          <input type="radio" name="modelo" value="<?= e($m['id']) ?>"<?= $i++ === 0 ? ' checked' : '' ?>>
          <span class="escolha-nome"><?= e($m['nome']) ?></span>
          <span class="escolha-nichos"><?= e($m['nichos']) ?></span>
          <span class="escolha-desc"><?= e($m['descricao']) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <fieldset>
      <legend>2. Negócio</legend>
      <div class="grade-2">
        <div class="campo"><label for="nome">Nome do negócio</label><input id="nome" name="nome" required placeholder="Ex.: Barbearia do João"></div>
        <div class="campo"><label for="slug">Endereço</label>
          <div class="prefixo"><span><?= e(preg_replace('#^https?://#', '', url_base())) ?></span><input id="slug" name="slug" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="60" placeholder="barbearia-do-joao"></div>
          <small>Só letras minúsculas, números e hífen. Não dá para mudar depois.</small></div>
        <div class="campo"><label for="whatsapp">WhatsApp do cliente (opcional)</label><input id="whatsapp" name="whatsapp" inputmode="numeric" placeholder="5511987654321"></div>
        <div class="campo"><label for="situacao">Situação</label>
          <select id="situacao" name="situacao"><?php foreach (SITUACOES as $k => $t): ?><option value="<?= e($k) ?>"<?= $k === 'previa' ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
      </div>
      <label class="check"><input type="checkbox" name="com_exemplo" value="1" checked> Começar com os textos de exemplo do modelo (recomendado)</label>
    </fieldset>
    <div class="acoes-form"><button class="botao botao-principal" type="submit">Criar site</button><a class="botao" href="./">Cancelar</a></div>
  </form>
<?php fim('<script>
(function(){var n=document.getElementById("nome"),s=document.getElementById("slug"),mexeu=false;
s.addEventListener("input",function(){mexeu=true});
n.addEventListener("input",function(){if(mexeu)return;s.value=n.value.toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g,"").replace(/&/g," e ").replace(/[^a-z0-9]+/g,"-").replace(/^-+|-+$/g,"").slice(0,60)});})();
</script>');
    exit;
}

if ($p === 'editar') {
    $f = ficha_ler((string)($_GET['slug'] ?? ''));
    if (!$f) { flash('Site não encontrado.', 'erro'); ir(); }
    $schema = schema_modelo($f['modelo']);
    $urlSite = url_base() . $f['slug'] . '/';
    topo('Editar ' . $f['nome'], 'lista');
    $dados = json_seguro(['ficha' => $f, 'schema' => $schema, 'csrf' => csrf_token(), 'slug' => $f['slug'], 'url_fotos' => '../' . $f['slug'] . '/fotos/'], false); ?>
  <div class="cab-pagina cab-editor">
    <div>
      <p class="migalha"><a href="./">Sites</a> / <?= e($modelos[$f['modelo']]['nome'] ?? $f['modelo']) ?></p>
      <h1 id="titulo-site"><?= e($f['nome']) ?></h1>
      <p class="linha-info"><span id="chip-situacao"><?= chip_situacao($f) ?></span> <a href="<?= e($urlSite) ?>" target="_blank" rel="noopener" class="mono"><?= e(preg_replace('#^https?://#', '', $urlSite)) ?></a></p>
    </div>
    <div class="acoes-editor">
      <span class="estado" id="estado" role="status" aria-live="polite">Salvo <?= e(data_br($f['atualizado_em'] ?? null)) ?></span>
      <a class="botao" href="<?= e($urlSite) ?>" target="_blank" rel="noopener">Abrir site ↗</a>
      <button class="botao botao-principal" type="button" id="salvar">Salvar e publicar</button>
    </div>
  </div>
  <div class="editor">
    <nav class="indice" id="indice" aria-label="Seções do formulário"></nav>
    <form id="ficha" class="ficha" novalidate></form>
  </div>
  <script type="application/json" id="dados-editor"><?= $dados ?></script>
<?php fim('<script src="painel.js?v=' . VERSAO . '"></script>');
    exit;
}

if ($p === 'excluir') {
    $f = ficha_ler((string)($_GET['slug'] ?? ''));
    if (!$f) { flash('Site não encontrado.', 'erro'); ir(); }
    topo('Excluir ' . $f['nome'], 'lista'); ?>
  <section class="cartao estreito">
    <h1>Excluir “<?= e($f['nome']) ?>”?</h1>
    <p>O site sai do ar em <span class="mono"><?= e($f['slug']) ?></span> e as fotos são apagadas. A ficha fica guardada na lixeira do servidor.</p>
    <form method="post" class="form-simples">
      <input type="hidden" name="acao" value="excluir"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="slug" value="<?= e($f['slug']) ?>">
      <label for="confirmo">Para confirmar, digite <b class="mono"><?= e($f['slug']) ?></b></label>
      <input id="confirmo" name="confirmo" autocomplete="off" required>
      <div class="acoes-form"><button class="botao botao-perigo" type="submit">Excluir site</button><a class="botao" href="./">Cancelar</a></div>
    </form>
  </section>
<?php fim();
    exit;
}

if ($p === 'config') {
    $c = config();
    topo('Configurações', 'config'); ?>
  <div class="cab-pagina"><h1>Configurações</h1></div>
  <form method="post" class="cartao form-simples largo">
    <input type="hidden" name="acao" value="config"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <h2>Galeria e contato da WGL</h2>
    <label for="wgl_whatsapp">WhatsApp da WGL (só números, com 55 e DDD)</label>
    <input id="wgl_whatsapp" name="wgl_whatsapp" value="<?= e($c['wgl_whatsapp'] ?? '') ?>" inputmode="numeric" placeholder="5511987654321">
    <small>Usado no botão “Quero um site assim” da galeria e na faixa das prévias.</small>
    <label for="wgl_site">Site principal</label>
    <input id="wgl_site" name="wgl_site" value="<?= e($c['wgl_site'] ?? 'https://wglsoftware.com') ?>">
    <label for="nome_galeria">Título da galeria</label>
    <input id="nome_galeria" name="nome_galeria" value="<?= e($c['nome_galeria'] ?? 'Modelos de site') ?>">
    <label for="texto_galeria">Texto de abertura da galeria</label>
    <textarea id="texto_galeria" name="texto_galeria" rows="3"><?= e($c['texto_galeria'] ?? '') ?></textarea>
    <h2>Proposta padrão</h2>
    <p class="suave">Aparece na página de proposta de cada prévia. Cada site pode trocar o valor e a condição.</p>
    <?php $PP = proposta_padrao(); ?>
    <label for="prop_plano">Nome do plano</label>
    <input id="prop_plano" name="prop_plano" value="<?= e($PP['plano']) ?>">
    <label for="prop_valor">Valor mensal (R$)</label>
    <input id="prop_valor" name="prop_valor" inputmode="decimal" value="<?= e((string)$PP['valor']) ?>">
    <label for="prop_condicao">Condição</label>
    <input id="prop_condicao" name="prop_condicao" value="<?= e($PP['condicao']) ?>">
    <label for="prop_inclui">O que está incluso (um por linha)</label>
    <textarea id="prop_inclui" name="prop_inclui" rows="8"><?= e(implode("\n", (array)$PP['inclui'])) ?></textarea>
    <label for="prop_observacao">Observação</label>
    <textarea id="prop_observacao" name="prop_observacao" rows="2"><?= e($PP['observacao']) ?></textarea>
    <h2>Trocar senha</h2>
    <label for="senha_atual">Senha atual</label>
    <input id="senha_atual" name="senha_atual" type="password" autocomplete="current-password">
    <label for="nova_senha">Nova senha (mínimo 10 caracteres)</label>
    <input id="nova_senha" name="nova_senha" type="password" autocomplete="new-password" minlength="10">
    <div class="acoes-form"><button class="botao botao-principal" type="submit">Salvar configurações</button></div>
  </form>
  <form method="post" class="cartao form-simples largo">
    <input type="hidden" name="acao" value="publicar_todos"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <h2>Atualizar todos os sites</h2>
    <p class="suave">Use depois de atualizar os arquivos dos modelos: gera de novo o HTML de todos os sites.</p>
    <div class="acoes-form"><button class="botao" type="submit">Publicar todos de novo</button></div>
  </form>
  <p class="suave versao">Painel WGL Modelos <?= e(VERSAO) ?> · PHP <?= e(PHP_VERSION) ?></p>
<?php fim();
    exit;
}

// ---------------------------------------------------------------- lista (padrão)
$fichas = fichas_listar();
$filtro = (string)($_GET['f'] ?? 'todos');
$busca = trim((string)($_GET['q'] ?? ''));
$contagem = ['todos' => count($fichas)];
foreach ($fichas as $f) { $s = $f['situacao'] ?? 'rascunho'; $contagem[$s] = ($contagem[$s] ?? 0) + 1; }
$visiveis = array_filter($fichas, function ($f) use ($filtro, $busca) {
    if ($filtro !== 'todos' && ($f['situacao'] ?? '') !== $filtro) return false;
    if ($busca !== '' && mb_stripos(($f['nome'] ?? '') . ' ' . $f['slug'], $busca) === false) return false;
    return true;
});
topo('Sites', 'lista'); ?>
  <div class="cab-pagina">
    <h1>Sites</h1>
    <a class="botao botao-principal" href="./?p=novo">+ Novo site</a>
  </div>
  <div class="filtros">
    <nav class="abas" aria-label="Filtrar por situação">
      <?php foreach (['todos' => 'Todos'] + SITUACOES as $k => $t): if ($k !== 'todos' && empty($contagem[$k])) continue; ?>
      <a href="./?f=<?= e($k) ?>"<?= $filtro === $k ? ' aria-current="page"' : '' ?>><?= e($t) ?> <span><?= (int)($contagem[$k] ?? 0) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <form class="busca" method="get"><input type="hidden" name="f" value="<?= e($filtro) ?>"><label class="sr" for="q">Buscar</label><input id="q" name="q" type="search" value="<?= e($busca) ?>" placeholder="Buscar por nome"></form>
  </div>
  <?php if (!$fichas): ?>
    <section class="cartao vazio">
      <h2>Nenhum site ainda</h2>
      <p>Crie o primeiro a partir de um modelo. Leva uns 10 minutos para virar a prévia de um cliente.</p>
      <a class="botao botao-principal" href="./?p=novo">Criar o primeiro site</a>
    </section>
  <?php elseif (!$visiveis): ?>
    <p class="suave">Nenhum site com esse filtro.</p>
  <?php else: ?>
  <div class="tabela-rolagem">
  <table class="tabela">
    <thead><tr><th>Site</th><th>Modelo</th><th>Situação</th><th>Atualizado</th><th><span class="sr">Ações</span></th></tr></thead>
    <tbody>
    <?php foreach ($visiveis as $f): $u = url_base() . $f['slug'] . '/'; ?>
      <tr>
        <td><a class="nome-site" href="./?p=editar&amp;slug=<?= e(rawurlencode($f['slug'])) ?>"><?= e($f['nome']) ?></a><span class="mono suave">/<?= e($f['slug']) ?></span></td>
        <td><?= e($modelos[$f['modelo']]['nome'] ?? $f['modelo']) ?></td>
        <td><?= chip_situacao($f) ?></td>
        <td class="num"><?= e(data_br($f['atualizado_em'] ?? null)) ?></td>
        <td class="acoes-linha">
          <a href="./?p=editar&amp;slug=<?= e(rawurlencode($f['slug'])) ?>">Editar</a>
          <a href="<?= e($u) ?>" target="_blank" rel="noopener">Abrir ↗</a>
          <button type="button" class="copiar" data-url="<?= e($u) ?>">Copiar link</button>
          <details class="mais"><summary>Mais</summary>
            <div class="menu-mais">
              <form method="post"><input type="hidden" name="acao" value="duplicar"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="slug" value="<?= e($f['slug']) ?>"><button type="submit">Duplicar</button></form>
              <?php if (!empty($f['proposta']['ativa'])): ?><a href="<?= e($u) ?>proposta.html" target="_blank" rel="noopener">Abrir a proposta ↗</a><?php endif; ?>
              <a href="./?acao=exportar&amp;slug=<?= e(rawurlencode($f['slug'])) ?>&amp;csrf=<?= e(csrf_token()) ?>">Exportar .zip para o domínio do cliente</a>
              <a class="perigo" href="./?p=excluir&amp;slug=<?= e(rawurlencode($f['slug'])) ?>">Excluir</a>
            </div>
          </details>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
<?php fim('<script>
document.querySelectorAll(".copiar").forEach(function(b){b.addEventListener("click",function(){
  var u=b.dataset.url, ok=function(){var t=b.textContent;b.textContent="Copiado";setTimeout(function(){b.textContent=t},1500)};
  if(navigator.clipboard){navigator.clipboard.writeText(u).then(ok,function(){prompt("Copie o link:",u)})}else{prompt("Copie o link:",u)}
})});
</script>');
