<?php
/**
 * Modelo PEDIATRIA — pediatra com consultório e/ou teleconsulta.
 * Papel creme, verde-petróleo e terracota; Fraunces + Hanken Grotesk. Motivo visual: a régua de crescimento.
 * Abertura com o agendador: a família escolhe presencial ou online, a idade da criança e o motivo, e a mensagem
 * sai pronta no WhatsApp. Nada é gravado e o site não orienta tratamento. Aviso de sinais de alerta sempre visível.
 * Régua das consultas de rotina (calendário mínimo da Caderneta da Criança), cada marco abre o WhatsApp.
 * Publicidade conforme a Resolução CFM 2.336/2023: nome, CRM e RQE visíveis, sem promessa de resultado e
 * sem "o melhor"; "especialista" só com RQE. Depoimentos ficam escondidos.
 */
$fundo   = cor($c['fundo'] ?? '', '#fbf5ec');
$tinta   = cor($c['tinta'] ?? '', '#1f3a3d');
$dest    = cor($c['destaque'] ?? '', '#b4472d');
$suave   = cor($c['suave'] ?? '', '#f4dccb');
$verde   = cor($c['verde'] ?? '', '#d8e8df');
$escuro  = cor($c['escuro'] ?? '', '#1f3a3d');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght,SOFT@9..144,500..700,100&family=Hanken+Grotesk:wght@400;500;600;700&display=swap';
$M['tipo_schema'] = 'Physician';
$M['cor_tema'] = $escuro;
$M['favicon'] = [$dest, '#ffffff'];
$M['ocultar'] = ['sobre', 'depoimentos'];

$limpa = fn($lista) => array_values(array_filter((array)$lista, fn($x) => trim((string)$x) !== ''));
$fatos = array_slice($limpa($f['hero']['fatos'] ?? []), 0, 3);
$temas = array_values(array_filter($f['temas'] ?? [], fn($x) => !empty($x['nome'])));
$marcos = array_values(array_filter($f['rotina']['marcos'] ?? [], fn($x) => !empty($x['idade'])));
$conv = $limpa($f['convenios'] ?? []);
$ped = $f['pediatra'] ?? [];
$formacao = $limpa($ped['formacao'] ?? []);
$bio = $limpa($ped['bio'] ?? []);
$presencial = empty($f['atendimento']['presencial_esconder']);
$online = empty($f['atendimento']['online_esconder']);
$cidadePresencial = $v('atendimento.presencial_onde') ?: trim(($f['endereco']['cidade'] ?? '') . ' – ' . ($f['endereco']['uf'] ?? ''), ' –');
$itensPres = $limpa($f['atendimento']['presencial_itens'] ?? []);
$itensOn = $limpa($f['atendimento']['online_itens'] ?? []);
$agendador = empty($f['agendador']['esconder']);
$idades = $limpa($f['agendador']['idades'] ?? []) ?: ['Recém-nascido', '1 a 6 meses', '7 a 12 meses', '1 a 2 anos', '3 a 5 anos', '6 a 11 anos', '12 anos ou mais', 'Ainda vai nascer'];
$motivos = $limpa($f['agendador']['motivos'] ?? []) ?: ['Consulta de rotina', 'Primeira consulta', 'Está doente', 'Amamentação ou alimentação', 'Sono ou desenvolvimento', 'Retorno', 'Consulta pré-natal com a pediatra'];
$alerta = $v('agendador.alerta') ?: 'Febre em bebê com menos de 3 meses, dificuldade para respirar, convulsão, sonolência fora do comum ou manchas roxas na pele: não espere a resposta. Procure o pronto-socorro ou ligue 192.';
$gtm = preg_match('/^GTM-[A-Z0-9]{4,12}$/', strtoupper(trim((string)$v('gtm_id')))) ? strtoupper(trim((string)$v('gtm_id'))) : '';
$nomeMedica = $ped['nome'] ?? '' ?: $f['nome'];
$registro = implode(' · ', array_filter([$ped['crm'] ?? '', $ped['rqe'] ?? '']));

$iniciais = function (string $nome): string {
    $p = array_values(array_filter(preg_split('/\s+/u', trim(preg_replace('/^(dra?\.?)\s+/iu', '', $nome))) ?: []));
    return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . (count($p) > 1 ? mb_substr($p[count($p) - 1], 0, 1) : ''));
};

