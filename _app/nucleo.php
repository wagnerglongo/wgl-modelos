<?php
/**
 * WGL Modelos — núcleo do painel.
 * Fichas (JSON) dos sites, configuração, publicação dos HTML estáticos e utilidades.
 * Requer PHP 8.1+ (Hostinger: hPanel → Avançado → Configuração do PHP).
 */
declare(strict_types=1);

define('APP', __DIR__);
define('RAIZ', dirname(__DIR__));            // public_html do subdomínio
define('DADOS', APP . '/dados');
define('FICHAS', DADOS . '/fichas');
define('LIXEIRA', DADOS . '/lixeira');
define('MODELOS_DIR', APP . '/modelos');
define('VERSAO', '1.1');
date_default_timezone_set('America/Sao_Paulo');

const RESERVADOS = ['admin', '_app', 'index', 'assets', 'galeria', 'robots', 'sitemap', 'favicon', 'api', 'wp-admin'];
const DIAS = ['seg' => 'Monday', 'ter' => 'Tuesday', 'qua' => 'Wednesday', 'qui' => 'Thursday', 'sex' => 'Friday', 'sab' => 'Saturday', 'dom' => 'Sunday'];
const SITUACOES = [
    'previa'       => 'Prévia',
    'portfolio'    => 'Portfólio',
    'demonstracao' => 'Demonstração',
    'rascunho'     => 'Rascunho',
];
const DIAS_PREVIA = 15;

// ---------- utilidades ----------
function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Lê um caminho com pontos: g($f, 'hero.titulo') */
function g(array $a, string $caminho, mixed $padrao = ''): mixed
{
    foreach (explode('.', $caminho) as $k) {
        if (!is_array($a) || !array_key_exists($k, $a)) {
            return $padrao;
        }
        $a = $a[$k];
    }
    return $a ?? $padrao;
}

function reais(mixed $v): string
{
    if ($v === null || $v === '' || !is_numeric($v)) {
        return (string)$v;
    }
    $f = (float)$v;
    if (floor($f) == $f) {
        return 'R$ ' . number_format($f, 0, ',', '.');
    }
    return 'R$ ' . number_format($f, 2, ',', '.');
}

/** '09:00' → '9h'  |  '10:30' → '10h30' */
function hora(string $h): string
{
    if (!preg_match('/^(\d{1,2}):(\d{2})$/', $h, $m)) {
        return $h;
    }
    return ((int)$m[1]) . 'h' . ($m[2] === '00' ? '' : $m[2]);
}

function slug_valido(string $s): bool
{
    return (bool)preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $s)
        && strlen($s) <= 60
        && !in_array($s, RESERVADOS, true);
}

function slugify(string $s): string
{
    $mapa = ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
             'ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n','&'=>' e '];
    $s = mb_strtolower(trim($s), 'UTF-8');
    $s = strtr($s, $mapa);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    $s = trim($s, '-');
    return substr($s, 0, 60) ?: 'site';
}

function ler_json(string $arq, array $padrao = []): array
{
    if (!is_file($arq)) {
        return $padrao;
    }
    $d = json_decode((string)file_get_contents($arq), true);
    return is_array($d) ? $d : $padrao;
}

