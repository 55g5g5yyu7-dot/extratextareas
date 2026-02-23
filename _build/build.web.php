<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('memory_limit', '256M');
set_time_limit(300);

$rootDir = dirname(__DIR__);
$transportScript = __DIR__ . '/build.transport.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function detectModxBasePath(string $rootDir, string $requested): array
{
    $candidates = [];
    if ($requested !== '') {
        $candidates[] = ['path' => $requested, 'source' => 'request parameter modx_base_path'];
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
    return ['path' => $fallback, 'source' => $requested !== '' ? 'request parameter modx_base_path' : 'repository root'];
}

function renderHeader(): void
{
    echo "<!doctype html>\n";
    echo "<html lang=\"ru\">\n";
    echo "<head><meta charset=\"utf-8\"><title>ExtraTextAreas: локальная сборка</title>\n";
    echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;color:#222}section{background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px;margin:12px 0}h1{margin-top:0}.ok{color:#0a7d22}.err{color:#b00020}.warn{color:#9a6700}code,pre,textarea{background:#fafafa;border:1px solid #eee;border-radius:6px;padding:8px;display:block;overflow:auto}</style></head><body>\n";
    echo "<h1>🔨 Сборка transport-пакета ExtraTextAreas</h1>\n";
}

function renderFooter(): void
{
    echo "</body></html>";
}

function renderReport(array $reportLines, string $logs): void
{
    $report = implode("\n", $reportLines) . "\n\nLogs:\n" . $logs;

    echo '<section>';
    echo '<strong>Отчёт для пересылки</strong>';
    echo '<p>Скопируйте блок ниже целиком и отправьте разработчику:</p>';
    echo '<textarea readonly style="width:100%;min-height:280px;font-family:monospace">' . h($report) . '</textarea>';
    echo '</section>';
}

renderHeader();

echo '<section>';
echo '<strong>Окружение</strong>';
echo '<p>PHP: <code>' . h(PHP_VERSION) . '</code><br>';
echo 'SAPI: <code>' . h(PHP_SAPI) . '</code><br>';
echo 'Путь к скрипту: <code>' . h(__FILE__) . '</code></p>';
echo '</section>';

$inputBasePath = isset($_REQUEST['modx_base_path']) ? trim((string) $_REQUEST['modx_base_path']) : '';
$basePathMeta = detectModxBasePath($rootDir, $inputBasePath);
$basePath = $basePathMeta['path'];
$configCorePath = $basePath . 'config.core.php';

$reportLines = [
    'ExtraTextAreas build report',
    'PHP: ' . PHP_VERSION,
    'SAPI: ' . PHP_SAPI,
    'Script: ' . __FILE__,
    'MODX base path: ' . $basePath,
    'Path source: ' . $basePathMeta['source'],
    'config.core.php: ' . $configCorePath,
    'Mode: direct include (pure PHP, no exec)',
];

$logs = '';
$exitCode = 1;

echo '<section>';
echo '<strong>Параметры</strong>';
echo '<p>MODX base path: <code>' . h($basePath) . '</code></p>';
echo '<p>Источник пути: <code>' . h($basePathMeta['source']) . '</code></p>';
echo '<p>config.core.php: <code>' . h($configCorePath) . '</code></p>';
echo '</section>';

if (!is_file($transportScript)) {
    $logs = 'build.transport.php not found: ' . $transportScript;
    echo '<section><p class="err">❌ Не найден файл сборки: <code>' . h($transportScript) . '</code></p></section>';
    $reportLines[] = 'Exit code: 1';
    renderReport($reportLines, $logs);
    renderFooter();
    exit(1);
}

if (!is_file($configCorePath)) {
    $logs = 'config.core.php not found in selected MODX base path';
    echo '<section>';
    echo '<p class="err">❌ Не найден <code>config.core.php</code> в указанном MODX пути.</p>';
    echo '<p>Передайте путь параметром <code>?modx_base_path=/полный/путь/к/modx/</code>.</p>';
    echo '</section>';
    $reportLines[] = 'Exit code: 1';
    renderReport($reportLines, $logs);
    renderFooter();
    exit(1);
}

echo '<section><strong>Режим запуска</strong><p><code>direct include (pure PHP, no exec)</code></p></section>';
echo '<section><strong>Выполнение</strong><p>Запуск <code>build.transport.php</code> в текущем PHP процессе.</p></section>';

try {
    putenv('MODX_BASE_PATH=' . $basePath);
    $_ENV['MODX_BASE_PATH'] = $basePath;
    putenv('EXTRATEXTAREAS_BUILD_EMBEDDED=1');
    $_ENV['EXTRATEXTAREAS_BUILD_EMBEDDED'] = '1';

    ob_start();
    require $transportScript;
    $logs = (string) ob_get_clean();
    $exitCode = 0;
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        $logs = (string) ob_get_clean();
    }
    $logs .= "\n" . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString();
    $exitCode = 1;
}

echo '<section>';
echo '<strong>Логи build.transport.php</strong>';
echo '<pre>' . h($logs) . '</pre>';
if ($exitCode === 0) {
    echo '<p class="ok">✅ Сборка завершена успешно.</p>';
} else {
    echo '<p class="err">❌ Сборка завершилась с ошибкой. Код: ' . h((string) $exitCode) . '.</p>';
}
echo '</section>';

$packageFiles = glob($basePath . 'core/packages/extratextareas-*.transport.zip') ?: [];
if ($packageFiles !== []) {
    usort($packageFiles, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
    $latest = $packageFiles[0];

    echo '<section>';
    echo '<strong>Результат</strong>';
    echo '<p class="ok">📦 Найден пакет: <code>' . h($latest) . '</code></p>';
    echo '</section>';
} else {
    echo '<section><p class="warn">⚠️ Пакет не найден в <code>' . h($basePath . 'core/packages/') . '</code>.</p></section>';
}

$reportLines[] = 'Exit code: ' . (string) $exitCode;
renderReport($reportLines, $logs);

renderFooter();
