<?php
/** Sessão, login e proteção contra envio forjado (CSRF) do painel. */
declare(strict_types=1);

const SESSAO_HORAS = 8;
const MAX_TENTATIVAS = 5;
const JANELA_TENTATIVAS = 900; // 15 min

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('wgl_painel');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/admin',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function logado(): bool
{
    iniciar_sessao();
    if (empty($_SESSION['ok']) || (time() - (int)($_SESSION['desde'] ?? 0)) > SESSAO_HORAS * 3600) {
        return false;
    }
    return true;
}

function csrf_token(): string
{
    iniciar_sessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_ok(?string $t): bool
{
    iniciar_sessao();
    return is_string($t) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

function ip_cliente(): string
{
    return substr(preg_replace('/[^0-9a-f.:]/i', '', $_SERVER['REMOTE_ADDR'] ?? '0'), 0, 45);
}

function bloqueado(): bool
{
    $t = ler_json(DADOS . '/tentativas.json');
    $ip = ip_cliente();
    $recentes = array_filter($t[$ip] ?? [], fn($ts) => $ts > time() - JANELA_TENTATIVAS);
    return count($recentes) >= MAX_TENTATIVAS;
}

function registrar_falha(): void
{
    $t = ler_json(DADOS . '/tentativas.json');
    $ip = ip_cliente();
    $t[$ip] = array_values(array_filter($t[$ip] ?? [], fn($ts) => $ts > time() - JANELA_TENTATIVAS));
    $t[$ip][] = time();
    // limpa IPs antigos
    foreach ($t as $k => $lista) {
        if (!array_filter($lista, fn($ts) => $ts > time() - JANELA_TENTATIVAS)) unset($t[$k]);
    }
    gravar_json(DADOS . '/tentativas.json', $t);
}

function entrar(string $senha): bool
{
    $c = config();
    if (bloqueado()) {
        return false;
    }
    if (!empty($c['senha_hash']) && password_verify($senha, $c['senha_hash'])) {
        session_regenerate_id(true);
        $_SESSION['ok'] = true;
        $_SESSION['desde'] = time();
        if (password_needs_rehash($c['senha_hash'], PASSWORD_DEFAULT)) {
            $c['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
            salvar_config($c);
        }
        return true;
    }
    registrar_falha();
    usleep(600000);
    return false;
}

function sair(): void
{
    iniciar_sessao();
    $_SESSION = [];
    session_destroy();
}

function definir_senha(string $senha): void
{
    $c = config();
    $c['senha_hash'] = password_hash($senha, PASSWORD_DEFAULT);
    salvar_config($c);
}
