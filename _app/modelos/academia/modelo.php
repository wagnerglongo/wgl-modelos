<?php
/** Modelo ACADEMIA — estúdios, academias de bairro, crossfit, pilates, lutas, dança. Visual esportivo: condensada itálica, cobalto e amarelo. */
$fundo = cor($c['fundo'] ?? '', '#f3f4f8');
$tinta = cor($c['tinta'] ?? '', '#0d1222');
$dest  = cor($c['destaque'] ?? '', '#2340e8');
$amar  = cor($c['amarelo'] ?? '', '#ffd23f');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@700;800&family=Barlow:wght@400;500;600;700&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'ExerciseGym';
$M['cor_tema'] = $tinta;
$M['favicon'] = [$dest, '#ffffff'];

$mods = array_values(array_filter($f['modalidades'] ?? [], fn($x) => !empty($x['nome'])));
$aulas = array_values(array_filter($f['aulas'] ?? [], fn($x) => !empty($x['modalidade']) && !empty($x['horario']) && !empty($x['dias'])));
$planos = array_values(array_filter($f['planos'] ?? [], fn($x) => !empty($x['nome'])));

$nomesDias = ['seg' => 'Seg', 'ter' => 'Ter', 'qua' => 'Qua', 'qui' => 'Qui', 'sex' => 'Sex', 'sab' => 'Sáb', 'dom' => 'Dom'];
$diasUsados = array_values(array_filter(array_keys($nomesDias), fn($d) => $d !== 'dom' || array_filter($aulas, fn($a) => in_array('dom', (array)$a['dias'], true))));
$horarios = array_values(array_unique(array_map(fn($a) => $a['horario'], $aulas)));
sort($horarios);
$porCelula = [];
$totalAulas = 0;
foreach ($aulas as $a) {
    foreach ((array)$a['dias'] as $d) { $porCelula[$a['horario']][$d][] = $a['modalidade']; $totalAulas++; }
}

$M['menu'] = array_values(array_filter([
    $mods ? ['modalidades', 'Modalidades'] : null,
    $aulas ? ['grade', 'Horários'] : null,
    $planos ? ['planos', 'Planos'] : null,
    ['duvidas', 'Dúvidas'],
]));

$B['tokens'] = <<<CSS
/* Layout: herói escuro com faixa cobalto inclinada e o cartão "aulas de hoje", modalidades, grade semanal com o dia de hoje marcado, planos */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#4f5670; --linha:#dde0ea;
  --destaque:$dest; --amarelo:$amar; --cta:$dest; --cta-hover:color-mix(in srgb,$dest 82%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Saira Condensed","Arial Narrow",Impact,sans-serif;
  --fonte-texto:"Barlow",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:12px; --raio-botao:6px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent); --rodape-fundo:$tinta;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<span class="raio" aria-hidden="true"></span><span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>';

// aulas de segunda para o HTML inicial (o JavaScript troca para o dia de hoje)
$aulasDoDia = function (string $d) use ($aulas): array {
    $l = array_values(array_filter($aulas, fn($a) => in_array($d, (array)$a['dias'], true)));
    usort($l, fn($a, $b) => strcmp($a['horario'], $b['horario']));
    return $l;
};
$hojeInicial = $aulasDoDia('seg');

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar aula experimental') ?></span></a>
        <?php if ($planos): ?><a class="botao botao-contorno" href="#planos">Ver planos</a><?php endif; ?>
      </div>
      <?php if ($mods || $aulas): ?>
      <ul class="numeros">
        <?php if ($mods): ?><li><strong><?= count($mods) ?></strong><span>modalidades</span></li><?php endif; ?>
        <?php if ($totalAulas): ?><li><strong><?= $totalAulas ?></strong><span>aulas por semana</span></li><?php endif; ?>
        <?php if ($v('hero.destaque_extra')): ?><li><strong><?= e($v('hero.destaque_extra')) ?></strong><span><?= e($v('hero.destaque_extra_rotulo')) ?></span></li><?php endif; ?>
      </ul>
      <?php endif; ?>
    </div>
    <?php if ($aulas): ?>
    <aside class="hoje" aria-labelledby="t-hoje">
      <div class="hoje-cab"><h2 id="t-hoje">Aulas de hoje</h2><span id="hoje-dia">Segunda</span></div>
      <ul id="hoje-lista">
        <?php foreach ($hojeInicial as $a): ?><li><time><?= e($a['horario']) ?></time><span><?= e($a['modalidade']) ?></span></li><?php endforeach; ?>
      </ul>
      <p class="hoje-vazio" id="hoje-vazio"<?= $hojeInicial ? ' hidden' : '' ?>>Sem aulas hoje. Veja a grade da semana.</p>
      <a class="botao botao-cta" href="<?= e($zap('Olá! Quero agendar uma aula experimental. Qual horário tem vaga?')) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Reservar vaga</span></a>
    </aside>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($mods): ?>
