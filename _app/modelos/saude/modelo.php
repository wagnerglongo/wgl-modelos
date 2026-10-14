<?php
/** Modelo SAÚDE — dentistas, fisioterapia, psicologia, nutrição, clínicas. Visual calmo: serifa de publicação médica, petróleo e menta. */
$fundo = cor($c['fundo'] ?? '', '#f6f9f8');
$tinta = cor($c['tinta'] ?? '', '#12302f');
$dest  = cor($c['destaque'] ?? '', '#0f6e6a');
$menta = cor($c['menta'] ?? '', '#e2f1ed');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,600;1,8..60,600&family=Source+Sans+3:wght@400;600;700&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'MedicalClinic';
$M['cor_tema'] = $dest;
$M['favicon'] = [$dest, '#ffffff'];

$esp = array_values(array_filter($f['especialidades'] ?? [], fn($x) => !empty($x['nome'])));
$equipe = array_values(array_filter($f['equipe'] ?? [], fn($x) => !empty($x['nome'])));
$conv = array_values(array_filter((array)($f['convenios'] ?? []), fn($x) => trim((string)$x) !== ''));
$passos = array_values(array_filter($f['passos'] ?? [], fn($x) => !empty($x['titulo'])));
$selos = array_values(array_filter((array)($f['selos'] ?? []), fn($x) => trim((string)$x) !== ''));
$cons = (array)($f['consulta'] ?? []);

$M['menu'] = array_values(array_filter([
    $esp ? ['especialidades', $v('especialidades_titulo') ?: 'Especialidades'] : null,
    $equipe ? ['equipe', 'Equipe'] : null,
    $conv ? ['convenios', 'Convênios'] : null,
    ['duvidas', 'Dúvidas'],
]));

$B['tokens'] = <<<CSS
/* Layout: herói em degradê de menta com o cartão da primeira consulta, especialidades em cartões, equipe em faixa branca */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#4d6664; --linha:#d6e4e1;
  --destaque:$dest; --menta:$menta; --cta:$dest; --cta-hover:color-mix(in srgb,$dest 82%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Source Serif 4",Georgia,"Times New Roman",serif;
  --fonte-texto:"Source Sans 3",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:16px; --raio-botao:999px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent);
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<span class="cruz" aria-hidden="true"></span><span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>';

$iniciais = function (string $nome): string {
    $partes = array_values(array_filter(preg_split('/\s+/', trim(preg_replace('/^(dra?\.?|dr\.?)\s+/iu', '', $nome))) ?: []));
    $ini = mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[count($partes) - 1] ?? '', 0, 1);
    return mb_strtoupper($ini);
};

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar consulta') ?></span></a>
        <?php if ($esp): ?><a class="botao botao-contorno" href="#especialidades">Ver <?= e(mb_strtolower($v('especialidades_titulo') ?: 'especialidades')) ?></a><?php endif; ?>
      </div>
      <?php if ($selos): ?><ul class="selos"><?php foreach ($selos as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <?php if (array_filter($cons)): ?>
    <aside class="cartao-consulta" aria-label="Primeira consulta">
      <div class="cc-topo"><h2><?= e($cons['titulo'] ?? '' ?: 'Sua primeira consulta') ?></h2><span>Agenda aberta</span></div>
      <dl>
        <?php if (!empty($cons['duracao'])): ?><div><dt>Duração</dt><dd><?= e($cons['duracao']) ?></dd></div><?php endif; ?>
        <?php if (isset($cons['valor']) && $cons['valor'] !== ''): ?><div><dt>Particular</dt><dd><?= e(reais($cons['valor'])) ?></dd></div><?php endif; ?>
        <?php if ($conv): ?><div><dt>Convênios</dt><dd><?= count($conv) ?> aceitos</dd></div><?php endif; ?>
        <?php if (!empty($cons['reembolso'])): ?><div><dt>Reembolso</dt><dd><?= e($cons['reembolso']) ?></dd></div><?php endif; ?>
      </dl>
      <?php if (!empty($cons['nota'])): ?><p class="cc-nota"><?= e($cons['nota']) ?></p><?php endif; ?>
      <a class="botao botao-cta" href="<?= e($zap('Olá! Quero agendar a minha primeira consulta. Quais horários vocês têm?')) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Ver horários disponíveis</span></a>
    </aside>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($esp): ?>
<section class="secao" id="especialidades" aria-labelledby="t-esp">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-esp" class="titulo-secao"><?= e($v('especialidades_titulo') ?: 'Especialidades') ?></h2>
      <?php if ($v('especialidades_intro')): ?><p><?= e($v('especialidades_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="especialidades">
      <?php foreach ($esp as $x): ?>
      <li class="esp">
        <span class="esp-icone" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($x['nome'], 0, 1))) ?></span>
        <h3><?= e($x['nome']) ?></h3>
        <p><?= e($x['descricao'] ?? '') ?></p>
        <?php if (!empty($x['detalhe'])): ?><small><?= e($x['detalhe']) ?></small><?php endif; ?>
        <a href="<?= e($zap('Olá! Gostaria de saber mais sobre ' . mb_strtolower($x['nome']) . ' e agendar uma avaliação.')) ?>" target="_blank" rel="noopener">Agendar avaliação</a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($equipe): ?>
<section class="secao secao-equipe" id="equipe" aria-labelledby="t-eq">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-eq" class="titulo-secao"><?= e($v('equipe_titulo') ?: 'Quem vai cuidar de você') ?></h2>
    </div>
    <ul class="equipe">
      <?php foreach ($equipe as $p): ?>
      <li class="pessoa">
        <span class="avatar" aria-hidden="true"><?php if (!empty($p['foto'])): ?><img src="fotos/<?= e($p['foto']) ?>" alt=""><?php else: ?><?= e($iniciais($p['nome'])) ?><?php endif; ?></span>
        <h3><?= e($p['nome']) ?></h3>
        <?php if (!empty($p['funcao'])): ?><span class="funcao"><?= e($p['funcao']) ?></span><?php endif; ?>
        <?php if (!empty($p['registro'])): ?><span class="registro"><?= e($p['registro']) ?></span><?php endif; ?>
        <?php if (!empty($p['bio'])): ?><p><?= e($p['bio']) ?></p><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($conv): ?>
<section class="secao" id="convenios" aria-labelledby="t-conv">
  <div class="embrulho convenios-caixa">
    <h2 id="t-conv" class="titulo-secao"><?= e($v('convenios_titulo') ?: 'Convênios aceitos') ?></h2>
    <div>
      <ul class="convenios"><?php foreach ($conv as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      <?php if ($v('convenios_nota')): ?><p class="convenios-nota"><?= e($v('convenios_nota')) ?></p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($passos): ?>
<section class="secao" id="como-funciona" aria-labelledby="t-passos">
  <div class="embrulho">
    <h2 id="t-passos" class="titulo-secao"><?= e($v('passos_titulo') ?: 'Como é a primeira consulta') ?></h2>
    <ol class="passos"><?php foreach ($passos as $x): ?><li><strong><?= e($x['titulo']) ?></strong><span><?= e($x['texto'] ?? '') ?></span></li><?php endforeach; ?></ol>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();
