<?php
/** Modelo BELEZA — barbearias, salões, estética. Visual de letreiro: condensada alta, azul-marinho e vermelho de poste. */
$fundo   = cor($c['fundo'] ?? '', '#f3f3ef');
$tinta   = cor($c['tinta'] ?? '', '#16202b');
$dest    = cor($c['destaque'] ?? '', '#b3302a');
$destEsc = cor($c['destaque_escuro'] ?? '', '#962620');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@800;900&family=Big+Shoulders+Text:wght@700;800;900&family=Figtree:wght@400;500;600;700&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'HairSalon';
$M['cor_tema'] = $tinta;
$M['favicon'] = [$tinta, '#efe9dc'];
$M['menu'] = [['precos', 'Preços'], ['sobre', $v('sobre.titulo') ?: 'Sobre'], ['duvidas', 'Dúvidas'], ['contato', $v('local_titulo') ?: 'Como chegar']];

$B['tokens'] = <<<CSS
/* Layout: faixa escura de letreiro no topo, tabela de preços com pontilhado de cardápio de parede */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#4c5561; --linha:#d9d9d1;
  --destaque:$dest; --cta:$dest; --cta-hover:$destEsc; --sobre-cta:#ffffff; --escuro:$tinta; --osso:#efe9dc;
  --fonte-titulo:"Big Shoulders Display","Arial Narrow",Impact,sans-serif;
  --fonte-texto:"Figtree",system-ui,-apple-system,"Segoe UI",sans-serif;
  --fonte-util:"Big Shoulders Text","Arial Narrow",sans-serif;
  --raio:6px; --raio-botao:4px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent);
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<span class="poste" aria-hidden="true"></span><span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>';

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar pelo WhatsApp') ?></span></a>
        <a class="botao botao-contorno" href="#precos">Ver preços</a>
      </div>
    </div>
    <?php if (!empty($f['horarios'])): ?>
    <aside class="placa" aria-label="Horários">
      <span class="placa-titulo">Horário</span>
      <dl>
        <?php foreach ($f['horarios'] as $h): ?><div><dt><?= e($h['dias'] ?? '') ?></dt><dd><?= !empty($h['fechado']) ? 'Fechado' : e(hora($h['abre'] ?? '') . '–' . hora($h['fecha'] ?? '')) ?></dd></div><?php endforeach; ?>
      </dl>
      <?php if ($v('hero.nota')): ?><p class="placa-nota"><?= e($v('hero.nota')) ?></p><?php endif; ?>
    </aside>
    <?php endif; ?>
  </div>
  <div class="faixa-poste" aria-hidden="true"></div>
</section>
<?php $B['hero'] = ob_get_clean();

$servicos = array_values(array_filter($f['servicos'] ?? [], fn($s) => !empty($s['nome'])));
$grupos = array_values(array_filter((array)($f['grupos'] ?? []), fn($g) => trim((string)$g) !== ''));
$sobra = $grupos ? 'Outros' : 'Serviços';
$porGrupo = [];
foreach ($servicos as $s) {
    $g = in_array($s['grupo'] ?? '', $grupos, true) ? $s['grupo'] : $sobra;
    $porGrupo[$g][] = $s;
}
ob_start(); ?>
<section class="secao" id="precos" aria-labelledby="t-precos">
  <div class="embrulho">
    <div class="precos-cab">
      <h2 id="t-precos" class="titulo-secao"><?= e($v('precos_titulo') ?: 'Serviços e preços') ?></h2>
      <?php if ($v('precos_intro')): ?><p><?= e($v('precos_intro')) ?></p><?php endif; ?>
    </div>
    <div class="grupos">
      <?php foreach (array_merge($grupos, [$sobra]) as $g):
        $itens = $porGrupo[$g] ?? [];
        if (!$itens) continue; ?>
      <div class="grupo">
        <h3><?= e($g) ?></h3>
        <ul class="itens">
          <?php foreach ($itens as $s): ?>
          <li class="item">
            <span class="item-nome"><?= e($s['nome']) ?></span>
            <span class="item-pontos" aria-hidden="true"></span>
            <span class="item-preco"><?php if (!empty($s['a_partir'])): ?><small>a partir de</small><?php endif; ?><?= ($s['preco'] ?? '') !== '' ? e(reais($s['preco'])) : 'consulte' ?></span>
            <span class="item-desc"><span><?= e($s['descricao'] ?? '') ?><?php if (!empty($s['duracao'])): ?> · <?= e($s['duracao']) ?><?php endif; ?></span><a href="<?= e($zap('Olá! Quero agendar: ' . $s['nome'] . '. Qual horário vocês têm?')) ?>" target="_blank" rel="noopener">Agendar</a></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($v('precos_nota')): ?><p class="precos-nota"><?= e($v('precos_nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php $B['principal'] = ob_get_clean();
