<?php
/**
 * Modelo ADVOCACIA — advogada/advogado com escritório próprio.
 * Rubi (a pedra do anel de formatura em Direito) sobre cinza frio; Newsreader + Instrument Sans.
 * Áreas de atuação em abas de pasta de processo; formulário que monta a mensagem e abre no WhatsApp (nada é gravado).
 * Publicidade conforme o Provimento 205/2021 da OAB: sem preço, sem promessa de resultado, sem depoimentos, com número da OAB.
 */
$fundo  = cor($c['fundo'] ?? '', '#eceff1');
$tinta  = cor($c['tinta'] ?? '', '#1b2130');
$dest   = cor($c['destaque'] ?? '', '#8f1d32');
$escuro = cor($c['escuro'] ?? '', '#1b2130');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,600&family=Instrument+Sans:wght@400;500;600;700&display=swap';
$M['tipo_schema'] = in_array($v('tipo_schema'), ['Attorney', 'LegalService'], true) ? $v('tipo_schema') : 'Attorney';
$M['cor_tema'] = $escuro;
$M['favicon'] = [$dest, '#ffffff'];
$M['ocultar'] = ['sobre', 'depoimentos'];

$limpa = fn($lista) => array_values(array_filter((array)$lista, fn($x) => trim((string)$x) !== ''));
$areas = array_values(array_filter($f['areas'] ?? [], fn($x) => !empty($x['nome'])));
$etapas = array_values(array_filter($f['etapas'] ?? [], fn($x) => !empty($x['titulo'])));
$formacao = array_values(array_filter($f['formacao'] ?? [], fn($x) => !empty($x['texto'])));
$fatos = array_slice($limpa($f['hero']['fatos'] ?? []), 0, 3);
$paragrafos = $limpa($f['apresentacao']['paragrafos'] ?? []);
$nomeAdv = $v('advogada.nome') ?: ($f['nome_curto'] ?? '' ?: $f['nome']);
$registro = $v('advogada.registro');
$foto = $v('advogada.foto');
$gtm = preg_match('/^GTM-[A-Z0-9]{4,12}$/', strtoupper(trim((string)$v('gtm_id')))) ? strtoupper(trim((string)$v('gtm_id'))) : '';

$iniciais = function (string $nome): string {
    $p = array_values(array_filter(preg_split('/\s+/u', trim(preg_replace('/^(dra?\.?)\s+/iu', '', $nome))) ?: []));
    return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . (count($p) > 1 ? mb_substr($p[count($p) - 1], 0, 1) : ''));
};
$ini = $iniciais($nomeAdv) ?: 'A';

$M['menu'] = array_values(array_filter([
    $areas ? ['areas', $v('areas_titulo') ?: 'Áreas de atuação'] : null,
    $paragrafos ? ['sobre', $v('apresentacao.titulo') ?: 'Sobre'] : null,
    $etapas ? ['atendimento', 'Atendimento'] : null,
    ['fale', 'Contato'],
]));

