<?php
/** Galeria pública — modelos.wglsoftware.com. Mostra só sites em Portfólio ou Demonstração (prévias nunca aparecem). */
declare(strict_types=1);
require __DIR__ . '/_app/nucleo.php';
header('X-Content-Type-Options: nosniff');

$cfg = config();
$modelos = modelos_disponiveis();
$sites = array_filter(fichas_listar(), fn($f) => in_array($f['situacao'] ?? '', ['portfolio', 'demonstracao'], true) && is_file(RAIZ . '/' . $f['slug'] . '/index.html'));
// portfólio (clientes reais) primeiro, depois demonstrações; dentro de cada um, mais recentes primeiro
uasort($sites, fn($a, $b) => [($a['situacao'] === 'portfolio' ? 0 : 1), $b['atualizado_em'] ?? ''] <=> [($b['situacao'] === 'portfolio' ? 0 : 1), $a['atualizado_em'] ?? '']);
$usados = array_unique(array_map(fn($f) => $f['modelo'], $sites));
$wglSite = $cfg['wgl_site'] ?: 'https://wglsoftware.com';
$wglZap = preg_replace('/\D/', '', (string)($cfg['wgl_whatsapp'] ?? ''));
$contato = fn(string $msg) => $wglZap ? 'https://wa.me/' . $wglZap . '?text=' . rawurlencode($msg) : $wglSite;
$titulo = $cfg['nome_galeria'] ?? '' ?: 'Modelos de site';
$texto = $cfg['texto_galeria'] ?? '' ?: 'Sites para pequenos negócios, prontos em 48 horas: com WhatsApp em todos os botões, preparados para o Google e feitos para abrir rápido no celular. Escolha um modelo e veja como ficaria o seu.';
$url = url_base();
// Sistemas sob medida que a WGL já construiu (código aberto no GitHub, com dados de demonstração)
$sistemas = [
    ['id' => 'cardapio-digital', 'etiqueta' => 'Restaurantes e delivery', 'nome' => 'Cardápio digital com pedido no WhatsApp',
     'texto' => 'O cliente monta o pedido pelo celular e envia pronto no WhatsApp da loja. O dono atualiza produtos, fotos, preços e horários num painel simples.',
     'itens' => ['Carrinho com entrega ou retirada', 'Painel com login e fotos otimizadas', 'Funciona sem app e sem mensalidade de plataforma']],
    ['id' => 'gestao-confeitaria', 'etiqueta' => 'Confeitarias e produção artesanal', 'nome' => 'Gestão de produção, estoque e caixa',
     'texto' => 'Ficha técnica com custo real de cada receita, baixa automática de estoque na produção, vendas, despesas e fluxo de caixa com lucro por produto.',
     'itens' => ['Custo e margem por receita', 'Alerta de estoque baixo', 'Caixa do mês exportável em CSV']],
    ['id' => 'crm-cobranca', 'etiqueta' => 'Call center e cobrança', 'nome' => 'CRM de cobrança para operação de call center',
     'texto' => 'Sistema usado em produção numa operação real: cargas do credor, filas de operadores, acionamentos, acordos e boletos integrados por API, dashboards de supervisão.',
     'itens' => ['Integração com a API do credor', 'API REST documentada', 'Busca e dashboards em tempo real']],
    ['id' => 'plataforma-ead', 'etiqueta' => 'Escolas, institutos e treinamentos', 'nome' => 'Plataforma de cursos online com certificado',
     'texto' => 'Aulas em vídeo, materiais e avaliações. O aluno continua de onde parou e, ao concluir, recebe o certificado em PDF com QR Code que qualquer empresa confere online.',
     'itens' => ['Mede o tempo realmente assistido', 'Certificado com validação pública', 'Painel com relatórios e permissões'],
     'demo' => 'https://plataforma-ead.wglsoftware.com'],
];
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($titulo) ?> para pequenos negócios | WGL Software</title>
<meta name="description" content="<?= e(mb_substr($texto, 0, 158)) ?>">
<link rel="canonical" href="<?= e($url) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:title" content="<?= e($titulo) ?> · WGL Software">
<meta property="og:description" content="<?= e(mb_substr($texto, 0, 158)) ?>">
<meta property="og:url" content="<?= e($url) ?>">
<meta property="og:image" content="https://wglsoftware.com/images/og-image.jpg">
<meta name="theme-color" content="#13201a">
<link rel="icon" href="/assets/wgl-logo.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap">
<style>
/* Layout: cabeçalho escuro da WGL, grade de "janelas" com o site real rodando em miniatura, filtro por nicho */
:root{
  --fundo:#f3f5f2; --superficie:#ffffff; --tinta:#13201a; --suave:#56645d; --linha:#dbe2dc;
  --verde:#0f7b5b; --verde-escuro:#0a5e45; --lima:#c6f135;
  --titulo:"Space Grotesk","Segoe UI",system-ui,sans-serif;
  --texto:"IBM Plex Sans",system-ui,-apple-system,"Segoe UI",sans-serif;
  --mono:"JetBrains Mono",ui-monospace,Consolas,monospace;
  color-scheme:light;
}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:var(--fundo);color:var(--tinta);font:16px/1.6 var(--texto);-webkit-font-smoothing:antialiased}
h1,h2,h3{font-family:var(--titulo);letter-spacing:-.02em;line-height:1.08;margin:0;text-wrap:balance}
p{margin:0}
a{color:inherit}
:focus-visible{outline:3px solid var(--lima);outline-offset:3px;border-radius:4px}
.largura{max-width:76rem;margin-inline:auto;padding-inline:clamp(1rem,4vw,2rem)}

