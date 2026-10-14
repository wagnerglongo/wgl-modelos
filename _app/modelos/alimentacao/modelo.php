<?php
/** Modelo ALIMENTAÇÃO — marmitarias, restaurantes, confeitarias, lanchonetes. Visual de comanda de balcão. */
$fundo = cor($c['fundo'] ?? '', '#fffaf4');
$tinta = cor($c['tinta'] ?? '', '#2b1d14');
$dest  = cor($c['destaque'] ?? '', '#c63f24');
$folha = cor($c['folha'] ?? '', '#2f6b3a');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Nunito+Sans:opsz,wght@6..12,400;6..12,600;6..12,700;6..12,800&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'Restaurant';
$M['cor_tema'] = $dest;
$M['favicon'] = [$dest, '#fffaf4'];

$cardapio = array_values(array_filter($f['cardapio'] ?? [], fn($d) => !empty($d['prato'])));
$tamanhos = array_values(array_filter($f['tamanhos'] ?? [], fn($t) => !empty($t['nome'])));
$extras = array_values(array_filter($f['extras'] ?? [], fn($x) => !empty($x['nome'])));
$ent = (array)($f['entrega'] ?? []);
$bairros = array_values(array_filter((array)($ent['bairros'] ?? []), fn($x) => trim((string)$x) !== ''));
$temEntrega = !empty($ent['janela']) || $bairros;
$primeiro = $cardapio[0] ?? ['dia' => '', 'prato' => '', 'acompanha' => '', 'opcao' => ''];
$pop = null;
foreach ($tamanhos as $t) { if (!empty($t['popular'])) { $pop = $t; break; } }
$pop = $pop ?? ($tamanhos[0] ?? ['nome' => '', 'preco' => '']);

$M['menu'] = array_values(array_filter([
    $cardapio ? ['cardapio', 'Cardápio'] : null,
    $tamanhos ? ['precos', 'Preços'] : null,
    $temEntrega ? ['entrega', 'Entrega'] : null,
    ['duvidas', 'Dúvidas'],
]));

$B['tokens'] = <<<CSS
/* Layout: herói com a comanda do dia ao lado, cardápio da semana em 5 colunas, tamanhos em cartões, entrega em faixa */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#6b5747; --linha:#ecdfcd;
  --destaque:$dest; --folha:$folha; --mostarda:#f3b634; --cta:$folha; --cta-hover:color-mix(in srgb,$folha 85%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Bricolage Grotesque","Trebuchet MS",system-ui,sans-serif;
  --fonte-texto:"Nunito Sans",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:16px; --raio-botao:999px; --topo-fundo:color-mix(in srgb,var(--fundo) 93%,transparent); --rodape-fundo:$tinta;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<span class="panela" aria-hidden="true"></span><span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>';

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Fazer pedido') ?></span></a>
        <?php if ($cardapio): ?><a class="botao botao-contorno" href="#cardapio">Cardápio da semana</a><?php endif; ?>
      </div>
      <?php if (!empty($ent['pedido_ate'])): ?><p class="prazo">Pedidos até <b><?= e(hora($ent['pedido_ate'])) ?></b><?php if (!empty($ent['janela'])): ?> · entrega <?= e($ent['janela']) ?><?php endif; ?></p><?php endif; ?>
    </div>
    <?php if ($cardapio): ?>
    <article class="comanda" id="comanda" aria-label="Prato do dia">
      <div class="comanda-topo"><span class="comanda-dia" id="comanda-dia"><?= e($primeiro['dia']) ?></span><span class="comanda-num">Prato do dia</span></div>
      <h2 id="comanda-prato"><?= e($primeiro['prato']) ?></h2>
      <p class="comanda-acomp" id="comanda-acomp"><?= e($primeiro['acompanha'] ?? '') ?></p>
      <p class="comanda-opcao" id="comanda-opcao"<?= empty($primeiro['opcao']) ? ' hidden' : '' ?>><b>Opção:</b> <span><?= e($primeiro['opcao'] ?? '') ?></span></p>
      <div class="comanda-preco">
        <?php if ($pop['nome'] !== ''): ?><span><small><?= e(mb_strtolower($pop['nome'])) ?></small><?= e(reais($pop['preco'] ?? '')) ?></span><?php else: ?><span></span><?php endif; ?>
        <a class="botao botao-cta" id="comanda-botao" href="<?= e($zap('Olá! Quero pedir o prato de ' . mb_strtolower($primeiro['dia']) . ': ' . $primeiro['prato'] . '.' . ($pop['nome'] !== '' ? ' Tamanho ' . $pop['nome'] . '.' : ''))) ?>" target="_blank" rel="noopener">Pedir este</a>
      </div>
    </article>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($cardapio): ?>
