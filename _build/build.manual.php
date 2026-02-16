<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('memory_limit', '512M');
set_time_limit(0);

$rootDir = dirname(__DIR__);
$runtimeDir = $rootDir . '/.modx-runtime-manual';
$distDir = $rootDir . '/dist';
$modxDir = $runtimeDir . '/modx';
$packageGlob = $distDir . '/extratextareas-*.transport.zip';

$mysqlUser = getenv('MYSQL_USER') ?: 'modx';
$mysqlPassword = getenv('MYSQL_PASSWORD') ?: 'modx';
$mysqlDatabase = getenv('MYSQL_DATABASE') ?: 'modx';
$mysqlHost = getenv('MYSQL_HOST') ?: '127.0.0.1';

function runCommand(string $command, string $workdir): void
{
    echo "\n$command\n";

    $descriptor = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptor, $pipes, $workdir);
    if (!is_resource($process)) {
        throw new RuntimeException("Unable to start process: {$command}");
    }

    fclose($pipes[0]);

    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    if ($stdout !== false && $stdout !== '') {
        echo $stdout;
    }
    if ($stderr !== false && $stderr !== '') {
        fwrite(STDERR, $stderr);
    }

    if ($exitCode !== 0) {
        throw new RuntimeException("Command failed with code {$exitCode}: {$command}");
    }
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if ($items === false) {
        throw new RuntimeException("Unable to read directory: {$dir}");
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path) && !is_link($path)) {
            rrmdir($path);
        } elseif (!unlink($path)) {
            throw new RuntimeException("Unable to remove file: {$path}");
        }
    }

    if (!rmdir($dir)) {
        throw new RuntimeException("Unable to remove directory: {$dir}");
    }
}

echo "[extratextareas] Manual standalone transport build\n";
echo "[extratextareas] Root: {$rootDir}\n";
echo "[extratextareas] Using MySQL host={$mysqlHost} db={$mysqlDatabase} user={$mysqlUser}\n";

if (!is_dir($distDir) && !mkdir($distDir, 0775, true) && !is_dir($distDir)) {
    throw new RuntimeException("Unable to create dist directory: {$distDir}");
}

rrmdir($runtimeDir);
if (!mkdir($runtimeDir, 0775, true) && !is_dir($runtimeDir)) {
    throw new RuntimeException("Unable to create runtime directory: {$runtimeDir}");
}

runCommand('composer create-project modx/revolution modx --no-interaction --quiet', $runtimeDir);

runCommand(
    sprintf(
        'mysql -h %s -u%s -p%s -e %s',
        escapeshellarg($mysqlHost),
        escapeshellarg($mysqlUser),
        escapeshellarg($mysqlPassword),
        escapeshellarg("CREATE DATABASE IF NOT EXISTS {$mysqlDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
    ),
    $modxDir
);

$installCommand = sprintf(
    'php setup/cli-install.php --mode=new --database_server=%s --database=%s --database_user=%s --database_password=%s --table_prefix=modx_ --language=en --cmsadmin=admin --cmspassword=admin1234 --cmsadminemail=admin@example.com --context_web_url=/ --context_mgr_url=/manager/ --context_connectors_url=/connectors/ --remove_setup_directory=0',
    escapeshellarg($mysqlHost),
    escapeshellarg($mysqlDatabase),
    escapeshellarg($mysqlUser),
    escapeshellarg($mysqlPassword)
);
runCommand($installCommand, $modxDir);

runCommand(
    sprintf('MODX_BASE_PATH=%s %s %s',
        escapeshellarg($modxDir),
        escapeshellarg(PHP_BINARY),
        escapeshellarg($rootDir . '/_build/build.transport.php')
    ),
    $rootDir
);

$packages = glob($modxDir . '/core/packages/extratextareas-*.transport.zip');
if ($packages === false || $packages === []) {
    throw new RuntimeException('No package produced in runtime MODX core/packages directory');
}

foreach ($packages as $package) {
    $target = $distDir . '/' . basename($package);
    if (!copy($package, $target)) {
        throw new RuntimeException("Unable to copy package to dist: {$target}");
    }
}

$distPackages = glob($packageGlob);
if ($distPackages === false || $distPackages === []) {
    throw new RuntimeException('No package found in dist after copy');
}

usort($distPackages, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
$latest = $distPackages[0];
$latestTarget = $distDir . '/extratextareas-latest.transport.zip';
if (!copy($latest, $latestTarget)) {
    throw new RuntimeException("Unable to update latest package: {$latestTarget}");
}

echo "[extratextareas] Built package: {$latest}\n";
echo "[extratextareas] Updated latest: {$latestTarget}\n";
