<?php

if (!function_exists('extratextareasConnectorFail')) {
    function extratextareasConnectorFail(string $message, int $statusCode = 500): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode([
            'success' => false,
            'message' => $message,
            'object' => [
                'trace' => $message,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

set_exception_handler(static function (Throwable $e): void {
    extratextareasConnectorFail('[extratextareas] connector exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    if (!in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    extratextareasConnectorFail('[extratextareas] connector fatal: ' . $error['message'] . ' @ ' . $error['file'] . ':' . $error['line']);
});

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
    extratextareasConnectorFail('[extratextareas] connector: config.core.php not found', 404);
}

require_once $configCore;

$configInc = MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
if (!is_file($configInc)) {
    extratextareasConnectorFail('[extratextareas] connector: MODX config not found at ' . $configInc);
}

require_once $configInc;
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('extratextareas.core_path', null, $modx->getOption('core_path') . 'components/extratextareas/');
$serviceFile = $corePath . 'src/ExtraTextAreas.php';
if (!is_file($serviceFile)) {
    extratextareasConnectorFail('[extratextareas] connector: service class not found at ' . $serviceFile);
}

require_once $serviceFile;
$extratextareas = new ExtraTextAreas($modx);

$modx->request->handleRequest([
    'processors_path' => $extratextareas->getConfig()['processorsPath'],
    'location' => '',
]);
