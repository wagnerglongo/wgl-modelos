<?php
/** Modelo PET — pet shops, banho e tosa, clínicas veterinárias. Visual alegre: roxo, amarelo-sol, tipografia redonda. */
$fundo = cor($c['fundo'] ?? '', '#fbf8ff');
$tinta = cor($c['tinta'] ?? '', '#24163f');
$dest  = cor($c['destaque'] ?? '', '#5b2fa8');
$sol   = cor($c['sol'] ?? '', '#ffc93c');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Mulish:wght@400;600;700;800&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'PetStore';
$M['cor_tema'] = $dest;
$M['favicon'] = [$dest, $sol];

$portes = array_values(array_filter($f['portes'] ?? [], fn($x) => !empty($x['nome'])));
$portes = array_slice($portes ?: [['nome' => 'Pequeno', 'peso' => 'até 10 kg'], ['nome' => 'Médio', 'peso' => '10 a 25 kg'], ['nome' => 'Grande', 'peso' => 'acima de 25 kg']], 0, 3);
$chaves = ['preco_p', 'preco_m', 'preco_g'];
$servicos = array_values(array_filter($f['servicos'] ?? [], fn($x) => !empty($x['nome'])));
$pacotes = array_values(array_filter($f['pacotes'] ?? [], fn($x) => !empty($x['nome'])));
$vet = array_values(array_filter($f['vet'] ?? [], fn($x) => !empty($x['nome'])));
$leva = (array)($f['leva'] ?? []);
$bairros = array_values(array_filter((array)($leva['bairros'] ?? []), fn($x) => trim((string)$x) !== ''));
$selos = array_values(array_filter((array)($f['selos'] ?? []), fn($x) => trim((string)$x) !== ''));

$M['menu'] = array_values(array_filter([
    $servicos ? ['precos', 'Preços'] : null,
    $pacotes ? ['pacotes', 'Pacotes'] : null,
    $bairros ? ['leva-e-traz', 'Leva e traz'] : null,
    $vet ? ['veterinario', $v('vet_titulo') ?: 'Veterinário'] : null,
    ['duvidas', 'Dúvidas'],
]));

$B['tokens'] = <<<CSS
/* Layout: herói com o simulador de banho por porte, tabela de preços com colunas por porte, pacotes em cartões, leva e traz em faixa escura */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#5d5275; --linha:#e7e0f3;
  --destaque:$dest; --sol:$sol; --verde:#2fb58a; --cta:$dest; --cta-hover:color-mix(in srgb,$dest 82%,#000); --sobre-cta:#ffffff;
  --fonte-titulo:"Baloo 2","Trebuchet MS",system-ui,sans-serif;
  --fonte-texto:"Mulish",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:22px; --raio-botao:999px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent); --rodape-fundo:$tinta;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<svg class="pata" viewBox="0 0 32 32" aria-hidden="true" fill="currentColor"><ellipse cx="16" cy="21.5" rx="7.5" ry="6.5"/><ellipse cx="7" cy="13" rx="3.2" ry="4"/><ellipse cx="12.5" cy="7.5" rx="3.2" ry="4.2"/><ellipse cx="19.5" cy="7.5" rx="3.2" ry="4.2"/><ellipse cx="25" cy="13" rx="3.2" ry="4"/></svg><span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>';

