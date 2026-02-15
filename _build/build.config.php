<?php

/**
 * Bootstrap MODX for package build.
 *
 * Usage:
 *   MODX_BASE_PATH=/var/www/site php _build/build.transport.php
 */

$modxBasePath = getenv('MODX_BASE_PATH');
if (!$modxBasePath) {
    $modxBasePath = dirname(__DIR__);
}

$modxBasePath = rtrim($modxBasePath, '/\\') . DIRECTORY_SEPARATOR;
$configCore = $modxBasePath . 'config.core.php';

if (!is_file($configCore)) {
    fwrite(STDERR, "[extratextareas] config.core.php not found. Set MODX_BASE_PATH to your MODX root.\n");
    exit(1);
}

require_once $configCore;
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
