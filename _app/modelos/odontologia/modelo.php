<?php
/**
 * Modelo ODONTOLOGIA — consultório ou clínica odontológica.
 * Esmalte branco e gengiva sobre azul-gelo; Red Hat Display + Red Hat Text.
 * Abertura com o seletor "onde está incomodando": a pessoa toca no dente (vista de espelho, numeração FDI),
 * escolhe o que sente e a mensagem sai pronta no WhatsApp. Nada é gravado e o site não dá diagnóstico.
 * Publicidade conforme o Código de Ética Odontológica e a Resolução CFO 196/2019: sem preço, sem forma de
 * pagamento, sem promoção e sem promessa de resultado; nome e CRO de cada profissional e do responsável técnico.
 */
$fundo   = cor($c['fundo'] ?? '', '#eef3f6');
$tinta   = cor($c['tinta'] ?? '', '#172338');
$dest    = cor($c['destaque'] ?? '', '#b8304f');
$gengiva = cor($c['gengiva'] ?? '', '#f8dfe5');
$escuro  = cor($c['escuro'] ?? '', '#172338');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@500;700&family=Red+Hat+Text:wght@400;500;700&display=swap';
$M['tipo_schema'] = 'Dentist';
$M['cor_tema'] = $escuro;
$M['favicon'] = [$dest, '#ffffff'];
$M['ocultar'] = ['depoimentos'];

$limpa = fn($lista) => array_values(array_filter((array)$lista, fn($x) => trim((string)$x) !== ''));
$trat = array_values(array_filter($f['tratamentos'] ?? [], fn($x) => !empty($x['nome'])));
$equipe = array_values(array_filter($f['equipe'] ?? [], fn($x) => !empty($x['nome'])));
$passos = array_values(array_filter($f['passos'] ?? [], fn($x) => !empty($x['titulo'])));
$conv = $limpa($f['convenios'] ?? []);
$fatos = array_slice($limpa($f['hero']['fatos'] ?? []), 0, 3);
$seletor = empty($f['seletor']['esconder']);
$queixas = $limpa($f['seletor']['queixas'] ?? []) ?: ['Dor', 'Sensibilidade ao gelado ou ao doce', 'Dente quebrado ou lascado', 'Gengiva sangrando', 'Prótese ou aparelho solto', 'Quero melhorar a estética', 'Só um check-up'];
$registroClinica = $v('clinica.registro');
$responsavel = $v('clinica.responsavel');
$gtm = preg_match('/^GTM-[A-Z0-9]{4,12}$/', strtoupper(trim((string)$v('gtm_id')))) ? strtoupper(trim((string)$v('gtm_id'))) : '';

$iniciais = function (string $nome): string {
    $p = array_values(array_filter(preg_split('/\s+/u', trim(preg_replace('/^(dra?\.?)\s+/iu', '', $nome))) ?: []));
    return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . (count($p) > 1 ? mb_substr($p[count($p) - 1], 0, 1) : ''));
};

$M['menu'] = array_values(array_filter([
    $trat ? ['tratamentos', $v('tratamentos_titulo') ?: 'Tratamentos'] : null,
    $equipe ? ['equipe', 'Equipe'] : null,
    $conv ? ['convenios', 'Convênios'] : null,
    ['contato', 'Como chegar'],
]));