<section class="secao secao-cardapio" id="cardapio" aria-labelledby="t-card">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-card" class="titulo-secao"><?= e($v('cardapio_titulo') ?: 'Cardápio da semana') ?></h2>
      <?php if ($v('cardapio_intro')): ?><p><?= e($v('cardapio_intro')) ?></p><?php endif; ?>
    </div>
    <ol class="semana">
      <?php foreach ($cardapio as $d): ?>
      <li class="dia" data-dia="<?= e($d['sigla'] ?? '') ?>">
        <h3><?= e($d['dia'] ?? '') ?><span class="selo-hoje">Hoje</span></h3>
        <p class="dia-prato"><?= e($d['prato']) ?></p>
        <p class="dia-acomp"><?= e($d['acompanha'] ?? '') ?></p>
        <?php if (!empty($d['opcao'])): ?><p class="dia-opcao"><b>Opção:</b> <?= e($d['opcao']) ?></p><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php if ($v('cardapio_nota')): ?><p class="cardapio-nota"><?= e($v('cardapio_nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($tamanhos): ?>
<section class="secao" id="precos" aria-labelledby="t-tam">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-tam" class="titulo-secao"><?= e($v('tamanhos_titulo') ?: 'Tamanhos e preços') ?></h2>
      <?php if ($v('tamanhos_intro')): ?><p><?= e($v('tamanhos_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="tamanhos">
      <?php foreach ($tamanhos as $t): ?>
      <li class="tamanho<?= !empty($t['popular']) ? ' popular' : '' ?>">
        <?php if (!empty($t['popular'])): ?><span class="tamanho-tag">Mais pedida</span><?php endif; ?>
        <span class="tamanho-letra" aria-hidden="true"><?= e($t['sigla'] ?? mb_substr($t['nome'], 0, 1)) ?></span>
        <h3><?= e($t['nome']) ?></h3>
        <span class="tamanho-peso"><?= e(trim(($t['peso'] ?? '') . (!empty($t['obs']) ? ' · ' . $t['obs'] : ''), ' ·')) ?></span>
        <span class="tamanho-preco"><?= ($t['preco'] ?? '') !== '' ? e(reais($t['preco'])) : 'Consulte' ?></span>
        <a href="<?= e($zap('Olá! Quero pedir 1 ' . mb_strtolower($t['nome']) . ' do prato de hoje.')) ?>" target="_blank" rel="noopener">Pedir</a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php if ($extras): ?><p class="extras"><?php foreach ($extras as $x): ?><span><b><?= e($x['nome']) ?></b> <?= e(reais($x['preco'] ?? '')) ?></span><?php endforeach; ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($temEntrega): ?>
<section class="secao secao-entrega" id="entrega" aria-labelledby="t-ent">
  <div class="embrulho entrega">
    <div>
      <h2 id="t-ent" class="titulo-secao">Entrega</h2>
      <div class="entrega-dados">
        <?php if (!empty($ent['pedido_ate'])): ?><p>Pedidos até <b><?= e(hora($ent['pedido_ate'])) ?></b><?= !empty($ent['dias']) ? ', de ' . e($ent['dias']) : '' ?>.</p><?php endif; ?>
        <?php if (!empty($ent['janela'])): ?><p>Entrega das <b><?= e($ent['janela']) ?></b>.</p><?php endif; ?>
        <?php if (isset($ent['taxa']) && $ent['taxa'] !== ''): ?><p>Taxa de entrega: <b><?= e(reais($ent['taxa'])) ?></b>. Retirada no balcão sem taxa.</p><?php endif; ?>
      </div>
    </div>
    <div>
      <?php if ($bairros): ?><ul class="bairros"><?php foreach ($bairros as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if (!empty($ent['nota'])): ?><p class="entrega-nota"><?= e($ent['nota']) ?></p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

if ($cardapio) {
    $dadosJs = json_seguro(array_map(fn($d) => [
        'dia' => $d['dia'] ?? '', 'sigla' => $d['sigla'] ?? '', 'prato' => $d['prato'],
        'acompanha' => $d['acompanha'] ?? '', 'opcao' => $d['opcao'] ?? '',
    ], $cardapio), false);
    $zapBase = json_seguro('https://wa.me/' . $whats . '?text=', false);
    $tamPop = json_seguro($pop['nome'] !== '' ? ' Tamanho ' . $pop['nome'] . '.' : '', false);
    $B['script'] = <<<JS
<script>
(function(){
  var cardapio=$dadosJs;
  var siglas=['dom','seg','ter','qua','qui','sex','sab'];
  var hoje=siglas[new Date().getDay()];
  var item=cardapio.find(function(d){return d.sigla===hoje});
  var li=document.querySelector('.dia[data-dia="'+hoje+'"]');
  if(li) li.classList.add('hoje');
  var alvo=item||cardapio[0];
  document.getElementById('comanda-dia').textContent=item?'Hoje · '+item.dia:alvo.dia+' · próximo cardápio';
  document.getElementById('comanda-prato').textContent=alvo.prato;
  document.getElementById('comanda-acomp').textContent=alvo.acompanha;
  var op=document.getElementById('comanda-opcao');
  op.hidden=!alvo.opcao; op.querySelector('span').textContent=alvo.opcao;
  var btn=document.getElementById('comanda-botao');
  btn.href=$zapBase+encodeURIComponent('Olá! Quero pedir o prato de '+alvo.dia.toLowerCase()+': '+alvo.prato+'.'+$tamPop);
  if(!item) btn.textContent='Encomendar';
})();
</script>
JS;
}