function gravar_json(string $arq, array $dados): void
{
    $tmp = $arq . '.tmp' . bin2hex(random_bytes(4));
    file_put_contents($tmp, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    rename($tmp, $arq);
}

/** JSON seguro para colocar dentro de <script>. */
function json_seguro(mixed $obj, bool $bonito = true): string
{
    $fl = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
    return (string)json_encode($obj, $bonito ? $fl | JSON_PRETTY_PRINT : $fl);
}

// ---------- configuração ----------
function config(): array
{
    return ler_json(DADOS . '/config.json', [
        'senha_hash'   => '',
        'wgl_whatsapp' => '',
        'wgl_site'     => 'https://wglsoftware.com',
        'url_base'     => '',
    ]);
}

/** Valores padrão da proposta comercial (editáveis em Configurações). */
function proposta_padrao(): array
{
    $c = config();
    $padrao = [
        'plano'      => 'Site Essencial',
        'valor'      => 60,
        'periodo'    => '/mês',
        'condicao'   => 'Sem taxa de implantação',
        'inclui'     => [
            'Site profissional, feito para abrir rápido no celular',
            'Domínio próprio (.com.br)',
            'Hospedagem com cadeado de segurança (SSL)',
            'E-mail profissional com o seu domínio',
            'Botão de WhatsApp em todo o site',
            'Perfil no Google Maps ligado ao site',
            '1 alteração de conteúdo por mês',
            'Política de privacidade (LGPD)',
        ],
        'passos'     => [
            ['Você aprova', 'Responda a mensagem dizendo que quer seguir.'],
            ['Ajustes com você', 'Trocamos textos, fotos e preços pelos seus.'],
            ['Domínio e publicação', 'Registramos o domínio e o site vai ao ar em até 48 horas.'],
            ['Mensalidade', 'Você paga só a mensalidade, com nota fiscal.'],
        ],
        'observacao' => '',
    ];
    $salvo = (array)($c['proposta'] ?? []);
    foreach ($padrao as $k => $v) {
        if (array_key_exists($k, $salvo) && $salvo[$k] !== '' && $salvo[$k] !== []) $padrao[$k] = $salvo[$k];
    }
    return $padrao;
}

/** Data em que a prévia (e a proposta) vence. */
function previa_vence_em(array $f): int
{
    $inicio = strtotime($f['previa_desde'] ?? $f['criado_em'] ?? 'now') ?: time();
    return $inicio + DIAS_PREVIA * 86400;
}

function salvar_config(array $c): void
{
    gravar_json(DADOS . '/config.json', $c);
}

/** URL pública do subdomínio, com barra no fim. */
function url_base(): string
{
    $c = config();
    if (!empty($c['url_base'])) {
        return rtrim($c['url_base'], '/') . '/';
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'modelos.wglsoftware.com');
    return ($https ? 'https' : 'http') . '://' . $host . '/';
}

// ---------- modelos ----------
function modelos_disponiveis(): array
{
    $lista = [];
    foreach (glob(MODELOS_DIR . '/*/schema.json') ?: [] as $arq) {
        $id = basename(dirname($arq));
        $s = ler_json($arq);
        $lista[$id] = [
            'id'        => $id,
            'nome'      => $s['nome'] ?? $id,
            'descricao' => $s['descricao'] ?? '',
            'nichos'    => $s['nichos'] ?? '',
            'ordem'     => $s['ordem'] ?? 99,
        ];
    }
    uasort($lista, fn($a, $b) => $a['ordem'] <=> $b['ordem']);
    return $lista;
}

function schema_modelo(string $modelo): array
{
    $comum = ler_json(MODELOS_DIR . '/_comum.schema.json');
    $proprio = ler_json(MODELOS_DIR . "/$modelo/schema.json");
    // seções do modelo entram depois de "Identificação" e "Contato"
    $secoes = array_merge(
        array_slice($comum['secoes'] ?? [], 0, 2),
        $proprio['secoes'] ?? [],
        array_slice($comum['secoes'] ?? [], 2)
    );
    return ['nome' => $proprio['nome'] ?? $modelo, 'secoes' => $secoes];
}

function exemplo_modelo(string $modelo): array
{
    return ler_json(MODELOS_DIR . "/$modelo/exemplo.json");
}

// ---------- fichas ----------
function ficha_arquivo(string $slug): string
{
    return FICHAS . "/$slug.json";
}

function ficha_ler(string $slug): ?array
{
    if (!slug_valido($slug) || !is_file(ficha_arquivo($slug))) {
        return null;
    }
    return ler_json(ficha_arquivo($slug));
}

function ficha_salvar(array $f): void
{
    $f['atualizado_em'] = date('c');
    gravar_json(ficha_arquivo($f['slug']), $f);
}

function fichas_listar(): array
{
    $lista = [];
    foreach (glob(FICHAS . '/*.json') ?: [] as $arq) {
        $f = ler_json($arq);
        if (!empty($f['slug'])) {
            $lista[$f['slug']] = $f;
        }
    }
    uasort($lista, fn($a, $b) => strcmp($b['atualizado_em'] ?? '', $a['atualizado_em'] ?? ''));
    return $lista;
}

function slug_livre(string $slug): bool
{
    return slug_valido($slug) && !is_file(ficha_arquivo($slug)) && !file_exists(RAIZ . "/$slug");
}

/** Dias que faltam para a prévia vencer (negativo = vencida). */
function previa_dias_restantes(array $f): ?int
{
    if (($f['situacao'] ?? '') !== 'previa') {
        return null;
    }
    $inicio = strtotime($f['previa_desde'] ?? $f['criado_em'] ?? 'now') ?: time();
    $fim = $inicio + DIAS_PREVIA * 86400;
    return (int)ceil(($fim - time()) / 86400);
}

// ---------- publicação ----------
function caminho_seguro(string $slug): string
{
    if (!slug_valido($slug)) {
        throw new RuntimeException('Endereço inválido.');
    }
    return RAIZ . '/' . $slug;
}

function remover_dir(string $dir): void
{
    $real = realpath($dir);
    $raiz = realpath(RAIZ);
    if ($real === false || $raiz === false || !str_starts_with($real, $raiz . DIRECTORY_SEPARATOR) || $real === realpath(APP)) {
        return;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($real);
}

/** Gera o site da ficha em /<slug>/ do subdomínio. */
function publicar(array $f): void
{
    require_once APP . '/render.php';
    $dir = caminho_seguro($f['slug']);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $arquivos = renderizar($f, 'modelos');
    foreach ($arquivos as $nome => $conteudo) {
        file_put_contents("$dir/$nome", $conteudo, LOCK_EX);
    }
    if (!isset($arquivos['proposta.html']) && is_file("$dir/proposta.html")) {
        unlink("$dir/proposta.html");
    }
}

function despublicar(string $slug): void
{
    $dir = caminho_seguro($slug);
    foreach (['index.html', 'politica-de-privacidade.html', 'sitemap.xml', 'robots.txt', 'proposta.html'] as $arq) {
        if (is_file("$dir/$arq")) {
            unlink("$dir/$arq");
        }
    }
}

/** Monta um .zip com o site pronto para o domínio do cliente. Retorna o caminho do arquivo temporário. */
function exportar_zip(array $f): string
{
    require_once APP . '/render.php';
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('O PHP da hospedagem está sem a extensão zip.');
    }
    $tmp = tempnam(sys_get_temp_dir(), 'wgl') . '.zip';
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach (renderizar($f, 'exportar') as $nome => $conteudo) {
        $zip->addFromString($nome, $conteudo);
    }
    $fotos = RAIZ . '/' . $f['slug'] . '/fotos';
    if (is_dir($fotos)) {
        foreach (glob("$fotos/*") ?: [] as $foto) {
            $zip->addFile($foto, 'fotos/' . basename($foto));
        }
    }
    $zip->close();
    return $tmp;
}

// ---------- fotos ----------
/** Salva uma foto enviada, redimensionada (máx. 1600 px) e convertida para WebP/JPEG. Retorna o nome do arquivo. */
function salvar_foto(string $slug, array $up): string
{
    if (($up['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível receber a foto (erro ' . (int)($up['error'] ?? 0) . ').');
    }
    if ($up['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Foto maior que 8 MB.');
    }
    $info = @getimagesize($up['tmp_name']);
    $tipos = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
    if (!$info || !isset($tipos[$info[2]])) {
        throw new RuntimeException('Envie fotos em JPG, PNG ou WebP.');
    }
    $dir = caminho_seguro($slug) . '/fotos';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $base = substr(slugify(pathinfo($up['name'], PATHINFO_FILENAME)), 0, 40) . '-' . bin2hex(random_bytes(3));

    if (!function_exists('imagecreatetruecolor')) {
        // sem GD: guarda como veio
        $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2]];
        move_uploaded_file($up['tmp_name'], "$dir/$base.$ext");
        return "$base.$ext";
    }
    $orig = ($tipos[$info[2]])($up['tmp_name']);
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($up['tmp_name']);
        $rot = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
        if ($rot) {
            $orig = imagerotate($orig, $rot, 0);
        }
    }
    $w = imagesx($orig);
    $h = imagesy($orig);
    $escala = min(1, 1600 / max($w, $h));
    $nw = (int)round($w * $escala);
    $nh = (int)round($h * $escala);
    $img = imagecreatetruecolor($nw, $nh);
    imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
    imagecopyresampled($img, $orig, 0, 0, 0, 0, $nw, $nh, $w, $h);
    if (function_exists('imagewebp')) {
        imagewebp($img, "$dir/$base.webp", 80);
        $nome = "$base.webp";
    } else {
        imagejpeg($img, "$dir/$base.jpg", 82);
        $nome = "$base.jpg";
    }
    imagedestroy($img);
    imagedestroy($orig);
    return $nome;
}

function remover_foto(string $slug, string $arquivo): void
{
    $arquivo = basename($arquivo);
    $alvo = caminho_seguro($slug) . '/fotos/' . $arquivo;
    if (preg_match('/^[a-z0-9\-]+\.(webp|jpg|png)$/', $arquivo) && is_file($alvo)) {
        unlink($alvo);
    }
}

/** Cor em hexadecimal vinda da ficha; se inválida, usa a padrão. */
function cor(mixed $c, string $padrao): string
{
    return (is_string($c) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $c)) ? $c : $padrao;
}
