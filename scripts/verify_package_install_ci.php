<?php

declare(strict_types=1);

function fail(string $message, int $code = 1): void
{
    fwrite(STDERR, "[verify] {$message}\n");
    exit($code);
}

$root = realpath(__DIR__ . '/..');
if (!$root) {
    fail('Unable to resolve project root.');
}

$modxBase = getenv('MODX_BASE_PATH') ?: ($root . '/.modx-runtime/modx');
$modxBase = rtrim($modxBase, '/');
$configCore = $modxBase . '/config.core.php';
if (!is_file($configCore)) {
    fail('config.core.php not found: ' . $configCore);
}

$distGlob = $root . '/dist/extratextareas-*.transport.zip';
$packages = glob($distGlob) ?: [];
if (!$packages) {
    fail('No built package found by pattern: ' . $distGlob);
}

usort($packages, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
$packageFile = $packages[0];
$signature = basename($packageFile, '.transport.zip');

echo "[verify] MODX base: {$modxBase}\n";
echo "[verify] package: {$packageFile}\n";
echo "[verify] signature: {$signature}\n";

require_once $configCore;
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->getService('error', 'error.modError');

$corePackages = rtrim((string) $modx->getOption('core_path'), '/') . '/packages';
if (!is_dir($corePackages) && !mkdir($corePackages, 0775, true) && !is_dir($corePackages)) {
    fail('Cannot create core packages directory: ' . $corePackages);
}

$targetPackage = $corePackages . '/' . basename($packageFile);
if (!copy($packageFile, $targetPackage)) {
    fail('Cannot copy package into MODX core/packages: ' . $targetPackage);
}

echo "[verify] copied package to {$targetPackage}\n";

$scanResponse = $modx->runProcessor('workspace/packages/scanlocal', [
    'workspace' => 1,
]);
if (!$scanResponse || $scanResponse->isError()) {
    $msg = $scanResponse ? ($scanResponse->getMessage() ?: 'scanlocal failed') : 'scanlocal returned empty response';
    $payload = $scanResponse ? print_r($scanResponse->getResponse(), true) : '';
    fail('Scan local packages failed: ' . $msg . "\n" . $payload);
}

echo "[verify] local package scan: OK\n";

$response = $modx->runProcessor('workspace/packages/install', [
    'signature' => $signature,
]);

if (!$response) {
    fail('workspace/packages/install returned empty response.');
}

if ($response->isError()) {
    $msg = $response->getMessage() ?: 'unknown install error';
    $payload = print_r($response->getResponse(), true);
    fail('Install processor failed: ' . $msg . "\n" . $payload);
}

echo "[verify] install response: OK\n";

$corePath = (string) $modx->getOption('extratextareas.core_path', null, $modx->getOption('core_path') . 'components/extratextareas/');
require_once $corePath . 'src/ExtraTextAreas.php';
new ExtraTextAreas($modx);

$checks = [];

$checks[] = ['package loaded', (bool) $modx->addPackage('extratextareas', $corePath . 'model/')];
$checks[] = ['class file field exists', is_file($corePath . 'model/extratextareas/extratextareasfield.class.php')];
$checks[] = ['class file value exists', is_file($corePath . 'model/extratextareas/extratextareasvalue.class.php')];
$checks[] = ['newObject field works', $modx->newObject('ExtraTextAreasField') !== null];
$checks[] = ['newObject value works', $modx->newObject('ExtraTextAreasValue') !== null];

$prefix = (string) $modx->getOption('table_prefix', null, 'modx_');
foreach (['extratextareas_fields', 'extratextareas_values'] as $table) {
    $tableName = $prefix . $table;
    $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($tableName));
    $ok = $stmt && $stmt->fetchColumn();
    $checks[] = ["table exists {$tableName}", (bool) $ok];
}

$field = $modx->newObject('ExtraTextAreasField');
if (!$field) {
    fail('Cannot instantiate ExtraTextAreasField for CRUD test.');
}

$testName = 'ci_test_' . time();
$field->fromArray([
    'name' => $testName,
    'caption' => 'CI test field',
    'description' => 'Created by CI verification',
    'active' => 1,
    'rank' => 999,
]);
$saveOk = $field->save();
$checks[] = ['create field record', (bool) $saveOk];

if ($saveOk) {
    $removeOk = $field->remove();
    $checks[] = ['remove field record', (bool) $removeOk];
}

$failed = false;
foreach ($checks as [$name, $ok]) {
    echo sprintf('[verify] %s %s', $ok ? '✅' : '❌', $name) . "\n";
    if (!$ok) {
        $failed = true;
    }
}

if ($failed) {
    fail('One or more verification checks failed.');
}

echo "[verify] all verification checks passed\n";
