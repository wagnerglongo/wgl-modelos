<?php
/** Modelo SERVIÇOS TÉCNICOS — refrigeração, elétrica, reformas, esquadrias, oficinas. Visual de ficha técnica com grade de planta. */
$fundo  = cor($c['fundo'] ?? '', '#f4f7f9');
$tinta  = cor($c['tinta'] ?? '', '#0f2233');
$dest   = cor($c['destaque'] ?? '', '#0b5d8f');
$cta    = cor($c['cta'] ?? '', '#f47b2a');
$ctaHov = cor($c['cta_hover'] ?? '', '#e56a17');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,400..800&family=IBM+Plex+Mono:wght@500;600&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'HomeAndConstructionBusiness';
$M['cor_tema'] = $tinta;
$M['favicon'] = [$dest, '#ffffff'];
$calc = (array)($f['calculadora'] ?? []);
$calcAtiva = !empty($calc['ativa']);
$M['menu'] = array_values(array_filter([
    $calcAtiva ? ['calculadora', 'Calcular BTUs'] : null,
    ['servicos', 'Serviços'],
    !empty($f['etapas']) ? ['como-funciona', 'Como funciona'] : null,
    !empty($f['areas_atendidas']) ? ['area', 'Onde atendemos'] : null,
    ['duvidas', 'Dúvidas'],
]));

$B['tokens'] = <<<CSS
/* Layout: herói com grade de planta e ferramenta útil ao lado (calculadora), serviços em fichas, etapas em linha */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#4a5d6c; --linha:#d5dfe6;
  --destaque:$dest; --cta:$cta; --cta-hover:$ctaHov; --sobre-cta:$tinta; --grade:color-mix(in srgb,$dest 7%,transparent);
  --fonte-titulo:"Archivo","Arial Black",system-ui,sans-serif;
  --fonte-texto:"Archivo",system-ui,-apple-system,"Segoe UI",sans-serif;
  --fonte-util:"IBM Plex Mono",ui-monospace,"Cascadia Mono",Consolas,monospace;
  --raio:10px; --raio-botao:8px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent);
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$nc = $f['nome_curto'] ?: $f['nome'];
$B['marca'] = '<span class="selo" aria-hidden="true">' . e(mb_substr($nc, 0, 1)) . '</span><span class="marca-nome">' . e($nc) . '</span>';

// cálculo inicial da calculadora (o mesmo que o JavaScript faz)
$cArea = 12; $cPessoas = 2; $cAparelhos = 1;
$cTotal = $cArea * 600 + ($cPessoas - 1) * 600 + $cAparelhos * 600;
$cRes = 'Acima de 60.000';
foreach ([9000, 12000, 18000, 24000, 30000, 36000, 48000, 60000] as $t) {
    if ($t >= $cTotal) { $cRes = number_format($t, 0, ',', '.'); break; }
}
$garantias = array_values(array_filter((array)($f['garantias'] ?? []), fn($x) => trim((string)$x) !== ''));

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div>
      <span class="rotulo"><?= e($v('hero.rotulo')) ?></span>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?></h1>
      <p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p>
      <div class="hero-acoes">
        <a class="botao botao-cta" href="<?= e($zap($v('hero.cta_msg') ?: null)) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span><?= e($v('hero.cta') ?: 'Pedir orçamento') ?></span></a>
        <a class="botao botao-contorno" href="#servicos">Ver serviços e preços</a>
      </div>
      <?php if ($garantias): ?><ul class="garantias"><?php foreach ($garantias as $gr): ?><li><?= e($gr) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <?php if ($calcAtiva): ?>
    <div class="calc" id="calculadora">
      <div class="calc-cab"><h2><?= e($calc['titulo'] ?? '' ?: 'Quantos BTUs eu preciso?') ?></h2><span>Calculadora</span></div>
      <form id="form-btu" novalidate>
        <div class="campo"><label for="btu-area">Área (m²)</label><input id="btu-area" type="number" inputmode="decimal" min="1" max="200" value="<?= $cArea ?>"></div>
        <div class="campo"><label for="btu-pessoas">Pessoas</label><input id="btu-pessoas" type="number" inputmode="numeric" min="1" max="30" value="<?= $cPessoas ?>"></div>
        <div class="campo"><label for="btu-aparelhos">TVs e PCs</label><input id="btu-aparelhos" type="number" inputmode="numeric" min="0" max="20" value="<?= $cAparelhos ?>"></div>
        <fieldset class="campo campo-sol">
          <legend>Bate sol à tarde?</legend>
          <div class="opcoes">
            <label><input type="radio" name="sol" id="btu-sol-nao" value="600" checked> Não</label>
            <label><input type="radio" name="sol" id="btu-sol-sim" value="800"> Sim</label>
          </div>
        </fieldset>
      </form>
      <div class="calc-resultado" aria-live="polite">
        <p>Aparelho recomendado</p>
        <div class="calc-btu" id="btu-resultado"><?= $cRes ?> <small>BTUs</small></div>
        <a class="botao botao-cta" id="btu-botao" href="<?= e($zap('Olá! Fiz a conta no site e deu ' . $cRes . ' BTUs. Quero um orçamento de instalação.')) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Pedir orçamento deste aparelho</span></a>
        <p class="calc-aviso">Estimativa: 600 BTUs por m² (800 com sol à tarde), mais 600 por pessoa extra e por aparelho. A medida final é confirmada na visita técnica.</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php $B['hero'] = ob_get_clean();

