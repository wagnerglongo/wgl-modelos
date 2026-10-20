<?php
/**
 * Transforma uma ficha em arquivos do site: index.html, politica-de-privacidade.html, sitemap.xml, robots.txt.
 * $modo = 'modelos'  → publicado em modelos.wglsoftware.com/<slug>/ (sempre fora do Google)
 * $modo = 'exportar' → pacote para o domínio do cliente (liberado para o Google)
 */
declare(strict_types=1);

const ICONE_ZAP = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-12.3 7.4L3 21l2.1-5.5A8.4 8.4 0 1 1 21 11.5z"/><path d="M8.5 9.5c.3 2.3 2.6 4.6 5 5l1.3-1.3 2 .9"/></svg>';

function favicon_svg(string $letra, string $fundo, string $tinta): string
{
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='14' fill='$fundo'/>"
         . "<text x='32' y='44' font-family='Arial,sans-serif' font-size='36' font-weight='700' text-anchor='middle' fill='$tinta'>"
         . htmlspecialchars($letra, ENT_QUOTES) . "</text></svg>";
    return 'data:image/svg+xml,' . rawurlencode($svg);
}

function montar_jsonld(array $f, array $M, string $url): array
{
    $end = $f['endereco'] ?? [];
    $neg = [
        '@context'    => 'https://schema.org',
        '@type'       => $M['tipo_schema'],
        'name'        => $f['nome'] ?? '',
        'description' => g($f, 'seo.descricao'),
        'url'         => $url,
        'telephone'   => !empty($f['whatsapp']) ? '+' . $f['whatsapp'] : null,
        'address'     => array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $end['rua'] ?? '',
            'addressLocality' => $end['cidade'] ?? '',
            'addressRegion'   => $end['uf'] ?? '',
            'postalCode'      => $end['cep'] ?? '',
            'addressCountry'  => 'BR',
        ]),
    ];
    if (!empty($f['faixa_preco']))      $neg['priceRange'] = $f['faixa_preco'];
    if (!empty($f['instagram']))        $neg['sameAs'] = ['https://instagram.com/' . $f['instagram']];
    if (!empty($f['areas_atendidas']))  $neg['areaServed'] = array_map(fn($c) => ['@type' => 'City', 'name' => $c], $f['areas_atendidas']);
    if (!empty($f['culinaria'])) {
        $neg['servesCuisine'] = $f['culinaria'];
        $neg['hasMenu'] = $url . '#cardapio';
    }
    if (!empty($f['fotos'][0]['arquivo'])) $neg['image'] = $url . 'fotos/' . $f['fotos'][0]['arquivo'];
    $horas = [];
    foreach ($f['horarios'] ?? [] as $h) {
        if (!empty($h['fechado']) || empty($h['schema'])) continue;
        $horas[] = ['@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => array_values(array_map(fn($d) => DIAS[$d] ?? $d, $h['schema'])),
                    'opens' => $h['abre'] ?? '', 'closes' => $h['fecha'] ?? ''];
    }
    if ($horas) $neg['openingHoursSpecification'] = $horas;
    $ofertas = [];
    foreach (($f['servicos'] ?? $f['tamanhos'] ?? []) as $item) {
        if (empty($item['nome'])) continue;
        $o = ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $item['nome']]];
        if (isset($item['preco']) && is_numeric($item['preco'])) {
            $o['price'] = number_format((float)$item['preco'], 2, '.', '');
            $o['priceCurrency'] = 'BRL';
        }
        $ofertas[] = $o;
    }
    if ($ofertas) $neg['hasOfferCatalog'] = ['@type' => 'OfferCatalog', 'name' => 'Serviços e preços', 'itemListElement' => $ofertas];
    // modelo loja: produtos com preço e disponibilidade
    $produtos = [];
    foreach ($f['produtos'] ?? [] as $item) {
        if (empty($item['nome'])) continue;
        $o = ['@type' => 'Offer', 'itemOffered' => array_filter(['@type' => 'Product', 'name' => $item['nome'], 'category' => $item['categoria'] ?? '', 'sku' => $item['codigo'] ?? '']),
              'availability' => !empty($item['esgotado']) ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock'];
        if (isset($item['preco']) && is_numeric($item['preco'])) {
            $o['price'] = number_format((float)$item['preco'], 2, '.', '');
            $o['priceCurrency'] = 'BRL';
        }
        $produtos[] = $o;
    }
    if ($produtos && !$ofertas) $neg['hasOfferCatalog'] = ['@type' => 'OfferCatalog', 'name' => 'Produtos', 'itemListElement' => $produtos];
    // dados extras do nicho (ex.: especialidade médica)
    if (!empty($M['schema_extra']) && is_array($M['schema_extra'])) $neg += $M['schema_extra'];
    $neg = array_filter($neg, fn($x) => $x !== null && $x !== '' && $x !== []);

    $faq = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => [
        '@type' => 'Question', 'name' => $q['pergunta'] ?? '',
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q['resposta'] ?? ''],
    ], array_values(array_filter($f['faq'] ?? [], fn($q) => !empty($q['pergunta']))))];

    return [json_seguro($neg), json_seguro($faq)];
}

