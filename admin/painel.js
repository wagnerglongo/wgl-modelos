/* Painel WGL Modelos — editor de ficha.
   Monta o formulário a partir do schema do modelo e salva a ficha em JSON. */
(function () {
  'use strict';
  var D = JSON.parse(document.getElementById('dados-editor').textContent);
  var ficha = JSON.parse(JSON.stringify(D.ficha));
  var form = document.getElementById('ficha');
  var indice = document.getElementById('indice');
  var estado = document.getElementById('estado');
  var botaoSalvar = document.getElementById('salvar');
  var sujo = false, salvando = false, contador = 0;

  var DIAS = [['seg', 'Seg'], ['ter', 'Ter'], ['qua', 'Qua'], ['qui', 'Qui'], ['sex', 'Sex'], ['sab', 'Sáb'], ['dom', 'Dom']];

  // ---------- utilidades ----------
  function el(tag, attrs, filhos) {
    var n = document.createElement(tag);
    if (attrs) Object.keys(attrs).forEach(function (k) {
      if (k === 'text') n.textContent = attrs[k];
      else if (k === 'class') n.className = attrs[k];
      else if (k.slice(0, 2) === 'on') n.addEventListener(k.slice(2), attrs[k]);
      else if (attrs[k] !== undefined && attrs[k] !== null && attrs[k] !== false) n.setAttribute(k, attrs[k] === true ? '' : attrs[k]);
    });
    (filhos || []).forEach(function (f) { if (f) n.appendChild(typeof f === 'string' ? document.createTextNode(f) : f); });
    return n;
  }
  function pegar(obj, caminho) {
    return caminho.split('.').reduce(function (o, k) { return (o && typeof o === 'object') ? o[k] : undefined; }, obj);
  }
  function por(obj, caminho, valor) {
    var partes = caminho.split('.'), o = obj;
    for (var i = 0; i < partes.length - 1; i++) {
      if (!o[partes[i]] || typeof o[partes[i]] !== 'object' || Array.isArray(o[partes[i]])) o[partes[i]] = {};
      o = o[partes[i]];
    }
    o[partes[partes.length - 1]] = valor;
  }
  function uid(c) { contador++; return 'c' + contador + '-' + c.replace(/[^a-z0-9]/gi, '-'); }
  function marcar() {
    sujo = true;
    estado.textContent = 'Alterações não salvas';
    estado.className = 'estado estado-sujo';
  }

  // ---------- campos ----------
  function campo(def, alvo) {
    var id = uid(def.chave);
    var valor = pegar(alvo, def.chave);
    var muda = function (v) { por(alvo, def.chave, v); marcar(); };
    var rotulo = el('label', { for: id, text: def.rotulo + (def.obrigatorio ? ' *' : '') });
    var ajuda = def.ajuda ? el('small', { text: def.ajuda }) : null;
    var entrada, extra = null;

    switch (def.tipo) {
      case 'area':
        entrada = el('textarea', { id: id, rows: 3 });
        entrada.value = valor || '';
        entrada.addEventListener('input', function () { muda(entrada.value); conta(); });
        break;
      case 'numero':
        entrada = el('input', { id: id, type: 'number', step: '0.01', min: '0', inputmode: 'decimal' });
        entrada.value = (valor === undefined || valor === null) ? '' : valor;
        entrada.addEventListener('input', function () { muda(entrada.value === '' ? '' : Number(entrada.value)); });
        break;
      case 'hora':
        entrada = el('input', { id: id, type: 'time' });
        entrada.value = valor || '';
        entrada.addEventListener('input', function () { muda(entrada.value); });
        break;
      case 'check':
        entrada = el('input', { id: id, type: 'checkbox' });
        entrada.checked = !!valor;
        entrada.addEventListener('change', function () { muda(entrada.checked); });
        return el('div', { class: 'campo campo-check' }, [el('label', { for: id }, [entrada, ' ' + def.rotulo]), ajuda]);
      case 'selecao':
        entrada = el('select', { id: id });
        var tem = false;
        (def.opcoes || []).forEach(function (o) {
          var op = el('option', { value: o[0], text: o[1] });
          if (o[0] === valor) { op.selected = true; tem = true; }
          entrada.appendChild(op);
        });
        if (!tem && def.opcoes && def.opcoes.length && valor === undefined) { /* usa a primeira como padrão */ por(alvo, def.chave, def.opcoes[0][0]); }
        entrada.addEventListener('change', function () { muda(entrada.value); });
        break;
      case 'linhas':
        entrada = el('textarea', { id: id, rows: 4 });
        entrada.value = Array.isArray(valor) ? valor.join('\n') : (valor || '');
        entrada.addEventListener('input', function () {
          muda(entrada.value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean));
        });
        break;
      case 'paragrafos':
        entrada = el('textarea', { id: id, rows: 6 });
        entrada.value = Array.isArray(valor) ? valor.join('\n\n') : (valor || '');
        entrada.addEventListener('input', function () {
          muda(entrada.value.split(/\n\s*\n/).map(function (s) { return s.trim(); }).filter(Boolean));
        });
        break;
      case 'dias':
        var marcados = Array.isArray(valor) ? valor.slice() : [];
        var grupo = el('div', { class: 'dias', role: 'group', 'aria-label': def.rotulo });
        DIAS.forEach(function (d) {
          var cb = el('input', { type: 'checkbox', value: d[0] });
          cb.checked = marcados.indexOf(d[0]) >= 0;
          cb.addEventListener('change', function () {
            marcados = DIAS.map(function (x) { return x[0]; }).filter(function (k) {
              var c = grupo.querySelector('input[value="' + k + '"]'); return c && c.checked;
            });
            muda(marcados);
          });
          grupo.appendChild(el('label', {}, [cb, d[1]]));
        });
        return el('div', { class: 'campo' }, [el('span', { class: 'rotulo', text: def.rotulo }), grupo, ajuda]);
      case 'cor':
        var texto = el('input', { id: id, type: 'text', placeholder: def.padrao || '', maxlength: 9, pattern: '#[0-9a-fA-F]{3,8}', class: 'mono' });
        texto.value = valor || '';
        var seletor = el('input', { type: 'color', 'aria-label': 'Escolher ' + def.rotulo });
        seletor.value = /^#[0-9a-f]{6}$/i.test(valor || '') ? valor : (def.padrao || '#000000');
        seletor.addEventListener('input', function () { texto.value = seletor.value; muda(seletor.value); });
        texto.addEventListener('input', function () {
          var v = texto.value.trim();
          if (/^#[0-9a-f]{6}$/i.test(v)) seletor.value = v;
          muda(v);
        });
        var limpar = el('button', { type: 'button', class: 'link', text: 'Usar a do modelo', onclick: function () { texto.value = ''; seletor.value = def.padrao || '#000000'; muda(''); } });
        return el('div', { class: 'campo' }, [rotulo, el('div', { class: 'cor' }, [seletor, texto, limpar]), ajuda]);
      case 'lista':
        return lista(def, alvo);
      case 'fotos':
        return fotos(def, alvo);
      default:
        entrada = el('input', { id: id, type: 'text' });
        entrada.value = (valor === undefined || valor === null) ? '' : valor;
        entrada.addEventListener('input', function () { muda(entrada.value); conta(); });
    }
    if (def.obrigatorio) entrada.required = true;
    if (def.max) {
      extra = el('span', { class: 'contador' });
      var conta = function () {
        var n = entrada.value.length;
        extra.textContent = n + ' / ' + def.max;
        extra.classList.toggle('passou', n > def.max);
      };
      conta();
    } else {
      var conta = function () {};
    }
    return el('div', { class: 'campo' + (def.tipo === 'area' || def.tipo === 'linhas' || def.tipo === 'paragrafos' ? ' campo-largo' : '') },
      [el('div', { class: 'rotulo-linha' }, [rotulo, extra]), entrada, ajuda]);
  }

  // ---------- listas ----------
  function lista(def, alvo) {
    var caixa = el('div', { class: 'lista campo-largo' });
    function itens() {
      var a = pegar(alvo, def.chave);
      if (!Array.isArray(a)) { a = []; por(alvo, def.chave, a); }
      return a;
    }
    function titulo(item, i) {
      var primeiro = def.campos[0] ? item[def.campos[0].chave] : '';
      return (primeiro && String(primeiro).trim()) ? String(primeiro) : (def.item || 'Item') + ' ' + (i + 1);
    }
    function desenha() {
      caixa.innerHTML = '';
      caixa.appendChild(el('div', { class: 'lista-cab' }, [el('span', { class: 'rotulo', text: def.rotulo }), el('span', { class: 'suave', text: itens().length + ' ' + (itens().length === 1 ? 'item' : 'itens') })]));
      itens().forEach(function (item, i) {
        var nomeItem = el('strong', { text: titulo(item, i) });
        var corpo = el('div', { class: 'item-corpo' });
        def.campos.forEach(function (c) {
          var f = campo(c, item);
          if (c === def.campos[0]) f.addEventListener('input', function () { nomeItem.textContent = titulo(item, i); });
          corpo.appendChild(f);
        });
        var acoes = el('div', { class: 'item-acoes' }, [
          el('button', { type: 'button', 'aria-label': 'Subir', title: 'Subir', text: '↑', disabled: i === 0, onclick: function () { move(i, -1); } }),
          el('button', { type: 'button', 'aria-label': 'Descer', title: 'Descer', text: '↓', disabled: i === itens().length - 1, onclick: function () { move(i, 1); } }),
          el('button', { type: 'button', class: 'perigo', text: 'Remover', onclick: function () { itens().splice(i, 1); marcar(); desenha(); } })
        ]);
        var det = el('details', { class: 'item', open: !titulo(item, i).match(new RegExp('^' + (def.item || 'Item') + ' \\d+$')) ? null : true }, [
          el('summary', {}, [el('span', { class: 'item-num mono', text: String(i + 1).padStart(2, '0') }), nomeItem]),
          corpo, acoes
        ]);
        caixa.appendChild(det);
      });
      caixa.appendChild(el('button', { type: 'button', class: 'botao botao-adicionar', text: '+ Adicionar ' + (def.item || 'item').toLowerCase(), onclick: function () {
        var novo = {};
        def.campos.forEach(function (c) { if (c.tipo === 'check') novo[c.chave] = false; });
        itens().push(novo); marcar(); desenha();
        var todos = caixa.querySelectorAll('details.item');
        var ult = todos[todos.length - 1];
        if (ult) { ult.open = true; var inp = ult.querySelector('input,textarea,select'); if (inp) inp.focus(); }
      } }));
    }
    function move(i, d) {
      var a = itens(), j = i + d;
      if (j < 0 || j >= a.length) return;
      var t = a[i]; a[i] = a[j]; a[j] = t; marcar(); desenha();
    }
    desenha();
    return caixa;
  }

  // ---------- fotos ----------
  function fotos(def, alvo) {
    var caixa = el('div', { class: 'fotos campo-largo' });
    function lista() {
      var a = pegar(alvo, def.chave);
      if (!Array.isArray(a)) { a = []; por(alvo, def.chave, a); }
      return a;
    }
    var entrada = el('input', { type: 'file', accept: 'image/jpeg,image/png,image/webp', multiple: true, id: 'enviar-fotos', class: 'sr' });
    var status = el('span', { class: 'suave', role: 'status' });
    entrada.addEventListener('change', function () {
      var arqs = Array.prototype.slice.call(entrada.files);
      entrada.value = '';
      var feito = 0;
      function proxima() {
        if (!arqs.length) { status.textContent = feito ? feito + ' foto(s) enviada(s).' : ''; desenha(); if (feito) salvar(); return; }
        var arq = arqs.shift();
        status.textContent = 'Enviando ' + arq.name + '…';
        var fd = new FormData();
        fd.append('acao', 'foto'); fd.append('csrf', D.csrf); fd.append('slug', D.slug); fd.append('foto', arq);
        fetch('./', { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (r) {
          if (r.ok) { lista().push({ arquivo: r.arquivo, descricao: '' }); feito++; }
          else alert_suave('Não enviei ' + arq.name + ': ' + r.erro);
          proxima();
        }).catch(function () { alert_suave('Falha de conexão ao enviar ' + arq.name + '.'); proxima(); });
      }
      proxima();
    });
    function desenha() {
      caixa.innerHTML = '';
      var grade = el('ul', { class: 'grade-fotos' });
      lista().forEach(function (ft, i) {
        var desc = el('input', { type: 'text', placeholder: 'Descreva a foto (ajuda no Google)', 'aria-label': 'Descrição da foto ' + (i + 1) });
        desc.value = ft.descricao || '';
        desc.addEventListener('input', function () { ft.descricao = desc.value; marcar(); });
        grade.appendChild(el('li', {}, [
          el('img', { src: D.url_fotos + encodeURIComponent(ft.arquivo), alt: '', loading: 'lazy' }),
          desc,
          el('div', { class: 'item-acoes' }, [
            el('button', { type: 'button', text: '←', 'aria-label': 'Mover para trás', disabled: i === 0, onclick: function () { var a = lista(); a.splice(i - 1, 0, a.splice(i, 1)[0]); marcar(); desenha(); } }),
            el('button', { type: 'button', class: 'perigo', text: 'Remover', onclick: function () {
              fetch('./', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ acao: 'remover_foto', csrf: D.csrf, slug: D.slug, arquivo: ft.arquivo }) })
                .then(function () { lista().splice(i, 1); desenha(); salvar(); });
            } })
          ])
        ]));
      });
      caixa.appendChild(el('div', { class: 'lista-cab' }, [el('span', { class: 'rotulo', text: def.rotulo }), status]));
      if (lista().length) caixa.appendChild(grade);
      caixa.appendChild(el('label', { for: 'enviar-fotos', class: 'botao botao-adicionar', text: '+ Enviar fotos (JPG, PNG ou WebP)' }));
      caixa.appendChild(entrada);
    }
    desenha();
    return caixa;
  }

  function alert_suave(msg) {
    estado.textContent = msg;
    estado.className = 'estado estado-erro';
  }

  // ---------- monta o formulário ----------
  D.schema.secoes.forEach(function (s) {
    var sec = el('section', { class: 'secao-form', id: 'sec-' + s.id, 'aria-labelledby': 't-' + s.id });
    sec.appendChild(el('h2', { id: 't-' + s.id, text: s.titulo }));
    if (s.ajuda) sec.appendChild(el('p', { class: 'suave', text: s.ajuda }));
    var grade = el('div', { class: 'campos' });
    s.campos.forEach(function (c) { grade.appendChild(campo(c, ficha)); });
    sec.appendChild(grade);
    form.appendChild(sec);
    indice.appendChild(el('a', { href: '#sec-' + s.id, text: s.titulo }));
  });
  sujo = false;

  // destaca a seção visível no índice
  if ('IntersectionObserver' in window) {
    var links = indice.querySelectorAll('a');
    var obs = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (en) {
        if (en.isIntersecting) links.forEach(function (a) { a.classList.toggle('ativo', a.getAttribute('href') === '#' + en.target.id); });
      });
    }, { rootMargin: '-20% 0px -70% 0px' });
    form.querySelectorAll('.secao-form').forEach(function (s) { obs.observe(s); });
  }

  // ---------- salvar ----------
  function salvar() {
    if (salvando) return;
    if (!String(ficha.nome || '').trim()) { alert_suave('Preencha o nome do negócio.'); return; }
    salvando = true;
    botaoSalvar.disabled = true;
    estado.textContent = 'Salvando…';
    estado.className = 'estado';
    fetch('./', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ acao: 'salvar', csrf: D.csrf, slug: D.slug, ficha: ficha })
    }).then(function (r) { return r.json(); }).then(function (r) {
      if (r.ok) {
        sujo = false;
        estado.textContent = 'Salvo e publicado às ' + r.quando;
        estado.className = 'estado estado-ok';
        document.getElementById('chip-situacao').innerHTML = r.situacao;
        document.getElementById('titulo-site').textContent = ficha.nome;
      } else {
        alert_suave(r.erro || 'Não foi possível salvar.');
      }
    }).catch(function () {
      alert_suave('Sem conexão. Nada foi perdido: tente salvar de novo.');
    }).then(function () { salvando = false; botaoSalvar.disabled = false; });
  }
  botaoSalvar.addEventListener('click', salvar);
  document.addEventListener('keydown', function (ev) {
    if ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === 's') { ev.preventDefault(); salvar(); }
  });
  window.addEventListener('beforeunload', function (ev) { if (sujo) { ev.preventDefault(); ev.returnValue = ''; } });
})();
