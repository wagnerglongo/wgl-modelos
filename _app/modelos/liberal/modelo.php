<?php
/** Modelo PROFISSIONAL LIBERAL — advocacia, contabilidade, corretores, consultorias. Visual sóbrio: serifa editorial, verde-escuro e bronze. Sem preços (regras da OAB). */
$fundo  = cor($c['fundo'] ?? '', '#f5f6f4');
$tinta  = cor($c['tinta'] ?? '', '#1b2330');
$dest   = cor($c['destaque'] ?? '', '#24453a');
$bronze = cor($c['bronze'] ?? '', '#a07c43');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,600;1,600&family=Public+Sans:wght@400;500;600;700&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'LegalService';
$M['cor_tema'] = $dest;
$M['favicon'] = [$dest, '#ffffff'];

$areas = array_values(array_filter($f['areas'] ?? [], fn($x) => !empty($x['nome'])));
$etapas = array_values(array_filter($f['etapas'] ?? [], fn($x) => !empty($x['titulo'])));
$equipe = array_values(array_filter($f['equipe'] ?? [], fn($x) => !empty($x['nome'])));
$fichaItens = array_values(array_filter((array)($f['atendimento']['itens'] ?? []), fn($x) => trim((string)$x) !== ''));

$M['menu'] = array_values(array_filter([
    $areas ? ['areas', $v('areas_titulo') ?: 'Áreas de atuação'] : null,
    $etapas ? ['como-trabalhamos', 'Como trabalhamos'] : null,
    $equipe ? ['equipe', 'Equipe'] : null,
    ['duvidas', 'Dúvidas'],
]));

$B['tokens'] = <<<CSS
/* Layout: herói tipográfico com ficha de atendimento ao lado, áreas em linhas de índice editorial, etapas numeradas, equipe com retratos */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#545e6b; --linha:#dcdfe0;
  --destaque:$dest; --bronze:$bronze; --bronze-claro:color-mix(in srgb,$bronze 60%,#fff); --cta:$dest; --cta-hover:color-mix(in srgb,$dest 80%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Spectral",Georgia,"Times New Roman",serif;
  --fonte-texto:"Public Sans",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:4px; --raio-botao:4px; --topo-fundo:color-mix(in srgb,var(--fundo) 94%,transparent); --rodape-fundo:$dest;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$nc = $f['nome_curto'] ?: $f['nome'];
$mono = '';
foreach (preg_split('/\s*(?:&|e)\s+|\s+/u', $nc) ?: [] as $pal) {
    if (mb_strlen($pal) > 2 && mb_strlen($mono) < 2) $mono .= mb_strtoupper(mb_substr($pal, 0, 1));
}
$B['marca'] = '<span class="monograma" aria-hidden="true">' . e($mono ?: mb_substr($nc, 0, 1)) . '</span><span class="marca-nome">' . e($nc) . '</span>';

$iniciais = function (string $nome): string {
    $p = array_values(array_filter(preg_split('/\s+/', trim(preg_replace('/^(dra?\.?|dr\.?)\s+/iu', '', $nome))) ?: []));
    return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . mb_substr($p[count($p) - 1] ?? '', 0, 1));
};

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar uma conversa') ?></span></a>
        <?php if ($areas): ?><a class="botao botao-contorno" href="#areas">Áreas de atuação</a><?php endif; ?>
      </div>
    </div>
    <?php if ($fichaItens): ?>
    <aside class="ficha-atendimento" aria-labelledby="t-ficha">
      <h2 id="t-ficha"><?= e($v('atendimento.titulo') ?: 'Atendimento') ?></h2>
      <ul><?php foreach ($fichaItens as $it): ?><li><?= e($it) ?></li><?php endforeach; ?></ul>
      <?php if ($v('atendimento.nota')): ?><p><?= e($v('atendimento.nota')) ?></p><?php endif; ?>
      <a class="botao" href="<?= e($zap('Olá! Gostaria de agendar uma conversa para explicar o meu caso.')) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Falar pelo WhatsApp</span></a>
    </aside>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($areas): ?>
<section class="secao" id="areas" aria-labelledby="t-areas">
  <div class="embrulho">
    <h2 id="t-areas" class="titulo-secao"><?= e($v('areas_titulo') ?: 'Áreas de atuação') ?></h2>
    <div class="areas">
      <?php foreach ($areas as $a): $itens = array_filter((array)($a['itens'] ?? []), fn($x) => trim((string)$x) !== ''); ?>
      <article class="area">
        <h3><?= e($a['nome']) ?></h3>
        <div class="area-corpo">
          <?php if (!empty($a['descricao'])): ?><p><?= e($a['descricao']) ?></p><?php endif; ?>
          <?php if ($itens): ?><ul><?php foreach ($itens as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($etapas): ?>
<section class="secao secao-etapas" id="como-trabalhamos" aria-labelledby="t-etapas">
  <div class="embrulho">
    <h2 id="t-etapas" class="titulo-secao"><?= e($v('etapas_titulo') ?: 'Como trabalhamos') ?></h2>
    <ol class="etapas"><?php foreach ($etapas as $et): ?><li><strong><?= e($et['titulo']) ?></strong><span><?= e($et['texto'] ?? '') ?></span></li><?php endforeach; ?></ol>
  </div>
</section>
<?php endif; ?>

<?php if ($equipe): ?>
<section class="secao" id="equipe" aria-labelledby="t-eq">
  <div class="embrulho">
    <h2 id="t-eq" class="titulo-secao"><?= e($v('equipe_titulo') ?: 'Equipe') ?></h2>
    <ul class="equipe">
      <?php foreach ($equipe as $p): ?>
      <li class="pessoa">
        <div class="retrato" aria-hidden="true"><?php if (!empty($p['foto'])): ?><img src="fotos/<?= e($p['foto']) ?>" alt=""><?php else: ?><?= e($iniciais($p['nome'])) ?><?php endif; ?></div>
        <h3><?= e($p['nome']) ?></h3>
        <?php if (!empty($p['cargo'])): ?><span class="cargo"><?= e($p['cargo']) ?></span><?php endif; ?>
        <?php if (!empty($p['registro'])): ?><span class="registro"><?= e($p['registro']) ?></span><?php endif; ?>
        <?php if (!empty($p['bio'])): ?><p><?= e($p['bio']) ?></p><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php if ($v('aviso_legal')): ?><p class="aviso-legal"><?= e($v('aviso_legal')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();