function renderizar(array $f, string $modo = 'modelos'): array
{
    $modelo = $f['modelo'] ?? '';
    $arqModelo = MODELOS_DIR . "/$modelo/modelo.php";
    if (!preg_match('/^[a-z0-9_]+$/', $modelo) || !is_file($arqModelo)) {
        throw new RuntimeException("Modelo \"$modelo\" não encontrado.");
    }
    $cfg = config();
    $galeria = url_base();
    if ($modo === 'exportar') {
        $url = !empty($f['dominio']) ? rtrim($f['dominio'], '/') . '/' : $galeria . $f['slug'] . '/';
        $indexar = true;
        $barra = null;
    } else {
        $url = $galeria . $f['slug'] . '/';
        $indexar = false;
        $barra = match ($f['situacao'] ?? '') {
            'demonstracao' => 'demo',
            'previa', 'rascunho' => 'previa',
            default => null,
        };
    }
    $wglSite = $cfg['wgl_site'] ?: 'https://wglsoftware.com';
    $wglZap = preg_replace('/\D/', '', (string)($cfg['wgl_whatsapp'] ?? ''));

    $v = fn(string $caminho, mixed $padrao = '') => g($f, $caminho, $padrao);
    $whats = preg_replace('/\D/', '', (string)($f['whatsapp'] ?? ''));
    $padraoMsg = $f['mensagem_padrao'] ?? ('Olá! Vim pelo site da ' . ($f['nome'] ?? '') . '.');
    $zap = fn(?string $msg = null) => 'https://wa.me/' . $whats . '?text=' . rawurlencode($msg ?: $padraoMsg);
    $ICONE = ICONE_ZAP;
    $ano = date('Y');
    $c = $f['cores'] ?? [];

    // O modelo preenche $M (metadados) e $B (blocos de HTML/CSS)
    $M = ['fontes' => '', 'tipo_schema' => 'LocalBusiness', 'cor_tema' => '#111111', 'favicon' => ['#111111', '#ffffff'], 'menu' => []];
    $B = ['tokens' => '', 'estilo' => '', 'marca' => '', 'hero' => '', 'principal' => '', 'contato' => '', 'rodape' => '', 'script' => ''];
    include $arqModelo;

    [$jsonNeg, $jsonFaq] = montar_jsonld($f, $M, $url);
    $nomeCurto = $f['nome_curto'] ?? '' ?: ($f['nome'] ?? 'S');
    $favicon = favicon_svg(mb_strtoupper(mb_substr($nomeCurto, 0, 1)), $M['favicon'][0], $M['favicon'][1]);
    $baseCss = (string)file_get_contents(MODELOS_DIR . '/_base.css');

    ob_start();
    include MODELOS_DIR . '/_base.php';
    $index = (string)ob_get_clean();

    ob_start();
    include MODELOS_DIR . '/_politica.php';
    $politica = (string)ob_get_clean();

    $proposta = null;
    if ($modo === 'modelos' && !empty($f['proposta']['ativa']) && in_array($f['situacao'] ?? '', ['previa', 'rascunho'], true)) {
        $P = proposta_padrao();
        $prop = (array)$f['proposta'];
        if (isset($prop['valor']) && $prop['valor'] !== '' && is_numeric($prop['valor'])) $P['valor'] = $prop['valor'];
        if (!empty($prop['condicao'])) $P['condicao'] = $prop['condicao'];
        if (!empty($prop['observacao'])) $P['observacao'] = $prop['observacao'];
        $P['para'] = trim((string)($prop['para'] ?? ''));
        $P['vence'] = previa_vence_em($f);
        ob_start();
        include MODELOS_DIR . '/_proposta.php';
        $proposta = (string)ob_get_clean();
    }

    $hoje = date('Y-m-d');
    $sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
             . "  <url><loc>" . e($url) . "</loc><lastmod>$hoje</lastmod><priority>1.0</priority></url>\n"
             . "  <url><loc>" . e($url) . "politica-de-privacidade.html</loc><lastmod>$hoje</lastmod><priority>0.2</priority></url>\n</urlset>\n";
    $robots = $indexar ? "User-agent: *\nAllow: /\n\nSitemap: {$url}sitemap.xml\n" : "User-agent: *\nDisallow: /\n";

    $arquivos = [
        'index.html' => $index,
        'politica-de-privacidade.html' => $politica,
        'sitemap.xml' => $sitemap,
        'robots.txt' => $robots,
    ];
    if ($proposta !== null) {
        $arquivos['proposta.html'] = $proposta;
    }
    return $arquivos;
}