$servicos = array_values(array_filter($f['servicos'] ?? [], fn($s) => !empty($s['nome'])));
$etapas = array_values(array_filter($f['etapas'] ?? [], fn($s) => !empty($s['titulo'])));
$areas = array_values(array_filter((array)($f['areas_atendidas'] ?? []), fn($x) => trim((string)$x) !== ''));
ob_start(); ?>
<section class="secao" id="servicos" aria-labelledby="t-serv">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-serv" class="titulo-secao"><?= e($v('servicos_titulo') ?: 'Serviços e preços') ?></h2>
      <?php if ($v('servicos_intro')): ?><p><?= e($v('servicos_intro')) ?></p><?php endif; ?>
    </div>
    <ul class="fichas">
      <?php foreach ($servicos as $s): $preco = $s['preco'] ?? ''; $inclui = array_filter((array)($s['inclui'] ?? []), fn($x) => trim((string)$x) !== ''); ?>
      <li class="ficha">
        <h3><?= e($s['nome']) ?></h3>
        <?php if ($preco !== ''): ?>
        <div class="ficha-preco"><?php if (is_numeric($preco)): ?><small><?= !empty($s['a_partir']) ? 'a partir de' : 'preço fechado' ?></small><?= e(reais($preco)) ?><?php else: ?><small>valor</small><?= e($preco) ?><?php endif; ?></div>
        <?php endif; ?>
        <p><?= e($s['descricao'] ?? '') ?></p>
        <?php if ($inclui): ?><ul><?php foreach ($inclui as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <a href="<?= e($zap('Olá! Quero um orçamento de: ' . $s['nome'] . '.')) ?>" target="_blank" rel="noopener">Pedir orçamento</a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php if ($etapas): ?>
<section class="secao secao-etapas" id="como-funciona" aria-labelledby="t-etapas">
  <div class="embrulho">
    <h2 id="t-etapas" class="titulo-secao">Como funciona</h2>
    <ol class="etapas">
      <?php foreach ($etapas as $et): ?><li><strong><?= e($et['titulo']) ?></strong><span><?= e($et['texto'] ?? '') ?></span></li><?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php if ($areas): ?>
<section class="secao" id="area" aria-labelledby="t-area">
  <div class="embrulho">
    <h2 id="t-area" class="titulo-secao">Onde atendemos</h2>
    <ul class="areas"><?php foreach ($areas as $a): ?><li><?= e($a) ?></li><?php endforeach; ?></ul>
    <?php if ($v('areas_nota')): ?><p class="areas-nota"><?= e($v('areas_nota')) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

if ($calcAtiva) {
    $zapBase = json_seguro('https://wa.me/' . $whats . '?text=', false);
    $B['script'] = <<<JS
<script>
(function(){
  var tamanhos=[9000,12000,18000,24000,30000,36000,48000,60000];
  var zap=$zapBase;
  var f=function(n){return n.toLocaleString('pt-BR')};
  var el=function(id){return document.getElementById(id)};
  function calcula(){
    var area=parseFloat((el('btu-area').value||'0').replace(',','.'))||0;
    var pessoas=Math.max(1,parseInt(el('btu-pessoas').value||'1',10)||1);
    var aparelhos=Math.max(0,parseInt(el('btu-aparelhos').value||'0',10)||0);
    var porM2=el('btu-sol-sim').checked?800:600;
    var total=area*porM2+(pessoas-1)*600+aparelhos*600;
    var rec=tamanhos.find(function(t){return t>=total});
    var texto, msg;
    if(!area){texto='—';msg='Olá! Quero ajuda para escolher o ar-condicionado.';}
    else if(!rec){texto='Acima de 60.000';msg='Olá! Fiz a conta no site e deu mais de 60.000 BTUs ('+area+' m²). Preciso de um projeto.';}
    else{texto=f(rec);msg='Olá! Fiz a conta no site: cômodo de '+area+' m², '+pessoas+' pessoa(s), '+(porM2===800?'com':'sem')+' sol à tarde. Deu '+f(rec)+' BTUs. Quero um orçamento de instalação.';}
    el('btu-resultado').innerHTML=texto+' <small>BTUs</small>';
    el('btu-botao').href=zap+encodeURIComponent(msg);
  }
  el('form-btu').addEventListener('input',calcula);
  el('form-btu').addEventListener('submit',function(e){e.preventDefault()});
  calcula();
})();
</script>
JS;
}