// preço inicial do simulador: primeiro serviço, porte médio
$s0 = $servicos[0] ?? null;
$p0 = $s0[$chaves[1]] ?? ($s0[$chaves[0]] ?? '');

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Agendar banho') ?></span></a>
        <?php if ($servicos): ?><a class="botao botao-contorno" href="#precos">Ver preços</a><?php endif; ?>
      </div>
      <?php if ($selos): ?><ul class="selos"><?php foreach ($selos as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <?php if ($servicos): ?>
    <div class="simulador" id="simulador">
      <h2><?= e($v('simulador_titulo') ?: 'Quanto fica o banho?') ?></h2>
      <fieldset class="sim-grupo">
        <legend>Porte do seu pet</legend>
        <div class="portes">
          <?php foreach ($portes as $i => $pt): ?>
          <label><input type="radio" name="porte" value="<?= $i ?>"<?= $i === 1 ? ' checked' : '' ?>><?= e($pt['nome']) ?><small><?= e($pt['peso'] ?? '') ?></small></label>
          <?php endforeach; ?>
        </div>
      </fieldset>
      <div class="sim-grupo">
        <label class="sim-rotulo" for="sim-servico">Serviço</label>
        <select id="sim-servico">
          <?php foreach ($servicos as $i => $s): ?><option value="<?= $i ?>"><?= e($s['nome']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="sim-preco" aria-live="polite"><span>Valor</span><strong id="sim-valor"><?= e(reais($p0)) ?></strong></div>
      <a class="botao botao-cta" id="sim-botao" href="<?= e($zap('Olá! Quero agendar ' . ($s0['nome'] ?? 'um banho') . ' para meu pet de porte ' . mb_strtolower($portes[1]['nome'] ?? '') . '.')) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Agendar este serviço</span></a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($servicos): ?>
<section class="secao" id="precos" aria-labelledby="t-precos">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-precos" class="titulo-secao"><?= e($v('precos_titulo') ?: 'Preços por porte') ?></h2>
      <?php if ($v('precos_intro')): ?><p><?= e($v('precos_intro')) ?></p><?php endif; ?>
    </div>
    <div class="tabela-precos">
      <table>
        <thead><tr><th scope="col">Serviço</th><?php foreach ($portes as $pt): ?><th scope="col"><?= e($pt['nome']) ?><small><?= e($pt['peso'] ?? '') ?></small></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($servicos as $s): ?>
          <tr><th scope="row"><?= e($s['nome']) ?><?php if (!empty($s['descricao'])): ?><small><?= e($s['descricao']) ?></small><?php endif; ?></th>
            <?php foreach (array_slice($chaves, 0, count($portes)) as $k): ?><td><?= ($s[$k] ?? '') !== '' ? e(reais($s[$k])) : '—' ?></td><?php endforeach; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($v('precos_nota')): ?><p class="precos-nota"><?= e($v('precos_nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($pacotes): ?>
<section class="secao secao-pacotes" id="pacotes" aria-labelledby="t-pac">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-pac" class="titulo-secao"><?= e($v('pacotes_titulo') ?: 'Pacotes mensais') ?></h2>
      <?php if ($v('pacotes_intro')): ?><p><?= e($v('pacotes_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="pacotes">
      <?php foreach ($pacotes as $p): ?>
      <li class="pacote<?= !empty($p['destaque']) ? ' destaque' : '' ?>">
        <?php if (!empty($p['tag'])): ?><span class="pacote-tag"><?= e($p['tag']) ?></span><?php endif; ?>
        <h3><?= e($p['nome']) ?></h3>
        <p><?= e($p['descricao'] ?? '') ?></p>
        <span class="pacote-preco"><?php if (($p['preco'] ?? '') !== ''): ?><?= e(reais($p['preco'])) ?> <small><?= e($p['sufixo'] ?? '') ?></small><?php else: ?>Consulte<?php endif; ?></span>
        <a href="<?= e($zap('Olá! Tenho interesse no pacote ' . $p['nome'] . '.')) ?>" target="_blank" rel="noopener">Quero este pacote</a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($bairros): ?>
<section class="secao secao-leva" id="leva-e-traz" aria-labelledby="t-leva">
  <div class="embrulho leva">
    <div>
      <h2 id="t-leva" class="titulo-secao"><?= e($v('leva.titulo') ?: 'Leva e traz') ?></h2>
      <div class="leva-dados">
        <?php if (!empty($leva['texto'])): ?><p><?= e($leva['texto']) ?></p><?php endif; ?>
        <?php if (isset($leva['taxa']) && $leva['taxa'] !== ''): ?><p>Taxa: <b><?= (float)$leva['taxa'] > 0 ? e(reais($leva['taxa'])) : 'grátis' ?></b><?= !empty($leva['taxa_obs']) ? ' · ' . e($leva['taxa_obs']) : '' ?></p><?php endif; ?>
      </div>
    </div>
    <div><ul class="bairros"><?php foreach ($bairros as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul></div>
  </div>
</section>
<?php endif; ?>

<?php if ($vet): ?>
<section class="secao" id="veterinario" aria-labelledby="t-vet">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-vet" class="titulo-secao"><?= e($v('vet_titulo') ?: 'Veterinário') ?></h2>
      <?php if ($v('vet_intro')): ?><p><?= e($v('vet_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="vet-lista">
      <?php foreach ($vet as $x): ?><li><strong><?= e($x['nome']) ?></strong><em><?= is_numeric($x['preco'] ?? '') ? e(reais($x['preco'])) : e($x['preco'] ?? '') ?></em><?php if (!empty($x['descricao'])): ?><span><?= e($x['descricao']) ?></span><?php endif; ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

if ($servicos) {
    $dados = json_seguro(array_map(fn($s) => ['nome' => $s['nome'], 'precos' => array_map(fn($k) => ($s[$k] ?? '') === '' ? null : (float)$s[$k], $chaves)], $servicos), false);
    $nomesPortes = json_seguro(array_map(fn($p) => mb_strtolower($p['nome']), $portes), false);
    $zapBase = json_seguro('https://wa.me/' . $whats . '?text=', false);
    $B['script'] = <<<JS
<script>
(function(){
  var S=$dados, P=$nomesPortes, zap=$zapBase;
  var sel=document.getElementById('sim-servico'), val=document.getElementById('sim-valor'), bt=document.getElementById('sim-botao');
  function brl(n){return 'R$ '+(Number.isInteger(n)?n.toLocaleString('pt-BR'):n.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}))}
  function atualiza(){
    var r=document.querySelector('input[name=porte]:checked'), pi=r?+r.value:0, s=S[+sel.value];
    var p=s.precos[pi];
    val.textContent=p==null?'Sob consulta':brl(p);
    var msg='Olá! Quero agendar '+s.nome+' para meu pet de porte '+P[pi]+(p==null?'.':' ('+brl(p)+').');
    bt.href=zap+encodeURIComponent(msg);
  }
  document.getElementById('simulador').addEventListener('change',atualiza);
  atualiza();
})();
</script>
JS;
}