<section class="secao" id="modalidades" aria-labelledby="t-mod">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-mod" class="titulo-secao"><?= e($v('modalidades_titulo') ?: 'Modalidades') ?></h2>
      <?php if ($v('modalidades_intro')): ?><p><?= e($v('modalidades_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="modalidades">
      <?php foreach ($mods as $m): ?>
      <li class="mod">
        <h3><?= e($m['nome']) ?></h3>
        <p><?= e($m['descricao'] ?? '') ?></p>
        <div class="mod-info"><?php if (!empty($m['duracao'])): ?><span><?= e($m['duracao']) ?></span><?php endif; ?><?php if (!empty($m['nivel'])): ?><span><?= e($m['nivel']) ?></span><?php endif; ?></div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($aulas): ?>
<section class="secao secao-grade" id="grade" aria-labelledby="t-grade">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-grade" class="titulo-secao"><?= e($v('grade_titulo') ?: 'Horários da semana') ?></h2>
      <?php if ($v('grade_intro')): ?><p><?= e($v('grade_intro')) ?></p><?php endif; ?>
    </div>
    <div class="grade-rolagem">
      <table class="grade-aulas">
        <thead><tr><th scope="col">Horário</th><?php foreach ($diasUsados as $d): ?><th scope="col" data-dia="<?= $d ?>"><?= $nomesDias[$d] ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($horarios as $h): ?>
          <tr><th scope="row"><?= e($h) ?></th><?php foreach ($diasUsados as $d): ?><td data-dia="<?= $d ?>"><?php foreach ($porCelula[$h][$d] ?? [] as $nomeAula): ?><span><?= e($nomeAula) ?></span><?php endforeach; ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($planos): ?>
<section class="secao" id="planos" aria-labelledby="t-planos">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-planos" class="titulo-secao"><?= e($v('planos_titulo') ?: 'Planos') ?></h2>
      <?php if ($v('planos_intro')): ?><p><?= e($v('planos_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="planos">
      <?php foreach ($planos as $p): $inclui = array_filter((array)($p['inclui'] ?? []), fn($x) => trim((string)$x) !== ''); ?>
      <li class="plano<?= !empty($p['destaque']) ? ' destaque' : '' ?>">
        <?php if (!empty($p['tag'])): ?><span class="plano-tag"><?= e($p['tag']) ?></span><?php endif; ?>
        <h3><?= e($p['nome']) ?></h3>
        <span class="plano-preco"><?php if (($p['preco'] ?? '') !== ''): ?><?= e(reais($p['preco'])) ?> <small><?= e($p['periodo'] ?? '') ?></small><?php else: ?>Consulte<?php endif; ?></span>
        <?php if (!empty($p['detalhe'])): ?><span class="plano-detalhe"><?= e($p['detalhe']) ?></span><?php endif; ?>
        <?php if ($inclui): ?><ul><?php foreach ($inclui as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <a class="botao <?= !empty($p['destaque']) ? 'botao-cta' : 'botao-contorno' ?>" href="<?= e($zap('Olá! Quero saber mais sobre o plano ' . $p['nome'] . '.')) ?>" target="_blank" rel="noopener">Quero este plano</a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

if ($aulas) {
    $dadosAulas = json_seguro(array_map(fn($a) => ['h' => $a['horario'], 'm' => $a['modalidade'], 'd' => array_values((array)$a['dias'])], $aulas), false);
    $B['script'] = <<<JS
<script>
(function(){
  var A=$dadosAulas;
  var siglas=['dom','seg','ter','qua','qui','sex','sab'], nomes=['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
  var i=new Date().getDay(), d=siglas[i];
  document.querySelectorAll('[data-dia="'+d+'"]').forEach(function(c){c.classList.add('col-hoje')});
  var hoje=A.filter(function(a){return a.d.indexOf(d)>=0}).sort(function(a,b){return a.h<b.h?-1:1});
  document.getElementById('hoje-dia').textContent=nomes[i];
  var ul=document.getElementById('hoje-lista'); ul.innerHTML='';
  hoje.forEach(function(a){var li=document.createElement('li');var t=document.createElement('time');t.textContent=a.h;var s=document.createElement('span');s.textContent=a.m;li.appendChild(t);li.appendChild(s);ul.appendChild(li)});
  document.getElementById('hoje-vazio').hidden=hoje.length>0;
})();
</script>
JS;
}
