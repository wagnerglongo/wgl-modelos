<?php
/** Página de proposta da WGL para o dono do negócio. Variáveis: $f, $P (proposta), $url, $wglSite, $wglZap, $favicon. */
$meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
$dataExtenso = fn(int $t) => (int)date('j', $t) . ' de ' . $meses[(int)date('n', $t) - 1] . ' de ' . date('Y', $t);
$msgAceite = 'Olá! Vi a prévia do site de ' . ($f['nome'] ?? '') . ' (' . $url . ') e quero seguir com o ' . $P['plano'] . '.';
$msgDuvida = 'Olá! Vi a prévia do site de ' . ($f['nome'] ?? '') . ' e tenho uma dúvida.';
$zapWgl = fn(string $m) => $wglZap ? 'https://wa.me/' . $wglZap . '?text=' . rawurlencode($m) : $wglSite;
$inclui = array_values(array_filter((array)$P['inclui'], fn($x) => trim((string)$x) !== ''));
$passos = array_values(array_filter((array)$P['passos'], fn($x) => is_array($x) && trim((string)($x[0] ?? '')) !== ''));
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Proposta para <?= e($f['nome'] ?? '') ?> · WGL Software</title>
<meta name="robots" content="noindex, nofollow">
<meta name="description" content="Proposta da WGL Software para o site da <?= e($f['nome'] ?? '') ?>.">
<meta property="og:title" content="Proposta para <?= e($f['nome'] ?? '') ?>">
<meta property="og:description" content="Veja a prévia do seu site e a proposta da WGL Software.">
<link rel="icon" href="/assets/wgl-logo.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap">
<style>
/* Layout: cabeçalho escuro da WGL com a saudação; ao lado da prévia em miniatura fica o cartão do plano; passos em linha; uma única chamada para aceitar */
:root{--fundo:#f3f5f2;--superficie:#fff;--tinta:#13201a;--suave:#56645d;--linha:#dbe2dc;--verde:#0f7b5b;--verde-escuro:#0a5e45;--lima:#c6f135;
--titulo:"Space Grotesk","Segoe UI",system-ui,sans-serif;--texto:"IBM Plex Sans",system-ui,-apple-system,"Segoe UI",sans-serif;--mono:"JetBrains Mono",ui-monospace,Consolas,monospace;color-scheme:light}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:var(--fundo);color:var(--tinta);font:16px/1.6 var(--texto);-webkit-font-smoothing:antialiased}
h1,h2,h3{font-family:var(--titulo);letter-spacing:-.02em;line-height:1.1;margin:0;text-wrap:balance}
p{margin:0}
a{color:inherit}
:focus-visible{outline:3px solid var(--lima);outline-offset:3px;border-radius:4px}
.largura{max-width:72rem;margin-inline:auto;padding-inline:clamp(1rem,4vw,2rem)}
.topo{background:var(--tinta);color:#fff}
.topo-linha{display:flex;align-items:center;justify-content:space-between;gap:1rem;min-height:4rem}
.marca{display:flex;align-items:center;gap:.5rem;text-decoration:none;font:700 1.0625rem/1 var(--titulo)}
.marca img{height:1.375rem;width:auto;display:block}
.topo-linha span{font:500 .8125rem/1 var(--mono);color:#aebbb4}
.abertura{background:var(--tinta);color:#fff;padding-block:clamp(2rem,5vw,3.5rem) clamp(5rem,10vw,7rem)}
.abertura .rotulo{font:500 .8125rem/1 var(--mono);color:var(--lima);letter-spacing:.06em;text-transform:uppercase}
.abertura h1{font-size:clamp(2rem,5.5vw,3.5rem);margin-top:.875rem;max-width:22ch}
.abertura p{margin-top:1rem;color:#c3cdc7;font-size:1.125rem;max-width:56ch}
.corpo{margin-top:clamp(-4.5rem,-8vw,-3.5rem);display:grid;gap:1.5rem;padding-bottom:3rem}
@media (min-width:60rem){.corpo{grid-template-columns:1.45fr 1fr;align-items:start}}
.janela{background:var(--superficie);border-radius:12px;overflow:hidden;box-shadow:0 26px 50px -30px rgba(19,32,26,.6);border:1px solid var(--linha);min-width:0}
.janela-barra{display:flex;align-items:center;gap:.375rem;padding:.625rem .875rem;border-bottom:1px solid var(--linha);background:#f7f8f6}
.janela-barra i{width:.5rem;height:.5rem;border-radius:50%;background:#d5dbd6}
.janela-barra code{margin-left:.5rem;font:500 .75rem/1 var(--mono);color:var(--suave);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tela{position:relative;aspect-ratio:16/11;overflow:hidden;background:#eef1ee}
.tela iframe{position:absolute;top:0;left:0;width:1280px;height:880px;border:0;transform-origin:0 0;transform:scale(var(--escala,.5));pointer-events:none}
.tela a{position:absolute;inset:0}
.janela-pe{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.75rem;padding:1rem 1.25rem}
.janela-pe p{color:var(--suave);font-size:.9375rem}
.link-forte{font-weight:600;color:var(--verde-escuro);text-underline-offset:.2em}
.plano{background:var(--superficie);border-radius:12px;border:2px solid var(--tinta);padding:1.75rem;display:grid;gap:1rem}
.plano-nome{font:600 .8125rem/1 var(--mono);text-transform:uppercase;letter-spacing:.08em;color:var(--verde-escuro)}
.preco{font:700 clamp(2.75rem,7vw,3.5rem)/1 var(--titulo);letter-spacing:-.03em;font-variant-numeric:tabular-nums}
.preco small{font:500 1.0625rem/1 var(--texto);color:var(--suave);letter-spacing:0}
.condicao{justify-self:start;background:var(--lima);color:var(--tinta);font-weight:600;font-size:.875rem;padding:.3rem .75rem;border-radius:99px}
.plano ul{list-style:none;margin:0;padding:1rem 0 0;border-top:1px solid var(--linha);display:grid;gap:.5rem}
.plano li{display:flex;gap:.625rem}
.plano li::before{content:"✓";color:var(--verde);font-weight:700}
.obs{font-size:.9375rem;color:var(--suave)}
.cta{display:flex;align-items:center;justify-content:center;gap:.5rem;min-height:3.25rem;background:var(--verde);color:#fff;text-decoration:none;font:600 1.0625rem/1.1 var(--titulo);border-radius:10px;padding:.875rem 1.25rem}
.cta:hover{background:var(--verde-escuro)}
.cta svg{width:1.25rem;height:1.25rem}
.validade{font-size:.875rem;color:var(--suave);text-align:center}
.passos-secao{border-top:1px solid var(--linha);padding-block:2.5rem 3.5rem}
.passos-secao h2{font-size:1.5rem;margin-bottom:1.25rem}
.passos{list-style:none;margin:0;padding:0;display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fit,minmax(min(100%,13rem),1fr));counter-reset:p}
.passos li{counter-increment:p;display:grid;gap:.375rem;align-content:start}
.passos li::before{content:counter(p);width:2rem;height:2rem;border-radius:50%;background:var(--tinta);color:var(--lima);display:grid;place-items:center;font:700 .9375rem/1 var(--titulo)}
.passos strong{font-family:var(--titulo);font-size:1.0625rem}
.passos span{color:var(--suave)}
.rodape{border-top:1px solid var(--linha);padding-block:1.25rem calc(1.5rem + env(safe-area-inset-bottom,0px));font-size:.875rem;color:var(--suave)}
.rodape .largura{display:flex;flex-wrap:wrap;justify-content:space-between;gap:.5rem}
</style>
</head>
<body>
<header class="topo"><div class="largura topo-linha"><a class="marca" href="<?= e($wglSite) ?>"><img src="/assets/wgl-logo.svg" alt="" width="40" height="21">WGL Software</a><span>Proposta</span></div></header>

<section class="abertura">
  <div class="largura">
    <span class="rotulo">Proposta · <?= e($dataExtenso(time())) ?></span>
    <h1><?= $P['para'] !== '' ? e($P['para']) . ', preparamos' : 'Preparamos' ?> uma prévia do site de <?= e($f['nome'] ?? '') ?>.</h1>
    <p>Use à vontade: abra no celular, mostre para quem trabalha com você. Textos, fotos e preços a gente ajusta junto antes de publicar.</p>
  </div>
</section>

<main class="largura corpo">
  <div class="janela">
    <div class="janela-barra" aria-hidden="true"><i></i><i></i><i></i><code><?= e(preg_replace('#^https?://#', '', $url)) ?></code></div>
    <div class="tela"><iframe src="./" title="Prévia do site <?= e($f['nome'] ?? '') ?>" loading="lazy" tabindex="-1" aria-hidden="true" scrolling="no"></iframe><a href="./" aria-label="Abrir a prévia do site"></a></div>
    <div class="janela-pe"><p>Toque para ver o site completo.</p><a class="link-forte" href="./">Abrir a prévia →</a></div>
  </div>

  <aside class="plano" aria-labelledby="t-plano">
    <span class="plano-nome" id="t-plano"><?= e($P['plano']) ?></span>
    <div class="preco"><?= e(reais($P['valor'])) ?> <small><?= e($P['periodo']) ?></small></div>
    <?php if (!empty($P['condicao'])): ?><span class="condicao"><?= e($P['condicao']) ?></span><?php endif; ?>
    <?php if ($inclui): ?><ul><?php foreach ($inclui as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if (!empty($P['observacao'])): ?><p class="obs"><?= e($P['observacao']) ?></p><?php endif; ?>
    <a class="cta" href="<?= e($zapWgl($msgAceite)) ?>" target="_blank" rel="noopener"><?= ICONE_ZAP ?><span>Quero meu site no ar</span></a>
    <a class="link-forte" style="text-align:center" href="<?= e($zapWgl($msgDuvida)) ?>" target="_blank" rel="noopener">Tenho uma dúvida</a>
    <p class="validade">Prévia e condições válidas até <?= e(date('d/m/Y', $P['vence'])) ?>.</p>
  </aside>
</main>

<?php if ($passos): ?>
<section class="largura passos-secao" aria-labelledby="t-passos">
  <h2 id="t-passos">Como funciona daqui para frente</h2>
  <ol class="passos"><?php foreach ($passos as $ps): ?><li><strong><?= e($ps[0]) ?></strong><span><?= e($ps[1] ?? '') ?></span></li><?php endforeach; ?></ol>
</section>
<?php endif; ?>

<footer class="rodape"><div class="largura"><span>WGL Software · sites para pequenos negócios</span><a href="<?= e($wglSite) ?>"><?= e(preg_replace('#^https?://#', '', $wglSite)) ?></a></div></footer>
<script>
(function(){function f(){document.querySelectorAll('.tela').forEach(function(t){t.style.setProperty('--escala',(t.clientWidth/1280).toFixed(4))})}f();addEventListener('resize',f)})();
</script>
</body>
</html>
