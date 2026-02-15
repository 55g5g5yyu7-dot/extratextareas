<?php

use xPDO\Transport\xPDOTransport;

require_once dirname(__DIR__) . '/core/model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');

$builder = new modPackageBuilder($modx);
$builder->createPackage('extratextareas', '1.0.0', 'beta');
$builder->registerNamespace('extratextareas', false, true, '{core_path}components/extratextareas/');
$builder->setPackageAttributes([
    'license' => 'GPLv2+',
    'readme' => 'Extra text areas for MODX 3 resources.',
]);

$category = $modx->newObject('modCategory');
$category->set('category', 'ExtraTextAreas');

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
    $event->fromArray(['event' => $eventName, 'priority' => 0, 'propertyset' => 0], '', true, true);
    $events[] = $event;
}
$plugin->addMany($events);
$category->addMany([$plugin], 'Plugins');

$menu = $modx->newObject('modMenu');
$menu->fromArray([
    'text' => 'extratextareas',
    'description' => 'extratextareas.menu_desc',
    'action' => 'home',
    'namespace' => 'extratextareas',
    'parent' => 'components',
], '', true, true);

$action = $modx->newObject('modAction');
$action->fromArray([
    'namespace' => 'extratextareas',
    'controller' => 'home',
    'haslayout' => 1,
    'lang_topics' => 'extratextareas:default',
    'assets' => '',
], '', true, true);

$vehicle = $builder->createVehicle($category, [
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Plugins' => [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => false,
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
                ],
            ],
        ],
    ],
]);

$vehicle->resolve('file', [
    'source' => dirname(__DIR__) . '/core/components/extratextareas',
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
$vehicle->resolve('file', [
    'source' => dirname(__DIR__) . '/assets/components/extratextareas',
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);
$vehicle->resolve('php', ['source' => dirname(__DIR__) . '/_build/resolvers/resolve.tables.php']);

$builder->putVehicle($vehicle);

$menuVehicle = $builder->createVehicle($menu, [
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
]);
$builder->putVehicle($menuVehicle);

$actionVehicle = $builder->createVehicle($action, [
    xPDOTransport::UNIQUE_KEY => ['namespace', 'controller'],
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
]);
$builder->putVehicle($actionVehicle);

$builder->pack();
echo "Package built\n";
