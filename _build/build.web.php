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

function renderHeader(): void
{
    echo "<!doctype html>\n";
    echo "<html lang=\"ru\">\n";
    echo "<head><meta charset=\"utf-8\"><title>ExtraTextAreas: локальная сборка</title>\n";
    echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;color:#222}section{background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px;margin:12px 0}h1{margin-top:0}.ok{color:#0a7d22}.err{color:#b00020}.warn{color:#9a6700}code,pre{background:#fafafa;border:1px solid #eee;border-radius:6px;padding:8px;display:block;overflow:auto}</style></head><body>\n";
    echo "<h1>🔨 Сборка transport-пакета ExtraTextAreas</h1>\n";
}

function renderFooter(): void
{
    echo "</body></html>";
}

renderHeader();

echo '<section>';
echo '<strong>Окружение</strong>';
echo '<p>PHP: <code>' . h(PHP_VERSION) . '</code><br>';
echo 'SAPI: <code>' . h(PHP_SAPI) . '</code><br>';
echo 'Путь к скрипту: <code>' . h(__FILE__) . '</code></p>';
echo '</section>';

if (!is_file($transportScript)) {
    echo '<section><p class="err">❌ Не найден файл сборки: <code>' . h($transportScript) . '</code></p></section>';
    renderFooter();
    exit(1);
}

$inputBasePath = isset($_REQUEST['modx_base_path']) ? trim((string) $_REQUEST['modx_base_path']) : '';
$basePath = $inputBasePath !== '' ? $inputBasePath : $rootDir;
$basePath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
$configCorePath = $basePath . 'config.core.php';

echo '<section>';
echo '<strong>Параметры</strong>';
echo '<p>MODX base path: <code>' . h($basePath) . '</code></p>';
echo '<p>config.core.php: <code>' . h($configCorePath) . '</code></p>';
echo '</section>';

if (!is_file($configCorePath)) {
    echo '<section>';
    echo '<p class="err">❌ Не найден <code>config.core.php</code> в указанном MODX пути.</p>';
    echo '<p>Передайте путь параметром <code>?modx_base_path=/полный/путь/к/modx/</code>.</p>';
    echo '</section>';
    renderFooter();
    exit(1);
}

$command = sprintf(
    'MODX_BASE_PATH=%s %s %s 2>&1',
    escapeshellarg($basePath),
    escapeshellarg(PHP_BINARY),
    escapeshellarg($transportScript)
);

echo '<section>';
echo '<strong>Выполнение</strong>';
echo '<p>Команда:</p><pre>' . h($command) . '</pre>';
echo '</section>';

$descriptor = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($command, $descriptor, $pipes, $rootDir);

if (!is_resource($process)) {
    echo '<section><p class="err">❌ Не удалось запустить процесс сборки (proc_open недоступен).</p></section>';
    renderFooter();
    exit(1);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
fclose($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);
$exitCode = proc_close($process);

echo '<section>';
echo '<strong>Логи build.transport.php</strong>';
echo '<pre>' . h((string) $stdout . (string) $stderr) . '</pre>';
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

renderFooter();
