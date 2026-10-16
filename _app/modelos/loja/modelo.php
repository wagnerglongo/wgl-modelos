<?php
/**
 * Modelo LOJA — semijoias, bijuterias, moda, cosméticos, presentes, artesanato.
 * Marfim, vinho e bronze; Cormorant Garamond + Jost.
 * Vitrine com filtro por categoria e sacola: a pessoa escolhe as peças, ajusta as quantidades e o pedido
 * sai pronto no WhatsApp (peças, códigos, valores e total). Nada é pago nem gravado no servidor:
 * a sacola fica só no navegador de quem está comprando.
 * Foto de cada produto = número da foto na seção Fotos; sem foto, aparece um desenho da categoria.
 */
$fundo = cor($c['fundo'] ?? '', '#fbf7f2');
$tinta = cor($c['tinta'] ?? '', '#2a1d1f');
$dest  = cor($c['destaque'] ?? '', '#8a6127');
$botao = cor($c['botao'] ?? '', '#5a2733');

$M['fontes'] = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500;1,600&family=Jost:wght@400;500;600&display=swap';
$M['tipo_schema'] = $v('tipo_schema') ?: 'JewelryStore';
$M['cor_tema'] = $botao;
$M['favicon'] = [$botao, '#fbf7f2'];
$M['ocultar'] = ['fotos']; // as fotos viram fotos dos produtos, sem galeria solta

$limpa = fn($lista) => array_values(array_filter((array)$lista, fn($x) => trim((string)$x) !== ''));
$selos = $limpa($f['selos'] ?? []);
$pagamento = $limpa($f['pagamento'] ?? []);
$envio = $limpa($f['envio'] ?? []);
$passos = array_values(array_filter($f['passos'] ?? [], fn($x) => !empty($x['titulo'])));
$faqTem = (bool)array_filter($f['faq'] ?? [], fn($x) => !empty($x['pergunta']));
$fotosLista = array_values(array_filter($f['fotos'] ?? [], fn($x) => !empty($x['arquivo'])));

/** Desenho de linha usado quando o produto não tem foto, escolhido pela categoria. */
$tipoDesenho = function (string $cat): string {
    $c = slugify($cat);
    foreach (['brinco' => ['brinc', 'argol', 'ear'], 'colar' => ['colar', 'corrent', 'chok', 'pingent', 'gargant'],
              'anel' => ['anel', 'aneis', 'alianc'], 'pulseira' => ['pulseir', 'bracelet', 'tornozel']] as $tipo => $chaves) {
        foreach ($chaves as $k) { if (str_contains($c, $k)) return $tipo; }
    }
    return 'brilho';
};
$desenho = function (string $tipo): string {
    $p = [
        'brinco'   => '<path d="M38 18c0-7 10-7 10 0v12"/><circle cx="43" cy="37" r="5"/><path d="M43 42C33 55 29 63 29 71a14 14 0 0 0 28 0c0-8-4-16-14-29z"/><path d="M76 30c0-6 8-6 8 0v9"/><circle cx="80" cy="44" r="4"/><path d="M80 48c-8 10-11 17-11 23a11 11 0 0 0 22 0c0-6-3-13-11-23z"/>',
        'colar'    => '<path d="M18 16c4 40 24 60 42 60s38-20 42-60"/><path d="M60 76v6"/><path d="M60 82l-9 11 9 13 9-13z"/>',
        'anel'     => '<ellipse cx="60" cy="74" rx="28" ry="26"/><ellipse cx="60" cy="74" rx="21" ry="19"/><path d="M48 48l12-16 12 16-12 7z"/><path d="M48 48h24"/>',
        'pulseira' => '<ellipse cx="60" cy="64" rx="42" ry="19"/><ellipse cx="60" cy="58" rx="42" ry="19"/><circle cx="30" cy="72" r="3"/><circle cx="60" cy="78" r="3"/><circle cx="90" cy="72" r="3"/>',
        'brilho'   => '<path d="M60 18l7 33 33 9-33 9-7 33-7-33-33-9 33-9z"/><path d="M92 20l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>',
    ];
    return '<svg viewBox="0 0 120 120" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">' . ($p[$tipo] ?? $p['brilho']) . '</svg>';
};