$M['schema_extra'] = ['medicalSpecialty' => 'https://schema.org/Pediatric'];
if ($online) {
    $M['schema_extra']['availableService'] = ['@type' => 'MedicalProcedure', 'name' => 'Teleconsulta pediátrica'];
    $M['schema_extra']['areaServed'] = ['@type' => 'Country', 'name' => 'Brasil'];
}

$M['menu'] = array_values(array_filter([
    ($presencial || $online) ? ['atendimento', 'Atendimento'] : null,
    $marcos ? ['rotina', 'Consultas de rotina'] : null,
    !empty($ped['nome']) ? ['pediatra', 'A pediatra'] : null,
    ['contato', 'Contato'],
]));

$B['tokens'] = <<<CSS
/* Layout: abertura com agendador (modalidade, idade, motivo), presencial x online lado a lado, régua das consultas de rotina, temas em grade, a pediatra com CRM e RQE */
:root{
  --fundo:$fundo; --superficie:#fffdf9; --tinta:$tinta; --tinta-suave:color-mix(in srgb,$tinta 70%,$fundo); --linha:color-mix(in srgb,$tinta 14%,$fundo);
  --destaque:$dest; --suave:$suave; --verde:$verde; --escuro:$escuro;
  --cta:$dest; --cta-hover:color-mix(in srgb,$dest 84%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Fraunces",Georgia,"Times New Roman",serif;
  --fonte-texto:"Hanken Grotesk",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:20px; --raio-botao:999px; --topo-fundo:color-mix(in srgb,var(--fundo) 90%,transparent); --rodape-fundo:$escuro; --foco:$dest;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<svg class="marca-regua" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="2.5" width="10" height="19" rx="3"/><path d="M7 7h4M7 11h6M7 15h4M7 19h6"/></svg><span class="marca-nome">' . e($f['nome_curto'] ?? '' ?: $f['nome']) . '</span>';

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div class="hero-texto">
      <?php if ($registro): ?><p class="hero-crm"><?= e($nomeMedica) ?> · <?= e($registro) ?></p><?php endif; ?>
      <h1 id="t-hero"><?= e($v('hero.titulo') ?: $f['nome']) ?></h1>
      <?php if ($v('hero.subtitulo')): ?><p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p><?php endif; ?>
      <div class="hero-acoes">
        <a class="botao botao-cta" data-local="abertura" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar consulta') ?></span></a>
        <?php if ($marcos): ?><a class="botao botao-contorno" href="#rotina">Calendário de consultas</a><?php endif; ?>
      </div>
      <?php if ($fatos): ?><ul class="fatos"><?php foreach ($fatos as $ft): ?><li><?= e($ft) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>

    <?php if ($agendador): ?>
    <form class="agendador" id="agendar" data-agendador onsubmit="return false" aria-labelledby="t-agendar">
      <h2 class="agendador-titulo" id="t-agendar"><?= e($v('agendador.titulo') ?: 'Agende em três toques') ?></h2>
      <p class="agendador-texto"><?= e($v('agendador.texto') ?: 'Escolha como prefere ser atendido, a idade da criança e o motivo. A mensagem chega pronta no WhatsApp.') ?></p>

      <?php if ($presencial && $online): ?>
      <fieldset class="modalidades">
        <legend><span class="num">1</span> Como prefere?</legend>
        <label class="modalidade"><input type="radio" name="modalidade" value="presencial" data-texto="presencial<?= $cidadePresencial ? ' (' . e($cidadePresencial) . ')' : '' ?>" checked><span><strong>No consultório</strong><small><?= e($cidadePresencial ?: 'Presencial') ?></small></span></label>
        <label class="modalidade"><input type="radio" name="modalidade" value="online" data-texto="online (teleconsulta)"><span><strong>Online</strong><small><?= e($v('atendimento.online_onde') ?: 'De qualquer lugar do Brasil') ?></small></span></label>
      </fieldset>
      <?php endif; ?>

      <fieldset class="chips">
        <legend><span class="num"><?= ($presencial && $online) ? 2 : 1 ?></span> Idade da criança</legend>
        <?php foreach ($idades as $i => $x): ?><label class="chip"><input type="radio" name="idade" value="<?= e($x) ?>"><span><?= e($x) ?></span></label><?php endforeach; ?>
      </fieldset>

      <fieldset class="chips">
        <legend><span class="num"><?= ($presencial && $online) ? 3 : 2 ?></span> Motivo</legend>
        <?php foreach ($motivos as $i => $x): ?><label class="chip"><input type="radio" name="motivo" value="<?= e($x) ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= e($x) ?></span></label><?php endforeach; ?>
      </fieldset>

      <p class="alerta" data-alerta role="note"><strong>Urgência?</strong> <?= e($alerta) ?></p>

      <a class="botao botao-cta agendador-enviar" data-enviar data-local="agendador" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Enviar pelo WhatsApp</span></a>
      <p class="agendador-nota">O que você escolhe só monta a mensagem no seu celular. Nada fica gravado neste site.</p>
    </form>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($presencial || $online): ?>
<section class="secao secao-atendimento" id="atendimento" aria-labelledby="t-at">
  <div class="embrulho">
    <h2 id="t-at" class="titulo-secao"><?= e($v('atendimento.titulo') ?: 'Duas formas de atendimento') ?></h2>
    <div class="modos<?= ($presencial && $online) ? '' : ' modos-um' ?>">
      <?php if ($presencial): ?>
      <article class="modo modo-presencial">
        <p class="modo-rotulo">Presencial</p>
        <h3><?= e($v('atendimento.presencial_titulo') ?: 'No consultório') ?></h3>
        <?php if ($v('atendimento.presencial_texto')): ?><p class="modo-texto"><?= e($v('atendimento.presencial_texto')) ?></p><?php endif; ?>
        <?php if ($itensPres): ?><ul><?php foreach ($itensPres as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <a class="modo-link" data-local="atendimento presencial" href="<?= e($zap('Olá! Vim pelo site e gostaria de agendar uma consulta presencial' . ($cidadePresencial ? ' em ' . $cidadePresencial : '') . '.')) ?>" target="_blank" rel="noopener">Agendar no consultório</a>
      </article>
      <?php endif; ?>
      <?php if ($online): ?>
      <article class="modo modo-online">
        <p class="modo-rotulo">Online</p>
        <h3><?= e($v('atendimento.online_titulo') ?: 'Teleconsulta para todo o Brasil') ?></h3>
        <?php if ($v('atendimento.online_texto')): ?><p class="modo-texto"><?= e($v('atendimento.online_texto')) ?></p><?php endif; ?>
        <?php if ($itensOn): ?><ul><?php foreach ($itensOn as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <a class="modo-link" data-local="atendimento online" href="<?= e($zap('Olá! Vim pelo site e gostaria de agendar uma teleconsulta.')) ?>" target="_blank" rel="noopener">Agendar teleconsulta</a>
      </article>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($marcos): ?>
<section class="secao secao-rotina" id="rotina" aria-labelledby="t-rot">
  <div class="embrulho">
    <div class="rotina-cabeca">
      <h2 id="t-rot" class="titulo-secao"><?= e($v('rotina.titulo') ?: 'Consultas de rotina') ?></h2>
      <?php if ($v('rotina.intro')): ?><p><?= e($v('rotina.intro')) ?></p><?php endif; ?>
    </div>
    <ol class="regua" aria-label="Calendário de consultas de rotina">
      <?php foreach ($marcos as $i => $m): ?>
      <li class="marco">
        <span class="marco-idade"><?= e($m['idade']) ?></span>
        <?php if (!empty($m['foco'])): ?><span class="marco-foco"><?= e($m['foco']) ?></span><?php endif; ?>
        <a class="marco-link" data-local="rotina: <?= e($m['idade']) ?>" href="<?= e($zap('Olá! Vim pelo site e gostaria de agendar a consulta de rotina (' . mb_strtolower($m['idade']) . ').')) ?>" target="_blank" rel="noopener" aria-label="Agendar a consulta de rotina: <?= e($m['idade']) ?>">Agendar</a>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php if ($v('rotina.nota')): ?><p class="rotina-nota"><?= e($v('rotina.nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($temas): ?>
<section class="secao secao-temas" id="temas" aria-labelledby="t-temas">
  <div class="embrulho temas-grade">
    <div class="temas-intro">
      <h2 id="t-temas" class="titulo-secao"><?= e($v('temas_titulo') ?: 'Do que a gente cuida') ?></h2>
      <?php if ($v('temas_intro')): ?><p><?= e($v('temas_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="temas">
      <?php foreach ($temas as $x): ?><li><h3><?= e($x['nome']) ?></h3><?php if (!empty($x['texto'])): ?><p><?= e($x['texto']) ?></p><?php endif; ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($ped['nome'])): ?>
<section class="secao secao-pediatra" id="pediatra" aria-labelledby="t-ped">
  <div class="embrulho pediatra">
    <div class="pediatra-foto<?= !empty($ped['foto']) ? ' tem-foto' : '' ?>" aria-hidden="true">
      <?php if (!empty($ped['foto'])): ?><img src="fotos/<?= e($ped['foto']) ?>" alt="" width="480" height="600" loading="lazy"><?php else: ?><span><?= e($iniciais($ped['nome'])) ?></span><?php endif; ?>
    </div>
    <div class="pediatra-texto">
      <p class="rotulo"><?= e($v('pediatra_titulo') ?: 'Quem vai atender') ?></p>
      <h2 id="t-ped"><?= e($ped['nome']) ?></h2>
      <p class="pediatra-profissao"><?= e($ped['profissao'] ?? '' ?: 'Médica pediatra') ?></p>
      <?php if ($registro): ?><p class="pediatra-registro"><?= e($registro) ?></p><?php endif; ?>
      <?php if ($bio): ?><div class="pediatra-bio"><?php foreach ($bio as $p): ?><p><?= e($p) ?></p><?php endforeach; ?></div><?php endif; ?>
      <?php if ($formacao): ?>
      <h3>Formação</h3>
      <ul class="formacao"><?php foreach ($formacao as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($conv || $v('convenios_nota')): ?>
<section class="secao secao-convenios" id="convenios" aria-labelledby="t-conv">
  <div class="embrulho convenios-grade">
    <h2 id="t-conv" class="titulo-secao"><?= e($v('convenios_titulo') ?: 'Convênios e reembolso') ?></h2>
    <div>
      <?php if ($conv): ?><ul class="convenios"><?php foreach ($conv as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($v('convenios_nota')): ?><p class="convenios-nota"><?= e($v('convenios_nota')) ?></p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

$linhasRodape = array_filter([
    !empty($ped['nome']) ? trim($ped['nome'] . ' · ' . ($ped['profissao'] ?? '' ?: 'Médica pediatra') . ($registro ? ' · ' . $registro : '')) : '',
    $v('clinica.responsavel') ? 'Diretor(a) técnico(a): ' . $v('clinica.responsavel') : '',
    $v('clinica.registro'),
]);
if ($linhasRodape) {
    $B['rodape'] = '<p class="rodape-registro">' . implode('<br>', array_map('e', $linhasRodape)) . '</p>';
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

  // Agendador: sem JavaScript, os botões continuam abrindo o WhatsApp com a mensagem padrão.
  var ag = document.querySelector('[data-agendador]');
  if (ag) {
    var enviar = ag.querySelector('[data-enviar]');
    var alerta = ag.querySelector('[data-alerta]');
    var marcado = function (nome) { var x = ag.querySelector('input[name=' + nome + ']:checked'); return x || null; };
    var montar = function () {
      var mod = marcado('modalidade'), idade = marcado('idade'), motivo = marcado('motivo');
      var texto = 'Olá! Vim pelo site e gostaria de agendar uma consulta.';
      if (mod) { texto += '\nAtendimento: ' + mod.getAttribute('data-texto'); }
      texto += '\nIdade da criança: ' + (idade ? idade.value.toLowerCase() : 'vou informar na conversa');
      if (motivo) { texto += '\nMotivo: ' + motivo.value.toLowerCase(); }
      enviar.href = 'https://wa.me/<?= e($numero) ?>?text=' + encodeURIComponent(texto);
      // quando a criança está doente, o aviso de urgência ganha destaque
      alerta.classList.toggle('alerta-forte', !!motivo && /doente/i.test(motivo.value));
    };
    ag.classList.add('ativo');
    ag.addEventListener('change', montar);
    montar();
  }

  // Cliques no WhatsApp (evento para o Google Tag Manager; idade e motivo não são enviados, são dados de saúde)
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
