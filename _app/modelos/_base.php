<?php
/** Esqueleto comum de todos os modelos. Variáveis: $f, $v, $M, $B, $zap, $ICONE, $url, $indexar, $barra, $galeria, $wglSite, $wglZap, $jsonNeg, $jsonFaq, $favicon, $baseCss, $ano */
$end = $f['endereco'] ?? [];
$menu = $M['menu'];
$fotos = array_values(array_filter($f['fotos'] ?? [], fn($x) => !empty($x['arquivo'])));
$faq = array_values(array_filter($f['faq'] ?? [], fn($x) => !empty($x['pergunta'])));
$difs = array_values(array_filter($f['diferenciais'] ?? [], fn($x) => !empty($x['titulo'])));
$deps = array_values(array_filter($f['depoimentos'] ?? [], fn($x) => !empty($x['texto'])));
$paragrafos = array_values(array_filter((array)$v('sobre.paragrafos', []), fn($p) => trim((string)$p) !== ''));
$ctaTopoMsg = $v('cta_topo_msg') ?: null;
$ctaFinalMsg = $v('cta_final_msg') ?: null;
// o modelo pode esconder seções comuns que não fazem sentido para o nicho (ex.: depoimentos na advocacia)
$ocultar = $M['ocultar'] ?? [];
if (in_array('sobre', $ocultar, true)) { $paragrafos = []; $difs = []; }
if (in_array('depoimentos', $ocultar, true)) { $deps = []; }
$linkWgl = $wglZap ? 'https://wa.me/' . $wglZap . '?text=' . rawurlencode('Olá! Vi o modelo "' . ($f['nome'] ?? '') . '" e quero um site assim.') : $wglSite;
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($v('seo.titulo') ?: $f['nome']) ?></title>
<meta name="description" content="<?= e($v('seo.descricao')) ?>">
<link rel="canonical" href="<?= e($url) ?>">
<meta name="theme-color" content="<?= e($M['cor_tema']) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="<?= e($f['nome']) ?>">
<meta property="og:title" content="<?= e($v('seo.titulo') ?: $f['nome']) ?>">
<meta property="og:description" content="<?= e($v('seo.descricao')) ?>">
<meta property="og:url" content="<?= e($url) ?>">
<?php if ($fotos): ?><meta property="og:image" content="<?= e($url . 'fotos/' . $fotos[0]['arquivo']) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
<meta name="robots" content="<?= $indexar ? 'index, follow' : 'noindex, nofollow' ?>">
<link rel="icon" href="<?= e($favicon) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e($M['fontes']) ?>">
<style>
<?= $B['tokens'] ?>
<?= $baseCss ?>
<?= $B['estilo'] ?>
</style>
<script type="application/ld+json"><?= $jsonNeg ?></script>
<?php if ($faq): ?><script type="application/ld+json"><?= $jsonFaq ?></script><?php endif; ?>
</head>
<body>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<?php if ($barra === 'demo'): ?>
<div class="barra-demo" role="note"><div class="embrulho">
  <span>Modelo de demonstração · negócio fictício</span>
  <a href="<?= e($galeria) ?>">Ver todos os modelos da WGL Software</a>
</div></div>
<?php elseif ($barra === 'previa'): ?>
<div class="barra-demo" role="note"><div class="embrulho">
  <span>Prévia do seu site, feita pela WGL Software</span>
  <?php if (!empty($f['proposta']['ativa'])): ?><a href="proposta.html">Ver a proposta</a><?php else: ?><a href="<?= e($linkWgl) ?>" target="_blank" rel="noopener">Gostou? Fale com a gente</a><?php endif; ?>
</div></div>
<?php endif; ?>

<header class="topo">
  <div class="embrulho topo-linha">
    <a class="marca" href="#inicio" aria-label="<?= e($f['nome']) ?>, início"><?= $B['marca'] ?: '<span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>' ?></a>
    <nav class="menu" aria-label="Seções">
      <?php foreach ($menu as [$ancora, $rotulo]): ?><a href="#<?= e($ancora) ?>"><?= e($rotulo) ?></a><?php endforeach; ?>
    </nav>
    <a class="botao botao-cta botao-pequeno" href="<?= e($zap($ctaTopoMsg)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('cta_topo') ?: 'Chamar no WhatsApp') ?></span></a>
  </div>
</header>

<main id="conteudo">
<?= $B['hero'] ?>
<?= $B['principal'] ?>

<?php if ($difs): ?>
<section class="secao secao-diferenciais" aria-labelledby="t-dif">
  <div class="embrulho">
    <h2 id="t-dif" class="titulo-secao"><?= e($v('diferenciais_titulo') ?: 'Por que escolher a gente') ?></h2>
    <ul class="diferenciais">
      <?php foreach ($difs as $d): ?><li><strong><?= e($d['titulo']) ?></strong><span><?= e($d['texto'] ?? '') ?></span></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($fotos): ?>
<section class="secao secao-galeria" id="fotos" aria-labelledby="t-fotos">
  <div class="embrulho">
    <h2 id="t-fotos" class="titulo-secao"><?= e($v('fotos_titulo') ?: 'Fotos') ?></h2>
    <ul class="galeria">
      <?php foreach ($fotos as $ft): ?><li><img src="fotos/<?= e($ft['arquivo']) ?>" alt="<?= e($ft['descricao'] ?? '') ?>" loading="lazy" width="800" height="600"></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($paragrafos): ?>