$produtos = [];
foreach (($f['produtos'] ?? []) as $i => $p) {
    if (empty($p['nome'])) continue;
    $n = (int)($p['foto'] ?? 0);
    $foto = ($n >= 1 && isset($fotosLista[$n - 1])) ? $fotosLista[$n - 1] : null;
    $preco = (isset($p['preco']) && is_numeric($p['preco'])) ? (float)$p['preco'] : null;
    $de = (isset($p['preco_de']) && is_numeric($p['preco_de']) && $preco !== null && (float)$p['preco_de'] > $preco) ? (float)$p['preco_de'] : null;
    $cat = trim((string)($p['categoria'] ?? '')) ?: 'Outros';
    $produtos[] = $p + [
        'id'    => 'p' . count($produtos),
        'cat'   => $cat,
        'catId' => slugify($cat),
        'tipo'  => $tipoDesenho($cat),
        'img'   => $foto ? 'fotos/' . $foto['arquivo'] : '',
        'alt'   => $foto ? (($foto['descricao'] ?? '') ?: $p['nome']) : '',
        'valor' => $preco,
        'de'    => $de,
    ];
}
$categorias = [];
foreach ($produtos as $p) { $categorias[$p['catId']] = $p['cat']; }
$vitrine = array_values(array_filter($produtos, fn($p) => !empty($p['destaque']) && empty($p['esgotado'])));
$vitrine = array_slice($vitrine ?: array_values(array_filter($produtos, fn($p) => empty($p['esgotado']))), 0, 3);
$temSacola = (bool)array_filter($produtos, fn($p) => $p['valor'] !== null && empty($p['esgotado']));

$imagem = function (array $p, string $classe = '') use ($desenho): string {
    if ($p['img'] !== '') {
        return '<img src="' . e($p['img']) . '" alt="' . e($p['alt']) . '" loading="lazy" width="600" height="600"' . ($classe ? ' class="' . $classe . '"' : '') . '>';
    }
    return '<span class="desenho desenho-' . e($p['tipo']) . '">' . $desenho($p['tipo']) . '</span>';
};

$M['menu'] = array_values(array_filter([
    $produtos ? ['colecao', 'Coleção'] : null,
    ($passos || $pagamento || $envio) ? ['como-comprar', $v('compra_titulo') ?: 'Como comprar'] : null,
    $faqTem ? ['duvidas', 'Dúvidas'] : null,
    ['contato', 'Contato'],
]));