/* ---------- arcada (vista de espelho: o lado esquerdo da pessoa fica à esquerda da tela) ---------- */
$NOMES = [1 => 'incisivo central', 2 => 'incisivo lateral', 3 => 'canino', 4 => 'primeiro pré-molar', 5 => 'segundo pré-molar', 6 => 'primeiro molar', 7 => 'segundo molar', 8 => 'terceiro molar (siso)'];
$arcada = function (bool $cima) use ($NOMES): string {
    $cx = 150; $cy = $cima ? 146 : 172; $rx = $cima ? 112 : 104; $ry = $cima ? 126 : 120;
    $larg = $cima ? [1 => .95, .8, .9, .86, .86, 1.25, 1.15, 1.05] : [1 => .7, .74, .84, .86, .88, 1.3, 1.2, 1.1];
    $prof = $cima ? [1 => .78, .74, .86, .92, .92, 1.1, 1.06, 1] : [1 => .72, .72, .82, .9, .9, 1.1, 1.06, 1];
    // ordem na tela: lado esquerdo da pessoa (quadrante 2 em cima, 3 embaixo) do fundo para a frente, depois o direito
    $qE = $cima ? 2 : 3; $qD = $cima ? 1 : 4;
    $ordem = [];
    for ($p = 8; $p >= 1; $p--) { $ordem[] = [$qE, $p]; }
    for ($p = 1; $p <= 8; $p++) { $ordem[] = [$qD, $p]; }
    // comprimento do arco, amostrado
    $amostras = []; $total = 0.0; $ant = null; $n = 400;
    for ($i = 0; $i <= $n; $i++) {
        $t = M_PI - M_PI * $i / $n;
        $pt = [$cx + $rx * cos($t), $cy + ($cima ? -1 : 1) * $ry * sin($t)];
        if ($ant) { $total += hypot($pt[0] - $ant[0], $pt[1] - $ant[1]); }
        $amostras[] = [$total, $t];
        $ant = $pt;
    }
    $folga = .1;
    $soma = array_sum(array_map(fn($d) => $larg[$d[1]], $ordem)) + $folga * (count($ordem) - 1);
    $util = $total * .96; $inicio = ($total - $util) / 2; $u = $util / $soma;
    $theta = function (float $s) use ($amostras): float {
        foreach ($amostras as [$l, $t]) { if ($l >= $s) { return $t; } }
        return 0.0;
    };
    $svg = '';
    $pos = $inicio;
    foreach ($ordem as [$q, $p]) {
        $w = $larg[$p] * $u; $h = $prof[$p] * $u * 1.12;
        $t = $theta($pos + $w / 2);
        $pos += $w + $folga * $u;
        $x = $cx + $rx * cos($t); $y = $cy + ($cima ? -1 : 1) * $ry * sin($t);
        $ang = rad2deg(atan2(($cima ? -1 : 1) * $ry * cos($t), -$rx * sin($t)));
        $num = $q * 10 + $p;
        $lado = in_array($q, [1, 4], true) ? 'direito' : 'esquerdo';
        $rot = 'Dente ' . $num . ', ' . $NOMES[$p] . ' de ' . ($cima ? 'cima' : 'baixo') . ', lado ' . $lado;
        $raio = $p >= 6 ? 8 : ($p >= 4 ? 7 : 5);
        $svg .= sprintf(
            '<g class="dente" role="button" tabindex="-1" aria-pressed="false" aria-label="%s" data-dente="%d" data-nome="%s" data-arco="%s" data-lado="%s" transform="translate(%.1f %.1f) rotate(%.1f)"><rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" rx="%d"/></g>',
            e($rot), $num, e($NOMES[$p]), $cima ? 'cima' : 'baixo', $lado, $x, $y, $ang,
            -($w - 1.6) / 2, -$h / 2, $w - 1.6, $h, $raio
        );
    }
    $gengiva = sprintf('<path class="gengiva" d="M%d %d A%d %d 0 0 %d %d %d"/>', $cx - $rx, $cy, $rx, $ry, $cima ? 1 : 0, $cx + $rx, $cy);
    return $gengiva . $svg;
};

