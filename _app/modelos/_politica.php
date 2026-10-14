<?php $end = $f['endereco'] ?? []; ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Política de privacidade · <?= e($f['nome']) ?></title>
<meta name="description" content="Como <?= e($f['nome']) ?> trata os dados pessoais de quem visita o site ou entra em contato.">
<link rel="canonical" href="<?= e($url) ?>politica-de-privacidade.html">
<meta name="robots" content="<?= $indexar ? 'index, follow' : 'noindex, nofollow' ?>">
<link rel="icon" href="<?= e($favicon) ?>">
<style>
:root{color-scheme:light}
body{margin:0;background:#fff;color:#1d2126;font:1.0625rem/1.65 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
main{max-width:44rem;margin-inline:auto;padding:2.5rem 1.25rem 4rem}
h1{font-size:2rem;line-height:1.15;margin:.5rem 0 .25rem;text-wrap:balance}
h2{font-size:1.25rem;margin:2rem 0 .5rem}
p,li{color:#3b424b}
a{color:#0b5cad}
.voltar{font-weight:600}
.data{color:#6b7380;font-size:.9375rem}
</style>
</head>
<body>
<main>
<p><a class="voltar" href="./">← Voltar ao site</a></p>
<h1>Política de privacidade</h1>
<p class="data"><?= e($f['nome']) ?><?php if ($v('documento')): ?> · <?= e($v('documento')) ?><?php endif; ?> · atualizada em <?= date('d/m/Y') ?></p>

<h2>Quais dados coletamos</h2>
<p>Este site não tem cadastro nem formulário que guarde dados. Quando você clica em um botão de contato, a conversa acontece no WhatsApp, e nós recebemos o seu nome de perfil, o seu número e o que você escrever na mensagem.</p>

<h2>Para que usamos</h2>
<?php if (in_array($M['tipo_schema'] ?? '', ['Attorney', 'LegalService'], true)): ?>
<ul>
<li>Responder o seu contato, agendar a conversa inicial e avaliar o seu caso.</li>
<li>Prestar o serviço contratado e cumprir obrigações legais.</li>
</ul>
<p>Tudo o que você conta está protegido pelo sigilo profissional da advocacia, mesmo que você não contrate.</p>
<?php else: ?>
<ul>
<li>Responder o seu contato, fazer orçamento, agendar ou combinar pedidos e entregas.</li>
<li>Emitir nota fiscal e cumprir obrigações legais, quando houver uma compra.</li>
</ul>
<?php endif; ?>
<p>Não vendemos nem repassamos os seus dados para terceiros com fins de publicidade.</p>

<h2>Por quanto tempo guardamos</h2>
<p>Guardamos as conversas enquanto forem necessárias para o atendimento e pelo prazo exigido por lei para documentos fiscais.</p>

<h2>Cookies e estatísticas</h2>
<?php if (preg_match('/^GTM-[A-Z0-9]{4,12}$/i', trim((string)$v('gtm_id')))): ?>
<p>Com a sua autorização no aviso de cookies, o site usa o Google Tag Manager e o Google Analytics para contar visitas e cliques nos botões de contato (páginas vistas, tipo de aparelho e cidade aproximada). Se você recusar, nada disso é carregado. Para mudar a escolha, apague os dados do site no seu navegador.</p>
<?php else: ?>
<p>O site pode usar ferramentas de estatística de visitas, que registram dados como páginas vistas, tipo de aparelho e cidade aproximada, sem identificar você pessoalmente.</p>
<?php endif; ?>

<h2>Seus direitos (LGPD)</h2>
<p>Pela Lei Geral de Proteção de Dados (Lei nº 13.709/2018), você pode pedir para ver, corrigir ou apagar os seus dados, ou saber com quem eles foram compartilhados. Basta pedir pelo WhatsApp <?= e($v('telefone_exibicao')) ?><?php if ($v('email')): ?> ou pelo e-mail <?= e($v('email')) ?><?php endif; ?>.</p>

<h2>Responsável</h2>
<p><?= e($f['nome']) ?><?php if ($v('documento')): ?>, <?= e($v('documento')) ?><?php endif; ?><?php if (!empty($end['rua'])): ?>, <?= e($end['rua']) ?>, <?= e($end['bairro'] ?? '') ?>, <?= e($end['cidade'] ?? '') ?> – <?= e($end['uf'] ?? '') ?><?php endif; ?>.</p>
</main>
</body>
</html>