<section class="secao secao-sobre" id="sobre" aria-labelledby="t-sobre">
  <div class="embrulho sobre">
    <h2 id="t-sobre" class="titulo-secao"><?= e($v('sobre.titulo') ?: 'Quem somos') ?></h2>
    <div class="sobre-texto"><?php foreach ($paragrafos as $p): ?><p><?= e($p) ?></p><?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($deps): ?>
<section class="secao secao-depoimentos" aria-labelledby="t-dep">
  <div class="embrulho">
    <h2 id="t-dep" class="titulo-secao">Quem já é cliente</h2>
    <ul class="depoimentos">
      <?php foreach ($deps as $d): ?><li><blockquote><p><?= e($d['texto']) ?></p></blockquote><cite><?= e($d['nome'] ?? '') ?></cite></li><?php endforeach; ?>
    </ul>
    <?php if ($v('link_avaliacoes')): ?><p><a class="link-seta" href="<?= e($v('link_avaliacoes')) ?>" target="_blank" rel="noopener">Ver avaliações no Google</a></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($faq): ?>
<section class="secao secao-faq" id="duvidas" aria-labelledby="t-faq">
  <div class="embrulho faq-grade">
    <h2 id="t-faq" class="titulo-secao">Perguntas frequentes</h2>
    <div class="faq">
      <?php foreach ($faq as $i => $q): ?>
      <details<?= $i === 0 ? ' open' : '' ?>><summary><?= e($q['pergunta']) ?></summary><p><?= e($q['resposta'] ?? '') ?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?= $B['contato'] ?>

<section class="secao secao-local" id="contato" aria-labelledby="t-local">
  <div class="embrulho local">
    <div class="local-info">
      <h2 id="t-local" class="titulo-secao"><?= e($v('local_titulo') ?: 'Onde estamos') ?></h2>
      <?php if (!empty($end['rua'])): ?>
      <address><?= e($end['rua']) ?><br><?= e(trim(($end['bairro'] ?? '') . ' · ' . ($end['cidade'] ?? '') . ' – ' . ($end['uf'] ?? ''), ' ·–')) ?><?php if (!empty($end['cep'])): ?><br>CEP <?= e($end['cep']) ?><?php endif; ?></address>
      <?php endif; ?>
      <p class="contato-linhas">
        <?php if ($v('telefone_exibicao')): ?><span>WhatsApp <strong><?= e($v('telefone_exibicao')) ?></strong></span><?php endif; ?>
        <?php if ($v('instagram')): ?><a href="https://instagram.com/<?= e($v('instagram')) ?>" target="_blank" rel="noopener">@<?= e($v('instagram')) ?></a><?php endif; ?>
        <?php if ($v('email')): ?><span><?= e($v('email')) ?></span><?php endif; ?>
      </p>
      <div class="local-botoes">
        <a class="botao botao-cta" href="<?= e($zap($ctaFinalMsg)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('cta_final') ?: 'Chamar no WhatsApp') ?></span></a>
        <?php if (!empty($end['rua'])): ?><a class="botao botao-contorno" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode(($f['nome'] ?? '') . ' ' . $end['rua'] . ' ' . ($end['cidade'] ?? ''))) ?>" target="_blank" rel="noopener">Abrir no Google Maps</a><?php endif; ?>
      </div>
    </div>
    <?php if (!empty($f['horarios'])): ?>
    <div class="horarios">
      <h3>Horário de atendimento</h3>
      <table><tbody>
        <?php foreach ($f['horarios'] as $h): ?>
        <tr><th scope="row"><?= e($h['dias'] ?? '') ?></th><td><?= !empty($h['fechado']) ? 'Fechado' : e(hora($h['abre'] ?? '') . ' às ' . hora($h['fecha'] ?? '')) ?></td></tr>
        <?php endforeach; ?>
      </tbody></table>
      <?php if ($v('horario_obs')): ?><p class="obs"><?= e($v('horario_obs')) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ($v('mapa_embed') && $barra !== 'demo' && str_starts_with((string)$v('mapa_embed'), 'https://www.google.com/maps/embed')): ?>
    <div class="mapa"><iframe src="<?= e($v('mapa_embed')) ?>" title="Mapa até <?= e($f['nome']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
    <?php endif; ?>
  </div>
</section>
</main>

<footer class="rodape">
  <div class="embrulho rodape-linha">
    <p><strong><?= e($f['nome']) ?></strong><?php if ($v('documento')): ?> · <?= e($v('documento')) ?><?php endif; ?><br><?= e(trim(($end['cidade'] ?? '') . ' – ' . ($end['uf'] ?? ''), ' –')) ?> · © <?= $ano ?></p>
    <p class="rodape-links"><a href="politica-de-privacidade.html">Política de privacidade</a><a href="<?= e($wglSite) ?>" rel="noopener">Site por WGL Software</a></p>
    <?= $B['rodape'] ?? '' ?>
  </div>
</footer>

<a class="zap-flutuante" href="<?= e($zap($ctaFinalMsg)) ?>" target="_blank" rel="noopener" aria-label="Conversar no WhatsApp"><?= $ICONE ?></a>
<?= $B['script'] ?>
</body>
</html>
