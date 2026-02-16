<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('memory_limit', '256M');
set_time_limit(300);

$rootDir = dirname(__DIR__);
$transportScript = __DIR__ . '/build.transport.php';

function detectModxBasePath(string $rootDir, string $requested): array
{
    $candidates = [];
    if ($requested !== '') {
        $candidates[] = ['path' => $requested, 'source' => 'request/env parameter'];
    }

    $candidates[] = ['path' => $rootDir, 'source' => 'repository root'];
    $candidates[] = ['path' => dirname($rootDir), 'source' => 'parent directory'];

    foreach ($candidates as $candidate) {
        $basePath = rtrim($candidate['path'], '/\\') . DIRECTORY_SEPARATOR;
        if (is_file($basePath . 'config.core.php')) {
            return ['path' => $basePath, 'source' => $candidate['source']];
        }
    }

    $fallback = rtrim($requested !== '' ? $requested : $rootDir, '/\\') . DIRECTORY_SEPARATOR;
    return ['path' => $fallback, 'source' => $requested !== '' ? 'request/env parameter' : 'repository root'];
}

function out(string $message): void
{
    echo $message . PHP_EOL;
}

out('[extratextareas] Manual PHP transport build (no exec/composer/ssh)');
out('[extratextareas] Root: ' . $rootDir);

if (!is_file($transportScript)) {
    throw new RuntimeException('build.transport.php not found: ' . $transportScript);
}

$requestedBasePath = '';
if (PHP_SAPI === 'cli') {
    $requestedBasePath = (string) (getenv('MODX_BASE_PATH') ?: '');
} elseif (isset($_REQUEST['modx_base_path'])) {
    $requestedBasePath = trim((string) $_REQUEST['modx_base_path']);
}

$basePathMeta = detectModxBasePath($rootDir, $requestedBasePath);
$basePath = $basePathMeta['path'];
$configCore = $basePath . 'config.core.php';

out('[extratextareas] MODX base path: ' . $basePath);
out('[extratextareas] Base path source: ' . $basePathMeta['source']);
out('[extratextareas] config.core.php: ' . $configCore);

if (!is_file($configCore)) {
    throw new RuntimeException('config.core.php not found. Set MODX_BASE_PATH or pass modx_base_path.');
}

putenv('MODX_BASE_PATH=' . $basePath);
$_ENV['MODX_BASE_PATH'] = $basePath;

ob_start();
try {
    require $transportScript;
    $logs = (string) ob_get_clean();
} catch (Throwable $e) {
    $logs = (string) ob_get_clean();
    out($logs);
    throw $e;
}

if ($logs !== '') {
    out($logs);
}

$packages = glob($basePath . 'core/packages/extratextareas-*.transport.zip') ?: [];
if ($packages === []) {
    throw new RuntimeException('Package not found after build in: ' . $basePath . 'core/packages/');
}

usort($packages, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
out('[extratextareas] Built package: ' . $packages[0]);
