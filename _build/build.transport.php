<?php

use xPDO\Transport\xPDOTransport;

require_once __DIR__ . '/build.config.php';

$packageName = 'extratextareas';
$packageVersion = '1.0.1';
$packageRelease = 'pl';

$builder = new modPackageBuilder($modx);
$builder->createPackage($packageName, $packageVersion, $packageRelease);
$builder->registerNamespace($packageName, false, true, '{core_path}components/' . $packageName . '/');
$builder->setPackageAttributes([
    'license' => 'GPLv2+',
    'readme' => file_get_contents(dirname(__DIR__) . '/README.md'),
    'changelog' => "1.0.1-pl\n- Improved build bootstrap and installer readiness.\n",
]);

$namespace = $modx->newObject('modNamespace');
$namespace->fromArray([
    'name' => $packageName,
    'path' => '{core_path}components/' . $packageName . '/',
    'assets_path' => '{assets_path}components/' . $packageName . '/',
], '', true, true);

$category = $modx->newObject('modCategory');
$category->fromArray(['category' => 'ExtraTextAreas'], '', true, true);

$plugin = $modx->newObject('modPlugin');
$plugin->fromArray([
    'name' => 'ExtraTextAreas',
    'description' => 'Injects and persists extra text areas in resource form.',
    'plugincode' => file_get_contents(dirname(__DIR__) . '/core/components/extratextareas/elements/plugins/extratextareas.plugin.php'),
    'disabled' => 0,
], '', true, true);

$events = [];
foreach (['OnDocFormRender', 'OnDocFormSave'] as $eventName) {
    $event = $modx->newObject('modPluginEvent');
    $event->fromArray([
        'event' => $eventName,
        'priority' => 0,
        'propertyset' => 0,
    ], '', true, true);
    $events[] = $event;
}
$plugin->addMany($events, 'PluginEvents');
$category->addMany([$plugin], 'Plugins');

$action = $modx->newObject('modAction');
$action->fromArray([
    'namespace' => $packageName,
    'controller' => 'home',
    'haslayout' => 1,
    'lang_topics' => 'extratextareas:default',
], '', true, true);

$menu = $modx->newObject('modMenu');
$menu->fromArray([
    'text' => 'extratextareas',
    'description' => 'extratextareas.menu_desc',
    'action' => 'home',
    'parent' => 'components',
    'namespace' => $packageName,
], '', true, true);

$namespaceVehicle = $builder->createVehicle($namespace, [
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
]);
$builder->putVehicle($namespaceVehicle);

$categoryVehicle = $builder->createVehicle($category, [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Plugins' => [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                ],
            ],
        ],
    ],
]);

$categoryVehicle->resolve('file', [
    'source' => dirname(__DIR__) . '/core/components/extratextareas',
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
$categoryVehicle->resolve('file', [
    'source' => dirname(__DIR__) . '/assets/components/extratextareas',
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);
$categoryVehicle->resolve('php', [
    'source' => dirname(__DIR__) . '/_build/resolvers/resolve.tables.php',
]);
$builder->putVehicle($categoryVehicle);

$actionVehicle = $builder->createVehicle($action, [
    xPDOTransport::UNIQUE_KEY => ['namespace', 'controller'],
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
]);
$builder->putVehicle($actionVehicle);

$menuVehicle = $builder->createVehicle($menu, [
    xPDOTransport::UNIQUE_KEY => ['text', 'parent', 'namespace'],
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
]);
$builder->putVehicle($menuVehicle);

if (!$builder->pack()) {
    fwrite(STDERR, "[extratextareas] Build failed.\n");
    exit(1);
}

echo "[extratextareas] Package created: {$packageName}-{$packageVersion}-{$packageRelease}.transport.zip\n";
