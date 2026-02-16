<?php

use xPDO\Transport\xPDOTransport;

require_once __DIR__ . '/build.config.php';

$packageName = 'extratextareas';
$packageVersion = '1.0.2';
$packageRelease = 'pl';

$coreSource = dirname(__DIR__) . '/core/components/extratextareas';
$assetsSource = dirname(__DIR__) . '/assets/components/extratextareas';
$pluginFile = $coreSource . '/elements/plugins/extratextareas.plugin.php';
$readmeFile = dirname(__DIR__) . '/README.md';
$resolverFile = __DIR__ . '/resolvers/resolve.tables.php';

function writeBuildError(string $message): void
{
    if (defined('STDERR')) {
        fwrite(STDERR, $message);
        return;
    }

    $stderr = @fopen('php://stderr', 'wb');
    if (is_resource($stderr)) {
        fwrite($stderr, $message);
        fclose($stderr);
        return;
    }

    echo $message;
}

function buildFail(string $message): void
{
    writeBuildError($message . "\n");
    exit(1);
}

function createObjectOrFail($modx, array $classes, string $label)
{
    foreach ($classes as $class) {
        $object = $modx->newObject($class);
        if ($object) {
            return $object;
        }
    }

    $supported = implode(', ', $classes);
    buildFail("[extratextareas] Build failed: unable to create {$label}. Tried: {$supported}");
}

foreach ([$coreSource, $assetsSource, $pluginFile, $readmeFile, $resolverFile] as $path) {
    if (!file_exists($path)) {
        buildFail("[extratextareas] Build failed: path not found: {$path}");
    }
}

$builderClass = class_exists('MODX\\Revolution\\Transport\\modPackageBuilder')
    ? 'MODX\\Revolution\\Transport\\modPackageBuilder'
    : 'modPackageBuilder';

$builder = new $builderClass($modx);
$builder->createPackage($packageName, $packageVersion, $packageRelease);
$builder->registerNamespace($packageName, false, true, '{core_path}components/' . $packageName . '/');
$builder->setPackageAttributes([
    'license' => 'GPLv2+',
    'readme' => file_get_contents($readmeFile),
    'changelog' => "1.0.2-pl\n- Build script hardening and clearer installer workflow.\n",
]);

$namespace = createObjectOrFail($modx, ['modNamespace', 'MODX\\Revolution\\modNamespace'], 'namespace');
$namespace->fromArray([
    'name' => $packageName,
    'path' => '{core_path}components/' . $packageName . '/',
    'assets_path' => '{assets_path}components/' . $packageName . '/',
], '', true, true);

$category = createObjectOrFail($modx, ['modCategory', 'MODX\\Revolution\\modCategory'], 'category');
$category->fromArray(['category' => 'ExtraTextAreas'], '', true, true);

$plugin = createObjectOrFail($modx, ['modPlugin', 'MODX\\Revolution\\modPlugin'], 'plugin');
$plugin->fromArray([
    'name' => 'ExtraTextAreas',
    'description' => 'Injects and persists extra text areas in resource form.',
    'plugincode' => file_get_contents($pluginFile),
    'disabled' => 0,
], '', true, true);

$events = [];
foreach (['OnDocFormRender', 'OnDocFormSave'] as $eventName) {
    $event = createObjectOrFail($modx, ['modPluginEvent', 'MODX\\Revolution\\modPluginEvent'], 'plugin event ' . $eventName);
    $event->fromArray([
        'event' => $eventName,
        'priority' => 0,
        'propertyset' => 0,
    ], '', true, true);
    $events[] = $event;
}
$plugin->addMany($events, 'PluginEvents');
$plugins = [$plugin];
$category->addMany($plugins, 'Plugins');

$action = createObjectOrFail($modx, ['modAction', 'MODX\\Revolution\\modAction'], 'action');
$action->fromArray([
    'namespace' => $packageName,
    'controller' => 'home',
    'haslayout' => 1,
    'lang_topics' => 'extratextareas:default',
], '', true, true);

$menu = createObjectOrFail($modx, ['modMenu', 'MODX\\Revolution\\modMenu'], 'menu');
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
    'source' => $coreSource,
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
$categoryVehicle->resolve('file', [
    'source' => $assetsSource,
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);
$categoryVehicle->resolve('php', ['source' => $resolverFile]);
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
    buildFail('[extratextareas] Build failed at pack() stage.');
}

echo "[extratextareas] Package created: {$packageName}-{$packageVersion}-{$packageRelease}.transport.zip\n";