$B['tokens'] = <<<CSS
/* Layout: abertura com a vitrine de 3 peças, faixa de selos, coleção com filtros e sacola lateral */
:root{
  --fundo:$fundo; --superficie:#ffffff; --tinta:$tinta; --tinta-suave:#6b5853; --linha:#eadfd3; --champanhe:#f4e9dc;
  --destaque:$dest; --cta:$botao; --cta-hover:color-mix(in srgb,$botao 82%,#000); --sobre-cta:#ffffff; --foco:$botao;
  --fonte-titulo:"Cormorant Garamond",Georgia,"Times New Roman",serif;
  --fonte-texto:"Jost",system-ui,-apple-system,"Segoe UI",sans-serif;
  --raio:6px; --raio-botao:999px; --topo-fundo:color-mix(in srgb,var(--fundo) 92%,transparent); --rodape-fundo:$tinta;
}
CSS;
$B['estilo'] = (string)file_get_contents(__DIR__ . '/estilo.css');
$B['marca'] = '<span class="losango" aria-hidden="true"></span><span class="marca-nome">' . e($f['nome_curto'] ?: $f['nome']) . '</span>';

ob_start(); ?>
<section class="hero" id="inicio" aria-labelledby="t-hero">
  <div class="embrulho hero-grade">
    <div class="hero-texto">
      <?php if ($v('hero.rotulo')): ?><span class="rotulo"><?= e($v('hero.rotulo')) ?></span><?php endif; ?>
      <h1 id="t-hero"><?= e($v('hero.titulo')) ?><?php if ($v('hero.titulo_destaque')): ?> <em><?= e($v('hero.titulo_destaque')) ?></em><?php endif; ?></h1>
      <?php if ($v('hero.subtitulo')): ?><p class="hero-sub"><?= e($v('hero.subtitulo')) ?></p><?php endif; ?>
      <div class="hero-acoes">
        <?php if ($produtos): ?><a class="botao botao-cta" href="#colecao"><?= e($v('hero.cta') ?: 'Ver a coleção') ?></a><?php endif; ?>
        <a class="botao botao-contorno" href="<?= e($zap()) ?>" target="_blank" rel="noopener"><?= $ICONE ?><span>Tirar uma dúvida</span></a>
      </div>
    </div>
    <?php if ($vitrine): ?>
    <ul class="vitrine" aria-label="Peças em destaque">
      <?php foreach ($vitrine as $p): ?>
      <li class="vitrine-peca">
        <a href="#produto-<?= e($p['id']) ?>">
          <span class="vitrine-foto"><?= $imagem($p) ?></span>
          <span class="vitrine-legenda"><span><?= e($p['nome']) ?></span><?php if ($p['valor'] !== null): ?><b><?= e(reais($p['valor'])) ?></b><?php endif; ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
  <?php if ($selos): ?>
  <div class="selos"><ul class="embrulho"><?php foreach ($selos as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
</section>
<?php $B['hero'] = ob_get_clean();

ob_start(); ?>
<?php if ($produtos): ?>
<section class="secao secao-colecao" id="colecao" aria-labelledby="t-colecao">
  <div class="embrulho">
    <div class="cab-secao">
      <h2 id="t-colecao" class="titulo-secao"><?= e($v('colecao_titulo') ?: 'A coleção') ?></h2>
      <?php if ($v('colecao_intro')): ?><p><?= e($v('colecao_intro')) ?></p><?php endif; ?>
    </div>
    <?php if (count($categorias) > 1): ?>
    <div class="filtros" role="group" aria-label="Filtrar por categoria">
      <button type="button" aria-pressed="true" data-filtro="">Todas</button>
      <?php foreach ($categorias as $id => $nome): ?><button type="button" aria-pressed="false" data-filtro="<?= e($id) ?>"><?= e($nome) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <ul class="produtos">
      <?php foreach ($produtos as $p): $oferta = $p['de'] ? (int)round((1 - $p['valor'] / $p['de']) * 100) : 0; ?>
      <li class="produto<?= !empty($p['esgotado']) ? ' esgotado' : '' ?>" id="produto-<?= e($p['id']) ?>" data-cat="<?= e($p['catId']) ?>">
        <div class="produto-foto">
          <?= $imagem($p) ?>
          <span class="produto-tags">
            <?php if (!empty($p['esgotado'])): ?><span class="tag tag-esgotado">Esgotado</span>
            <?php else: ?>
              <?php if (!empty($p['novidade'])): ?><span class="tag">Novidade</span><?php endif; ?>
              <?php if ($oferta > 0): ?><span class="tag tag-oferta">−<?= $oferta ?>%</span><?php endif; ?>
            <?php endif; ?>
          </span>
        </div>
        <div class="produto-info">
          <p class="produto-cat"><?= e($p['cat']) ?></p>
          <h3><?= e($p['nome']) ?></h3>
          <?php if (!empty($p['detalhe'])): ?><p class="produto-detalhe"><?= e($p['detalhe']) ?></p><?php endif; ?>
          <p class="produto-preco"><?php if ($p['de']): ?><del aria-label="De <?= e(reais($p['de'])) ?>"><?= e(reais($p['de'])) ?></del> <?php endif; ?><strong><?= $p['valor'] !== null ? e(reais($p['valor'])) : 'Consulte' ?></strong></p>
          <?php if (!empty($p['esgotado'])): ?>
            <a class="produto-botao produto-avisar" href="<?= e($zap('Olá! Vi no site o ' . $p['nome'] . (!empty($p['codigo']) ? ' (ref. ' . $p['codigo'] . ')' : '') . ', que está esgotado. Pode me avisar quando chegar?')) ?>" target="_blank" rel="noopener">Avise-me quando chegar</a>
          <?php elseif ($p['valor'] === null): ?>
            <a class="produto-botao" href="<?= e($zap('Olá! Quero saber o preço do ' . $p['nome'] . (!empty($p['codigo']) ? ' (ref. ' . $p['codigo'] . ')' : '') . '.')) ?>" target="_blank" rel="noopener">Consultar preço</a>
          <?php else: ?>
            <button type="button" class="produto-botao produto-add" data-id="<?= e($p['id']) ?>"><span class="rotulo-add">Adicionar<span class="so-largo"> à sacola</span></span></button>
          <?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($passos || $pagamento || $envio): ?>
<section class="secao secao-compra" id="como-comprar" aria-labelledby="t-compra">
  <div class="embrulho">
    <h2 id="t-compra" class="titulo-secao"><?= e($v('compra_titulo') ?: 'Como comprar') ?></h2>
    <?php if ($passos): ?>
    <ol class="passos">
      <?php foreach ($passos as $p): ?><li><h3><?= e($p['titulo']) ?></h3><?php if (!empty($p['texto'])): ?><p><?= e($p['texto']) ?></p><?php endif; ?></li><?php endforeach; ?>
    </ol>
    <?php endif; ?>
    <?php if ($pagamento || $envio): ?>
    <div class="condicoes">
      <?php if ($pagamento): ?><div><h3>Pagamento</h3><ul><?php foreach ($pagamento as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <?php if ($envio): ?><div><h3>Entrega</h3><ul><?php foreach ($envio as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php $B['principal'] = ob_get_clean();

if ($temSacola) {
    $dadosJs = json_seguro(array_combine(
        array_column($produtos, 'id'),
        array_map(fn($p) => ['nome' => $p['nome'], 'preco' => $p['valor'], 'codigo' => (string)($p['codigo'] ?? ''), 'ativo' => $p['valor'] !== null && empty($p['esgotado'])], $produtos)
    ), false);
    $zapBase = json_seguro('https://wa.me/' . $whats . '?text=', false);
    $nomeLoja = json_seguro($f['nome'] ?? '', false);
    $chave = json_seguro('sacola-' . ($f['slug'] ?? 'loja'), false);
    $obsPedido = $v('pedido_minimo');
    $ICONE_SACOLA = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14l-1.2 11.2a2 2 0 0 1-2 1.8H8.2a2 2 0 0 1-2-1.8z"/><path d="M9 10V7a3 3 0 0 1 6 0v3"/></svg>';
    ob_start(); ?>
<button type="button" class="sacola-abrir" hidden aria-haspopup="dialog"><?= $ICONE_SACOLA ?><span class="sacola-qtd" aria-hidden="true">0</span><span class="sr">Abrir a sacola</span></button>
<div class="aviso-sacola" role="status" aria-live="polite"><span class="aviso-texto"></span><button type="button" class="aviso-ver">Ver sacola</button></div>
<dialog class="sacola" aria-labelledby="t-sacola">
  <div class="sacola-cab">
    <h2 id="t-sacola">Sua sacola</h2>
    <button type="button" class="sacola-fechar" aria-label="Fechar a sacola">×</button>
  </div>
  <div class="sacola-corpo">
    <ul class="sacola-itens"></ul>
    <p class="sacola-vazia">Sua sacola está vazia. Escolha as peças na coleção e elas aparecem aqui.</p>
  </div>
  <form class="sacola-pe">
    <p class="sacola-total"><span>Total das peças</span><strong>R$ 0</strong></p>
    <?php if ($obsPedido): ?><p class="sacola-obs"><?= e($obsPedido) ?></p><?php endif; ?>
    <div class="sacola-campos">
      <label>Seu nome <input name="nome" autocomplete="name"></label>
      <label>Cidade ou CEP <input name="local" autocomplete="postal-code"></label>
      <label class="largo">Observação <textarea name="obs" rows="2" placeholder="Ex.: tamanho do anel, é para presente"></textarea></label>
    </div>
    <button type="submit" class="botao botao-cta"><?= $ICONE ?><span>Enviar pedido pelo WhatsApp</span></button>
    <p class="sacola-nota">Nada é pago no site. Você combina o pagamento e o frete com a loja pelo WhatsApp. <button type="button" class="sacola-limpar">Esvaziar sacola</button></p>
  </form>
</dialog>
<script>
(function(){
  var P=<?= $dadosJs ?>, CHAVE=<?= $chave ?>, ZAP=<?= $zapBase ?>, LOJA=<?= $nomeLoja ?>;
  var itens=ler();
  function ler(){try{var s=JSON.parse(localStorage.getItem(CHAVE)||'{}'),o={};for(var k in s){if(P[k]&&P[k].ativo&&s[k]>0)o[k]=Math.min(99,s[k]|0)}return o}catch(e){return {}}}
  function gravar(){try{localStorage.setItem(CHAVE,JSON.stringify(itens))}catch(e){}}
  function brl(n){return n.toLocaleString('pt-BR',{style:'currency',currency:'BRL'})}
  function qtd(){var t=0;for(var k in itens)t+=itens[k];return t}
  function total(){var t=0;for(var k in itens)t+=itens[k]*P[k].preco;return t}
  var dlg=document.querySelector('.sacola'),lista=dlg.querySelector('.sacola-itens'),vazia=dlg.querySelector('.sacola-vazia'),form=dlg.querySelector('.sacola-pe');
  var abrir=document.querySelector('.sacola-abrir'),aviso=document.querySelector('.aviso-sacola'),timer;
  function el(tag,cls,txt){var x=document.createElement(tag);if(cls)x.className=cls;if(txt!=null)x.textContent=txt;return x}
  function desenha(){
    lista.innerHTML='';
    Object.keys(itens).forEach(function(k){
      var p=P[k],li=el('li','sacola-item');
      var info=el('div','sacola-info');info.appendChild(el('strong',null,p.nome));
      info.appendChild(el('span',null,brl(p.preco)+(p.codigo?' · ref. '+p.codigo:'')));
      var ctrl=el('div','sacola-qtd-ctrl');
      var menos=el('button',null,'−');menos.type='button';menos.setAttribute('aria-label','Diminuir a quantidade de '+p.nome);
      var n=el('span','sacola-n',String(itens[k]));n.setAttribute('aria-label',itens[k]+' unidade(s)');
      var mais=el('button',null,'+');mais.type='button';mais.setAttribute('aria-label','Aumentar a quantidade de '+p.nome);
      menos.onclick=function(){muda(k,-1)};mais.onclick=function(){muda(k,1)};
      ctrl.appendChild(menos);ctrl.appendChild(n);ctrl.appendChild(mais);
      li.appendChild(info);li.appendChild(ctrl);li.appendChild(el('span','sacola-sub',brl(p.preco*itens[k])));
      lista.appendChild(li);
    });
    var q=qtd();
    vazia.hidden=q>0;form.hidden=q===0;
    form.querySelector('.sacola-total strong').textContent=brl(total());
    abrir.hidden=q===0;abrir.querySelector('.sacola-qtd').textContent=q;
    abrir.querySelector('.sr').textContent='Abrir a sacola, '+q+(q===1?' peça':' peças');
    document.querySelectorAll('.produto-add').forEach(function(b){
      var k=b.getAttribute('data-id'),r=b.querySelector('.rotulo-add');
      if(!r.dataset.orig)r.dataset.orig=r.innerHTML;
      b.classList.toggle('na-sacola',!!itens[k]);
      if(itens[k]){r.textContent='✓ Na sacola ('+itens[k]+')';b.setAttribute('aria-label','Adicionar mais um '+P[k].nome+'. Já tem '+itens[k]+' na sacola')}
      else{r.innerHTML=r.dataset.orig;b.removeAttribute('aria-label')}
    });
  }
  function muda(k,d){itens[k]=(itens[k]||0)+d;if(itens[k]<=0)delete itens[k];if(itens[k]>99)itens[k]=99;gravar();desenha()}
  function mostrar(){aviso.classList.remove('mostrar');if(dlg.showModal){dlg.showModal()}else{dlg.setAttribute('open','')}}
  function avisar(txt){aviso.querySelector('.aviso-texto').textContent=txt;aviso.classList.add('mostrar');clearTimeout(timer);timer=setTimeout(function(){aviso.classList.remove('mostrar')},3500)}
  document.querySelectorAll('.produto-add').forEach(function(b){
    b.addEventListener('click',function(){var k=b.getAttribute('data-id');muda(k,1);avisar(P[k].nome+' foi para a sacola.')});
  });
  abrir.addEventListener('click',mostrar);
  aviso.querySelector('.aviso-ver').addEventListener('click',function(){aviso.classList.remove('mostrar');mostrar()});
  dlg.querySelector('.sacola-fechar').addEventListener('click',function(){dlg.close()});
  dlg.addEventListener('click',function(e){if(e.target===dlg)dlg.close()});
  dlg.querySelector('.sacola-limpar').addEventListener('click',function(){itens={};gravar();desenha()});
  form.addEventListener('submit',function(e){
    e.preventDefault();
    var linhas=['Olá! Quero fazer este pedido pelo site da '+LOJA+':',''];
    Object.keys(itens).forEach(function(k){var p=P[k];linhas.push('• '+itens[k]+'x '+p.nome+(p.codigo?' (ref. '+p.codigo+')':'')+' — '+brl(p.preco*itens[k]))});
    linhas.push('','Total das peças: '+brl(total()));
    var nome=form.nome.value.trim(),local=form.local.value.trim(),obs=form.obs.value.trim();
    if(nome||local||obs)linhas.push('');
    if(nome)linhas.push('Nome: '+nome);
    if(local)linhas.push('Envio para: '+local);
    if(obs)linhas.push('Observação: '+obs);
    linhas.push('','Pode me passar o frete e as formas de pagamento?');
    var url=ZAP+encodeURIComponent(linhas.join('\n'));
    var w=window.open(url,'_blank');if(w){w.opener=null}else{location.href=url}
  });
  desenha();
})();
</script>
<?php
    $scriptSacola = ob_get_clean();
} else {
    $scriptSacola = '';
}

$scriptFiltro = count($categorias) > 1 ? <<<JS
<script>
(function(){
  var bts=document.querySelectorAll('.filtros button'),itens=document.querySelectorAll('.produto');
  bts.forEach(function(b){b.addEventListener('click',function(){
    var f=b.getAttribute('data-filtro');
    bts.forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false')});
    itens.forEach(function(li){li.hidden=!!f&&li.getAttribute('data-cat')!==f});
  })});
})();
</script>
JS : '';

$B['script'] = $scriptSacola . $scriptFiltro;