$B['tokens'] = <<<CSS
/* Layout: abertura em duas colunas com o seletor da arcada, tratamentos em lista com intro fixa, passos da primeira consulta, equipe com CRO, convênios */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:color-mix(in srgb,$tinta 68%,$fundo); --linha:color-mix(in srgb,$tinta 13%,$fundo);
  --destaque:$dest; --gengiva:$gengiva; --escuro:$escuro; --esmalte:#ffffff;
  --cta:$dest; --cta-hover:color-mix(in srgb,$dest 84%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Red Hat Display",system-ui,-apple-system,"Segoe UI",sans-serif;
  --fonte-texto:"Red Hat Text",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:18px; --raio-botao:999px; --topo-fundo:color-mix(in srgb,var(--fundo) 90%,transparent); --rodape-fundo:$escuro; --foco:$dest;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<svg class="marca-dente" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.2 3.2c-2.6 0-4.2 2-4.2 4.7 0 2.4 1 4 1.6 6.3.6 2.4.8 6.6 2.9 6.6 1.8 0 1.6-4.4 3.1-5.6.4-.3.8-.4 1.4-.4s1 .1 1.4.4c1.5 1.2 1.3 5.6 3.1 5.6 2.1 0 2.3-4.2 2.9-6.6.6-2.3 1.6-3.9 1.6-6.3 0-2.7-1.6-4.7-4.2-4.7-1.9 0-3 1-4.8 1s-2.9-1-4.8-1z"/></svg><span class="marca-nome">' . e($f['nome_curto'] ?? '' ?: $f['nome']) . '</span>';

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div class="hero-texto">
      <h1 id="t-hero"><?= e($v('hero.titulo') ?: $f['nome']) ?></h1>
      <?php if ($v('hero.subtitulo')): ?><p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p><?php endif; ?>
      <div class="hero-acoes">
        <a class="botao botao-cta" data-local="abertura" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar consulta') ?></span></a>
        <?php if ($trat): ?><a class="botao botao-contorno" href="#tratamentos">Ver tratamentos</a><?php endif; ?>
      </div>
      <?php if ($fatos): ?><ul class="fatos"><?php foreach ($fatos as $ft): ?><li><?= e($ft) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>

    <?php if ($seletor): ?>
    <div class="seletor" id="seletor" data-seletor>
      <h2 class="seletor-titulo"><?= e($v('seletor.titulo') ?: 'Onde está incomodando?') ?></h2>
      <p class="seletor-texto"><?= e($v('seletor.texto') ?: 'Toque no dente e diga o que está sentindo. A mensagem chega pronta no WhatsApp da clínica.') ?></p>
      <figure class="arcada">
        <svg viewBox="0 0 300 314" role="group" aria-label="Arcada dentária, como você se vê no espelho">
          <g class="arco-cima"><?= $arcada(true) ?></g>
          <g class="arco-baixo"><?= $arcada(false) ?></g>
          <text class="leitura-num" x="150" y="175" text-anchor="middle" data-leitura aria-hidden="true">?</text>
        </svg>
        <figcaption class="lados" aria-hidden="true"><span>Seu lado esquerdo</span><span>Seu lado direito</span></figcaption>
      </figure>
      <div class="seletor-controles" data-controles hidden>
        <p class="leitura" aria-live="polite" data-descricao>Nenhum dente escolhido.</p>
        <button type="button" class="nao-sei" data-nao-sei>Não sei qual é o dente</button>
        <fieldset class="queixas">
          <legend>O que você está sentindo?</legend>
          <?php foreach ($queixas as $i => $qx): ?><label class="queixa"><input type="radio" name="queixa" value="<?= e($qx) ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= e($qx) ?></span></label><?php endforeach; ?>
        </fieldset>
      </div>
      <a class="botao botao-cta seletor-enviar" data-enviar data-local="seletor" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Enviar pelo WhatsApp</span></a>
      <p class="seletor-nota">Não é um diagnóstico: só ajuda a clínica a entender o que você sente antes de responder. Nada do que você escolhe fica gravado neste site.</p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($trat): ?>
<section class="secao secao-tratamentos" id="tratamentos" aria-labelledby="t-trat">
  <div class="embrulho tratamentos">
    <div class="tratamentos-intro">
      <h2 id="t-trat" class="titulo-secao"><?= e($v('tratamentos_titulo') ?: 'Tratamentos') ?></h2>
      <?php if ($v('tratamentos_intro')): ?><p><?= e($v('tratamentos_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="lista-trat">
      <?php foreach ($trat as $x): ?>
      <li>
        <h3><?= e($x['nome']) ?></h3>
        <?php if (!empty($x['descricao'])): ?><p><?= e($x['descricao']) ?></p><?php endif; ?>
        <?php if (!empty($x['quando'])): ?><p class="quando"><strong>Procure quando:</strong> <?= e($x['quando']) ?></p><?php endif; ?>
        <a class="trat-link" href="<?= e($zap('Olá! Vim pelo site e gostaria de agendar uma avaliação para ' . mb_strtolower($x['nome']) . '.')) ?>" data-local="tratamento: <?= e($x['nome']) ?>" target="_blank" rel="noopener">Agendar avaliação para <?= e(mb_strtolower($x['nome'])) ?></a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($passos): ?>
<section class="secao secao-passos" id="primeira-consulta" aria-labelledby="t-passos">
  <div class="embrulho">
    <h2 id="t-passos" class="titulo-secao"><?= e($v('passos_titulo') ?: 'Como é a primeira consulta') ?></h2>
    <ol class="passos" style="--n:<?= count($passos) ?>"><?php foreach ($passos as $x): ?><li><strong><?= e($x['titulo']) ?></strong><?php if (!empty($x['texto'])): ?><span><?= e($x['texto']) ?></span><?php endif; ?></li><?php endforeach; ?></ol>
    <?php if ($v('passos_nota')): ?><p class="passos-nota"><?= e($v('passos_nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($equipe): ?>
<section class="secao secao-equipe" id="equipe" aria-labelledby="t-eq">
  <div class="embrulho">
    <h2 id="t-eq" class="titulo-secao"><?= e($v('equipe_titulo') ?: 'Quem vai atender você') ?></h2>
    <ul class="equipe">
      <?php foreach ($equipe as $p): ?>
      <li class="pessoa">
        <span class="avatar<?= !empty($p['foto']) ? ' tem-foto' : '' ?>" aria-hidden="true"><?php if (!empty($p['foto'])): ?><img src="fotos/<?= e($p['foto']) ?>" alt="" width="240" height="240" loading="lazy"><?php else: ?><?= e($iniciais($p['nome'])) ?><?php endif; ?></span>
        <div>
          <h3><?= e($p['nome']) ?></h3>
          <p class="profissao"><?= e($p['profissao'] ?? '' ?: 'Cirurgião-dentista') ?><?php if (!empty($p['area'])): ?>, <?= e($p['area']) ?><?php endif; ?></p>
          <?php if (!empty($p['registro'])): ?><p class="registro"><?= e($p['registro']) ?></p><?php endif; ?>
          <?php if (!empty($p['bio'])): ?><p class="bio"><?= e($p['bio']) ?></p><?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($conv): ?>
<section class="secao secao-convenios" id="convenios" aria-labelledby="t-conv">
  <div class="embrulho convenios-grade">
    <h2 id="t-conv" class="titulo-secao"><?= e($v('convenios_titulo') ?: 'Convênios aceitos') ?></h2>
    <div>
      <ul class="convenios"><?php foreach ($conv as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      <?php if ($v('convenios_nota')): ?><p class="convenios-nota"><?= e($v('convenios_nota')) ?></p><?php endif; ?>
      <a class="trat-link" href="<?= e($zap('Olá! Vim pelo site e queria confirmar se vocês atendem o meu convênio.')) ?>" data-local="convênios" target="_blank" rel="noopener">Confirmar o meu convênio</a>
    </div>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

if ($registroClinica || $responsavel) {
    $B['rodape'] = '<p class="rodape-registro">'
        . ($responsavel ? 'Responsável técnico: ' . e($responsavel) : '')
        . ($responsavel && $registroClinica ? '<br>' : '')
        . ($registroClinica ? e($registroClinica) : '')
        . '</p>';
}

$numero = preg_replace('/\D/', '', (string)($f['whatsapp'] ?? ''));
ob_start(); ?>
<?php if ($gtm): ?>
<div class="cookies" id="cookies" role="region" aria-label="Cookies" hidden>
  <p>Usamos cookies de estatística para saber como o site é usado. Você aceita?</p>
  <div><button type="button" class="botao botao-cta botao-pequeno" data-cookies="sim">Aceitar</button><button type="button" class="botao botao-contorno botao-pequeno" data-cookies="nao">Recusar</button></div>
</div>
<?php endif; ?>
<script>
(function () {
  var dl = window.dataLayer = window.dataLayer || [];

  // Seletor da arcada: sem JavaScript, fica só o desenho e o botão do WhatsApp.
  var sel = document.querySelector('[data-seletor]');
  if (sel) {
    var dentes = [].slice.call(sel.querySelectorAll('.dente'));
    var cima = dentes.filter(function (d) { return d.getAttribute('data-arco') === 'cima'; });
    var baixo = dentes.filter(function (d) { return d.getAttribute('data-arco') === 'baixo'; });
    var leituraNum = sel.querySelector('[data-leitura]');
    var descricao = sel.querySelector('[data-descricao]');
    var enviar = sel.querySelector('[data-enviar]');
    var escolhido = null;
    sel.querySelector('[data-controles]').hidden = false;
    sel.classList.add('ativo');
    dentes[0].tabIndex = 0;

    var montar = function () {
      var q = sel.querySelector('input[name=queixa]:checked');
      var queixa = q ? q.value : '';
      var texto = 'Olá! Vim pelo site e gostaria de agendar uma avaliação.';
      if (queixa) { texto += '\nMotivo: ' + queixa; }
      if (escolhido) {
        texto += '\nDente: ' + escolhido.getAttribute('data-dente') + ' (' + escolhido.getAttribute('data-nome') + ' de ' + escolhido.getAttribute('data-arco') + ', lado ' + escolhido.getAttribute('data-lado') + ')';
      } else if (!/check-up/i.test(queixa)) {
        texto += '\nDente: não sei dizer qual';
      }
      enviar.href = 'https://wa.me/<?= e($numero) ?>?text=' + encodeURIComponent(texto);
    };
    var escolher = function (d, foco) {
      dentes.forEach(function (x) { x.setAttribute('aria-pressed', x === d ? 'true' : 'false'); x.tabIndex = -1; });
      escolhido = d;
      (d || dentes[0]).tabIndex = 0;
      if (d) {
        leituraNum.textContent = d.getAttribute('data-dente');
        descricao.textContent = 'Dente ' + d.getAttribute('data-dente') + ': ' + d.getAttribute('data-nome') + ' de ' + d.getAttribute('data-arco') + ', do seu lado ' + d.getAttribute('data-lado') + '.';
        if (foco) { d.focus(); }
      } else {
        leituraNum.textContent = '?';
        descricao.textContent = 'Sem dente escolhido: a clínica pergunta na conversa.';
      }
      montar();
    };
    dentes.forEach(function (d) {
      d.addEventListener('click', function () { escolher(escolhido === d ? null : d, false); });
      d.addEventListener('keydown', function (e) {
        var arco = d.getAttribute('data-arco') === 'cima' ? cima : baixo, i = arco.indexOf(d), alvo = null;
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); escolher(escolhido === d ? null : d, true); return; }
        if (e.key === 'ArrowRight') alvo = arco[Math.min(i + 1, arco.length - 1)];
        else if (e.key === 'ArrowLeft') alvo = arco[Math.max(i - 1, 0)];
        else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') alvo = (arco === cima ? baixo : cima)[i];
        if (alvo) { e.preventDefault(); d.tabIndex = -1; alvo.tabIndex = 0; alvo.focus(); }
      });
    });
    sel.querySelector('[data-nao-sei]').addEventListener('click', function () { escolher(null, false); });
    sel.addEventListener('change', montar);
    montar();
  }

  // Cliques no WhatsApp (evento para o Google Tag Manager; o dente e a queixa não são enviados)
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href^="https://wa.me/"]');
    if (!a) return;
    var local = a.getAttribute('data-local') || (a.closest('.topo') ? 'topo' : a.classList.contains('zap-flutuante') ? 'botão flutuante' : (a.closest('[id]') || {}).id || 'site');
    dl.push({ event: 'clique_whatsapp', local: local });
  });
<?php if ($gtm): ?>

  // Google Tag Manager só depois do aceite (LGPD)
  var caixa = document.getElementById('cookies'), chave = 'cookies-estatistica', escolha = null;
  try { escolha = localStorage.getItem(chave); } catch (x) {}
  var carregar = function () {
    dl.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtm.js?id=<?= e($gtm) ?>';
    document.head.appendChild(s);
  };
  if (escolha === 'sim') { carregar(); }
  else if (escolha !== 'nao') { caixa.hidden = false; }
  caixa.addEventListener('click', function (e) {
    var b = e.target.closest('[data-cookies]');
    if (!b) return;
    var v = b.getAttribute('data-cookies');
    try { localStorage.setItem(chave, v); } catch (x) {}
    caixa.hidden = true;
    if (v === 'sim') { carregar(); }
  });
<?php endif; ?>
})();
</script>
<?php $B['script'] = ob_get_clean();
