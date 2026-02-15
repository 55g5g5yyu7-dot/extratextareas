<?php

/** @var modX $modx */
require_once dirname(__DIR__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('extratextareas.core_path', null, $modx->getOption('core_path') . 'components/extratextareas/');
require_once $corePath . 'src/ExtraTextAreas.php';
$extratextareas = new ExtraTextAreas($modx);

$modx->request->handleRequest([
    'processors_path' => $extratextareas->getConfig()['processorsPath'],
    'location' => '',
]);