.topo{background:var(--tinta);color:#fff}
.topo-linha{display:flex;align-items:center;justify-content:space-between;gap:1rem;min-height:4rem}
.marca{display:flex;align-items:center;gap:.5rem;text-decoration:none;font:700 1.0625rem/1 var(--titulo)}
.marca img{height:1.375rem;width:auto;display:block}
.topo nav{display:flex;gap:1.25rem;align-items:center;font-size:.9375rem}
.topo nav a{text-decoration:none;color:#cfd8d3}
.topo nav a:hover{color:#fff}
.cta{display:inline-flex;align-items:center;gap:.5rem;background:var(--lima);color:var(--tinta)!important;font:600 .9375rem/1 var(--titulo);padding:.75rem 1.125rem;border-radius:8px;text-decoration:none;white-space:nowrap}
.cta:hover{filter:brightness(.94)}

.abertura{background:var(--tinta);color:#fff;padding-block:clamp(2.5rem,7vw,4.5rem) clamp(3rem,7vw,5rem)}
.abertura .rotulo{font:500 .8125rem/1 var(--mono);color:var(--lima);letter-spacing:.06em;text-transform:uppercase}
.abertura h1{font-size:clamp(2.25rem,6vw,4rem);margin-top:1rem;max-width:18ch}
.abertura p{margin-top:1.25rem;color:#c3cdc7;font-size:1.125rem;max-width:58ch}
.abertura .acoes{display:flex;flex-wrap:wrap;gap:.75rem 1.5rem;align-items:center;margin-top:2rem}
.abertura .acoes a:not(.cta){color:#fff;font-weight:600;text-underline-offset:.25em}
.inclui{list-style:none;padding:0;margin:2.5rem 0 0;display:flex;flex-wrap:wrap;gap:.5rem}
.inclui li{border:1px solid rgba(255,255,255,.18);border-radius:99px;padding:.375rem .875rem;font-size:.875rem;color:#dfe6e2}

.galeria{padding-block:clamp(2rem,5vw,3.5rem) 4rem}
.filtros{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.75rem}
.filtros button{font:600 .875rem/1 var(--texto);padding:.5rem .875rem;border-radius:99px;border:1px solid var(--linha);background:var(--superficie);color:var(--suave);cursor:pointer}
.filtros button[aria-pressed="true"]{background:var(--tinta);border-color:var(--tinta);color:#fff}
.grade{list-style:none;margin:0;padding:0;display:grid;gap:clamp(1.25rem,3vw,2rem);grid-template-columns:repeat(auto-fill,minmax(min(100%,21rem),1fr))}
.site{display:grid;gap:.875rem;align-content:start}
.site[hidden]{display:none}
.janela{border-radius:10px;overflow:hidden;background:var(--superficie);border:1px solid var(--linha);box-shadow:0 18px 36px -26px rgba(19,32,26,.45);transition:transform .2s ease,box-shadow .2s ease}
.site:hover .janela{transform:translateY(-3px);box-shadow:0 24px 44px -26px rgba(19,32,26,.55)}
.janela-barra{display:flex;align-items:center;gap:.375rem;padding:.5rem .75rem;border-bottom:1px solid var(--linha);background:#f7f8f6}
.janela-barra span{width:.5rem;height:.5rem;border-radius:50%;background:#d5dbd6}
.janela-barra code{margin-left:.5rem;font:500 .6875rem/1 var(--mono);color:var(--suave);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tela{position:relative;aspect-ratio:16/10;overflow:hidden;background:#eef1ee}
.tela iframe{position:absolute;top:0;left:0;width:1280px;height:800px;border:0;transform-origin:0 0;transform:scale(var(--escala,.27));pointer-events:none}
.tela a{position:absolute;inset:0;z-index:1}
.info{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem}
.info h2{font-size:1.25rem}
.etiqueta{font:500 .75rem/1 var(--mono);color:var(--verde-escuro);text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:.375rem}
.nichos{font-size:.875rem;color:var(--suave);margin-top:.25rem}
.links{display:flex;flex-wrap:wrap;gap:.25rem 1rem;font-size:.9375rem;font-weight:600}
.links a{color:var(--verde-escuro);text-underline-offset:.2em}
.vazio{padding:3rem 1.5rem;text-align:center;border:1px dashed var(--linha);border-radius:10px;color:var(--suave)}

.final{background:var(--superficie);border-top:1px solid var(--linha)}
.final .largura{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.25rem;padding-block:2.5rem}
.final h2{font-size:clamp(1.5rem,3.5vw,2rem);max-width:26ch}
.final .cta{background:var(--verde);color:#fff!important}
.rodape{padding-block:1.5rem calc(1.5rem + env(safe-area-inset-bottom,0px));font-size:.875rem;color:var(--suave)}
.rodape .largura{display:flex;flex-wrap:wrap;justify-content:space-between;gap:.5rem}
.sistemas{background:var(--tinta);color:#fff;padding-block:clamp(2.5rem,6vw,4.5rem)}
.sistemas .rotulo{font:500 .8125rem/1 var(--mono);color:var(--lima);letter-spacing:.06em;text-transform:uppercase}
.sistemas>.largura>h2{font-size:clamp(1.75rem,4.5vw,2.75rem);margin-top:.875rem;max-width:22ch}
.sistemas .sub{margin-top:1rem;color:#c3cdc7;max-width:60ch}
.sis-lista{list-style:none;margin:2.25rem 0 0;padding:0;display:grid;gap:clamp(1.25rem,3vw,2rem);grid-template-columns:repeat(auto-fill,minmax(min(100%,24rem),1fr))}
.sis{display:grid;grid-template-rows:auto 1fr;background:#1b2b23;border:1px solid rgba(255,255,255,.1);border-radius:12px;overflow:hidden}
.sis img{display:block;width:100%;height:auto;aspect-ratio:16/10;object-fit:cover;object-position:top left;border-bottom:1px solid rgba(255,255,255,.1)}
.sis-corpo{display:flex;flex-direction:column;gap:.75rem;padding:1.25rem 1.25rem 1.5rem}
.sis .etiqueta{color:var(--lima);margin:0}
.sis h3{font-size:1.25rem}
.sis p{color:#c3cdc7;font-size:.9375rem}
.sis ul{margin:0;padding:0;list-style:none;display:grid;gap:.375rem;font-size:.875rem;color:#dfe6e2}
.sis ul li{padding-left:1.25rem;position:relative}
.sis ul li::before{content:"";position:absolute;left:0;top:.55em;width:.5rem;height:.5rem;border-radius:2px;background:var(--lima)}
.sis .links{margin-top:auto;padding-top:.5rem}
.sis .links a{color:#fff}
@media (max-width:40rem){.topo nav a:not(.cta){display:none}}
@media (prefers-reduced-motion:reduce){.janela{transition:none}}
</style>
</head>
<body>
<header class="topo">
  <div class="largura topo-linha">
    <a class="marca" href="<?= e($wglSite) ?>"><img src="/assets/wgl-logo.svg" alt="" width="40" height="21">WGL Software</a>
    <nav aria-label="Principal">
      <a href="#modelos">Sites</a>
      <a href="#sistemas">Sistemas</a>
      <a href="<?= e($wglSite) ?>">Site da WGL</a>
      <a class="cta" href="<?= e($contato('Olá! Vi os modelos de site e quero um para o meu negócio.')) ?>" target="_blank" rel="noopener">Quero um site</a>
    </nav>
  </div>
</header>

<section class="abertura">
  <div class="largura">
    <span class="rotulo">WGL Software · modelos</span>
    <h1><?= e($titulo) ?> para pequenos negócios</h1>
    <p><?= e($texto) ?></p>
    <div class="acoes">
      <a class="cta" href="<?= e($contato('Olá! Vi os modelos de site e quero um para o meu negócio.')) ?>" target="_blank" rel="noopener">Quero um site assim</a>
      <a href="#modelos">Ver os modelos</a>
      <a href="#sistemas">Precisa de um sistema?</a>
    </div>
    <ul class="inclui">
      <li>Pronto em 48 horas</li><li>Domínio próprio</li><li>WhatsApp em todos os botões</li><li>Preparado para o Google</li><li>Feito para celular</li>
    </ul>
  </div>
</section>

<main class="galeria largura" id="modelos">
  <?php if (count($usados) > 1): ?>
  <div class="filtros" role="group" aria-label="Filtrar por tipo de negócio">
    <button type="button" aria-pressed="true" data-filtro="">Todos</button>
    <?php foreach ($modelos as $m): if (!in_array($m['id'], $usados, true)) continue; ?>
    <button type="button" aria-pressed="false" data-filtro="<?= e($m['id']) ?>"><?= e($m['nome']) ?></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!$sites): ?>
    <p class="vazio">Os modelos estão sendo preparados. Volte em breve.</p>
  <?php else: ?>
  <ul class="grade">
    <?php foreach ($sites as $f): $u = $f['slug'] . '/'; $m = $modelos[$f['modelo']] ?? ['nome' => $f['modelo'], 'nichos' => '']; ?>
    <li class="site" data-modelo="<?= e($f['modelo']) ?>">
      <div class="janela">
        <div class="janela-barra" aria-hidden="true"><span></span><span></span><span></span><code><?= e(preg_replace('#^https?://#', '', $url) . $f['slug']) ?></code></div>
        <div class="tela">
          <iframe src="<?= e($u) ?>" title="Miniatura do site <?= e($f['nome']) ?>" loading="lazy" tabindex="-1" aria-hidden="true" scrolling="no"></iframe>
          <a href="<?= e($u) ?>" aria-label="Abrir o site <?= e($f['nome']) ?>"></a>
        </div>
      </div>
      <div class="info">
        <div>
          <span class="etiqueta"><?= $f['situacao'] === 'portfolio' ? 'Cliente' : 'Modelo ' . e(mb_strtolower($m['nome'])) ?></span>
          <h2><?= e($f['nome']) ?></h2>
          <p class="nichos"><?= e($m['nichos']) ?></p>
        </div>
      </div>
      <p class="links"><a href="<?= e($u) ?>">Ver o site</a><a href="<?= e($contato('Olá! Vi o modelo "' . $f['nome'] . '" e quero um site assim para o meu negócio.')) ?>" target="_blank" rel="noopener">Quero um assim</a></p>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</main>

<section class="sistemas" id="sistemas" aria-labelledby="sistemas-titulo">
  <div class="largura">
    <span class="rotulo">Além do site</span>
    <h2 id="sistemas-titulo">Sistemas sob medida para o dia a dia do negócio</h2>
    <p class="sub">Quando planilha e caderno não dão mais conta, a WGL desenvolve o sistema. Estes são alguns que já construímos: o código está aberto no GitHub para quem quiser conferir como é feito.</p>
    <ul class="sis-lista">
      <?php foreach ($sistemas as $s): ?>
      <li class="sis">
        <img src="/assets/sistemas/<?= e($s['id']) ?>.webp" alt="Tela do sistema: <?= e($s['nome']) ?>" width="960" height="600" loading="lazy">
        <div class="sis-corpo">
          <span class="etiqueta"><?= e($s['etiqueta']) ?></span>
          <h3><?= e($s['nome']) ?></h3>
          <p><?= e($s['texto']) ?></p>
          <ul><?php foreach ($s['itens'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
          <p class="links"><?php if (!empty($s['demo'])): ?><a href="<?= e($s['demo']) ?>" target="_blank" rel="noopener">Ver funcionando</a><?php endif; ?><a href="https://github.com/wagnerglongo/<?= e($s['id']) ?>" target="_blank" rel="noopener">Ver o código</a><a href="<?= e($contato('Olá! Vi o sistema "' . $s['nome'] . '" e quero algo parecido para o meu negócio.')) ?>" target="_blank" rel="noopener">Quero um sistema assim</a></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="final">
  <div class="largura">
    <h2>Não achou o seu tipo de negócio? A gente monta o site ou o sistema para você.</h2>
    <a class="cta" href="<?= e($contato('Olá! Quero um site para o meu negócio. Vocês fazem para o meu ramo?')) ?>" target="_blank" rel="noopener">Falar com a WGL</a>
  </div>
</section>
<footer class="rodape"><div class="largura"><span>© <?= date('Y') ?> WGL Software</span><a href="<?= e($wglSite) ?>"><?= e(preg_replace('#^https?://#', '', $wglSite)) ?></a></div></footer>

<script>
(function(){
  // ajusta a miniatura do site ao tamanho do cartão
  function escala(){document.querySelectorAll('.tela').forEach(function(t){t.style.setProperty('--escala',(t.clientWidth/1280).toFixed(4))})}
  escala(); window.addEventListener('resize',escala);
  if('ResizeObserver' in window){var ro=new ResizeObserver(escala);document.querySelectorAll('.tela').forEach(function(t){ro.observe(t)})}
  var bts=document.querySelectorAll('.filtros button');
  bts.forEach(function(b){b.addEventListener('click',function(){
    bts.forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false')});
    var f=b.dataset.filtro;
    document.querySelectorAll('.site').forEach(function(s){s.hidden=!!f&&s.dataset.modelo!==f});
  })});
})();
</script>
</body>
</html>
