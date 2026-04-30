<?php
/**
 * Carga .env (si existe) y expone helpers para que local y hosting compartan la misma base de código.
 */
if (defined('HOTEL_TAME_ENV_LOADED')) {
    return;
}
define('HOTEL_TAME_ENV_LOADED', true);

$hotelTameRoot = dirname(__DIR__);

if (!function_exists('hotel_tame_parse_env_file')) {
    /**
     * Carga .env sin Composer (hosting sin vendor): KEY=valor por línea.
     */
    function hotel_tame_parse_env_file(string $path): void {
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return;
        }
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        $lines = preg_split("/\r\n|\n|\r/", $raw);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (preg_match('/^export\s+/i', $line)) {
                $line = trim(substr($line, 7));
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);
            if ($k === '') {
                continue;
            }
            if (
                (str_starts_with($v, '"') && str_ends_with($v, '"') && strlen($v) >= 2)
                || (str_starts_with($v, "'") && str_ends_with($v, "'") && strlen($v) >= 2)
            ) {
                $v = substr($v, 1, -1);
            }
            $_ENV[$k] = $v;
            @putenv($k . '=' . $v);
        }
    }
}

$hotelTameEnvLoadedFromFile = false;

if (is_file($hotelTameRoot . '/vendor/autoload.php')) {
    require_once $hotelTameRoot . '/vendor/autoload.php';
    if (class_exists(\Dotenv\Dotenv::class) && is_file($hotelTameRoot . '/.env')) {
        \Dotenv\Dotenv::createImmutable($hotelTameRoot)->safeLoad();
        $hotelTameEnvLoadedFromFile = true;
    }
}

if (!$hotelTameEnvLoadedFromFile && is_file($hotelTameRoot . '/.env')) {
    hotel_tame_parse_env_file($hotelTameRoot . '/.env');
}

if (!function_exists('hotel_tame_env')) {
    function hotel_tame_env(string $key, mixed $default = null): mixed {
        if (array_key_exists($key, $_ENV)) {
            $v = $_ENV[$key];
            return $v === null ? $default : $v;
        }
        $g = getenv($key);
        if ($g !== false) {
            return $g;
        }
        return $default;
    }
}

if (!function_exists('hotel_tame_base_path')) {
    /**
     * Prefijo de URL del proyecto (ej. /Hotel_tame) o cadena vacía si vive en la raíz del dominio.
     */
    function hotel_tame_base_path(): string {
        $base = (string) hotel_tame_env('APP_BASE_PATH', '/Hotel_tame');
        $base = trim($base);
        if ($base === '' || $base === '/') {
            return '';
        }
        return '/' . trim($base, '/');
    }
}

if (!function_exists('hotel_tame_assets_url_prefix')) {
    function hotel_tame_assets_url_prefix(): string {
        $b = hotel_tame_base_path();
        return $b === '' ? '/assets' : $b . '/assets';
    }
}

if (!function_exists('hotel_tame_define_web_constants')) {
    function hotel_tame_define_web_constants(): void {
        if (!defined('HOTEL_TAME_WEB_BASE')) {
            define('HOTEL_TAME_WEB_BASE', hotel_tame_base_path());
        }
        if (!defined('ASSETS_URL')) {
            define('ASSETS_URL', hotel_tame_assets_url_prefix());
        }
    }
}

if (!function_exists('hotel_tame_url_path')) {
    /**
     * Ruta absoluta en el sitio (ej. /Hotel_tame/login o /login).
     */
    function hotel_tame_url_path(string $path = ''): string {
        $path = ltrim($path, '/');
        $b = hotel_tame_base_path();
        if ($path === '') {
            return $b === '' ? '/' : $b . '/';
        }
        return ($b === '' ? '' : $b) . '/' . $path;
    }
}