$B['tokens'] = <<<CSS
/* Layout: abertura com retrato/monograma em rubi, áreas em abas de pasta, apresentação com trajetória, etapas, formulário escuro */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:color-mix(in srgb,$tinta 70%,$fundo); --linha:color-mix(in srgb,$tinta 14%,$fundo);
  --destaque:$dest; --destaque-claro:color-mix(in srgb,$dest 10%,#fff); --escuro:$escuro;
  --cta:$dest; --cta-hover:color-mix(in srgb,$dest 82%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Newsreader",Georgia,"Times New Roman",serif;
  --fonte-texto:"Instrument Sans",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:10px; --raio-botao:6px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent); --rodape-fundo:$escuro; --foco:$dest;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<span class="selo" aria-hidden="true">' . e($ini) . '</span><span class="marca-nome">' . e($f['nome_curto'] ?? '' ?: $f['nome']) . '</span>';

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div class="hero-texto">
      <h1 id="t-hero"><?= e($v('hero.titulo') ?: $f['nome']) ?></h1>
      <?php if ($v('hero.subtitulo')): ?><p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p><?php endif; ?>
      <div class="hero-acoes">
        <a class="botao botao-cta" data-local="abertura" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Conversar pelo WhatsApp') ?></span></a>
        <a class="botao botao-contorno" href="#fale">Escrever uma mensagem</a>
      </div>
      <?php if ($fatos): ?><ul class="fatos"><?php foreach ($fatos as $ft): ?><li><?= e($ft) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <figure class="retrato<?= $foto ? ' tem-foto' : '' ?>">
      <?php if ($foto): ?><img src="fotos/<?= e($foto) ?>" alt="<?= e($nomeAdv) ?>" width="640" height="800"><?php else: ?><span class="retrato-ini" aria-hidden="true"><?= e($ini) ?></span><?php endif; ?>
      <figcaption><strong><?= e($nomeAdv) ?></strong><?php if ($registro): ?><span><?= e($registro) ?></span><?php endif; ?></figcaption>
    </figure>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($areas): ?>
<section class="secao secao-areas" id="areas" aria-labelledby="t-areas">
  <div class="embrulho">
    <h2 id="t-areas" class="titulo-secao"><?= e($v('areas_titulo') ?: 'Áreas de atuação') ?></h2>
    <div class="pastas" data-pastas>
      <div class="pastas-abas" role="tablist" aria-label="<?= e($v('areas_titulo') ?: 'Áreas de atuação') ?>" hidden>
        <?php foreach ($areas as $i => $a): ?><button type="button" role="tab" id="aba-<?= $i ?>" aria-controls="area-<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= e($a['nome']) ?></button><?php endforeach; ?>
      </div>
      <?php foreach ($areas as $i => $a): $sit = $limpa($a['situacoes'] ?? []); ?>
      <article class="pasta" id="area-<?= $i ?>" role="tabpanel" aria-labelledby="aba-<?= $i ?>">
        <div class="pasta-resumo">
          <h3><?= e($a['nome']) ?></h3>
          <?php if (!empty($a['resumo'])): ?><p><?= e($a['resumo']) ?></p><?php endif; ?>
          <a class="botao botao-cta" data-local="area: <?= e($a['nome']) ?>" href="<?= e($zap('Olá! Vim pelo site e gostaria de conversar sobre um caso de ' . mb_strtolower($a['nome']) . '.')) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Conversar sobre <?= e(mb_strtolower($a['nome'])) ?></span></a>
        </div>
        <?php if ($sit): ?>
        <div class="pasta-situacoes">
          <h4>Situações comuns</h4>
          <ul><?php foreach ($sit as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($paragrafos): ?>
<section class="secao secao-apresentacao" id="sobre" aria-labelledby="t-sobre">
  <div class="embrulho apresentacao">
    <div>
      <h2 id="t-sobre" class="titulo-secao"><?= e($v('apresentacao.titulo') ?: 'Sobre') ?></h2>
      <div class="apresentacao-texto"><?php foreach ($paragrafos as $p): ?><p><?= e($p) ?></p><?php endforeach; ?></div>
      <p class="assinatura"><strong><?= e($nomeAdv) ?></strong><?php if ($registro): ?> <span><?= e($registro) ?></span><?php endif; ?></p>
    </div>
    <?php if ($formacao): ?>
    <div class="trajetoria">
      <h3>Formação e trajetória</h3>
      <ol><?php foreach ($formacao as $fo): ?><li><?php if (!empty($fo['ano'])): ?><time><?= e($fo['ano']) ?></time><?php endif; ?><span><?= e($fo['texto']) ?></span></li><?php endforeach; ?></ol>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($etapas): ?>
<section class="secao secao-etapas" id="atendimento" aria-labelledby="t-etapas">
  <div class="embrulho">
    <h2 id="t-etapas" class="titulo-secao"><?= e($v('etapas_titulo') ?: 'Como funciona o atendimento') ?></h2>
    <ol class="etapas" style="--n:<?= count($etapas) ?>"><?php foreach ($etapas as $et): ?><li><strong><?= e($et['titulo']) ?></strong><?php if (!empty($et['texto'])): ?><span><?= e($et['texto']) ?></span><?php endif; ?></li><?php endforeach; ?></ol>
    <?php if ($v('etapas_nota')): ?><p class="etapas-nota"><?= e($v('etapas_nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

ob_start(); ?>
<section class="secao secao-fale" id="fale" aria-labelledby="t-fale">
  <div class="embrulho fale">
    <div class="fale-intro">
      <h2 id="t-fale" class="titulo-secao"><?= e($v('contato.titulo') ?: 'Conte o seu caso') ?></h2>
      <p><?= e($v('contato.texto') ?: 'Escreva em poucas linhas o que aconteceu. A mensagem abre no WhatsApp já preenchida, e você revisa antes de enviar.') ?></p>
      <p class="fale-sigilo">O sigilo profissional vale desde o primeiro contato, mesmo que você não contrate.</p>
    </div>
    <form class="formulario" id="form-contato" action="<?= e($zap()) ?>" method="get" target="_blank" novalidate>
      <div class="campo-f">
        <label for="fc-nome">Seu nome</label>
        <input id="fc-nome" name="nome" type="text" autocomplete="name" required maxlength="80">
      </div>
      <div class="campo-f">
        <label for="fc-assunto">Assunto</label>
        <select id="fc-assunto" name="assunto">
          <?php foreach ($areas as $a): ?><option><?= e($a['nome']) ?></option><?php endforeach; ?>
          <option>Outro assunto</option>
        </select>
      </div>
      <div class="campo-f">
        <label for="fc-msg">O que aconteceu?</label>
        <textarea id="fc-msg" name="mensagem" rows="5" required maxlength="1200"></textarea>
      </div>
      <div class="campo-f campo-aceite">
        <input id="fc-aceite" name="aceite" type="checkbox" required>
        <label for="fc-aceite">Concordo com o uso destes dados para responder o meu contato, conforme a <a href="politica-de-privacidade.html">política de privacidade</a>.</label>
      </div>
      <p class="form-erro" id="fc-erro" role="alert" hidden></p>
      <button class="botao botao-cta" type="submit"><?= $ICONE ?><span>Abrir no WhatsApp</span></button>
      <p class="form-nota">Nada do que você escreve fica gravado neste site.</p>
    </form>
    <?php if ($v('aviso_legal')): ?><p class="aviso-legal"><?= e($v('aviso_legal')) ?></p><?php endif; ?>
  </div>
</section>
<?php $B['contato'] = ob_get_clean();

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

  // Abas de pasta: sem JavaScript, todas as áreas aparecem uma embaixo da outra.
  var pastas = document.querySelector('[data-pastas]');
  if (pastas) {
    var lista = pastas.querySelector('[role=tablist]');
    var abas = [].slice.call(lista.querySelectorAll('[role=tab]'));
    var paineis = [].slice.call(pastas.querySelectorAll('[role=tabpanel]'));
    lista.hidden = false;
    pastas.classList.add('com-abas');
    var abrir = function (i, foco) {
      abas.forEach(function (a, j) {
        a.setAttribute('aria-selected', j === i ? 'true' : 'false');
        a.tabIndex = j === i ? 0 : -1;
        paineis[j].hidden = j !== i;
      });
      if (foco) { abas[i].focus(); }
      abas[i].scrollIntoView({ block: 'nearest', inline: 'nearest' });
    };
    abas.forEach(function (a, i) {
      a.addEventListener('click', function () { abrir(i, false); });
      a.addEventListener('keydown', function (e) {
        var n = abas.length, alvo = null;
        if (e.key === 'ArrowRight') alvo = (i + 1) % n;
        else if (e.key === 'ArrowLeft') alvo = (i - 1 + n) % n;
        else if (e.key === 'Home') alvo = 0;
        else if (e.key === 'End') alvo = n - 1;
        if (alvo !== null) { e.preventDefault(); abrir(alvo, true); }
      });
    });
    paineis.forEach(function (p, j) { p.hidden = j !== 0; });
  }

  // Cliques no WhatsApp (evento para o Google Tag Manager)
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href^="https://wa.me/"]');
    if (!a) return;
    var local = a.getAttribute('data-local') || (a.closest('.topo') ? 'topo' : a.classList.contains('zap-flutuante') ? 'botão flutuante' : (a.closest('[id]') || {}).id || 'site');
    dl.push({ event: 'clique_whatsapp', local: local });
  });

  // Formulário: monta a mensagem e abre no WhatsApp
  var form = document.getElementById('form-contato');
  if (form) {
    var erro = document.getElementById('fc-erro');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var nome = form.nome.value.trim(), msg = form.mensagem.value.trim(), assunto = form.assunto.value;
      var falta = !nome ? form.nome : !msg ? form.mensagem : !form.aceite.checked ? form.aceite : null;
      [form.nome, form.mensagem, form.aceite].forEach(function (c) { c.removeAttribute('aria-invalid'); });
      if (falta) {
        falta.setAttribute('aria-invalid', 'true');
        erro.textContent = falta === form.nome ? 'Escreva o seu nome.' : falta === form.mensagem ? 'Conte em poucas linhas o que aconteceu.' : 'Marque a concordância com a política de privacidade para continuar.';
        erro.hidden = false;
        falta.focus();
        return;
      }
      erro.hidden = true;
      var texto = 'Olá! Meu nome é ' + nome + '.\nAssunto: ' + assunto + '\n\n' + msg;
      var url = 'https://wa.me/<?= e($numero) ?>?text=' + encodeURIComponent(texto);
      dl.push({ event: 'envio_formulario', assunto: assunto });
      var janela = window.open(url, '_blank', 'noopener');
      if (!janela) { window.location.href = url; }
    });
  }
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
