<?php

/** @var modX $modx */
$baseDirCandidates = [
    dirname(__DIR__, 3),
    dirname(__DIR__, 4),
];

$configCore = null;
foreach ($baseDirCandidates as $baseDir) {
    $candidate = $baseDir . '/config.core.php';
    if (is_file($candidate)) {
        $configCore = $candidate;
        break;
    }
}

if ($configCore === null) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'message' => '[extratextareas] connector: config.core.php not found',
    ]);
    exit;
}

require_once $configCore;
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('extratextareas.core_path', null, $modx->getOption('core_path') . 'components/extratextareas/');
require_once $corePath . 'src/ExtraTextAreas.php';
$extratextareas = new ExtraTextAreas($modx);

$modx->request->handleRequest([
    'processors_path' => $extratextareas->getConfig()['processorsPath'],
    'location' => '',
]);
