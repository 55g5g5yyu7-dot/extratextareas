<?php

/** @var modX $modx */
if ($object->xpdo) {
    $modx = $object->xpdo;
} else {
    return true;
}

$manager = $modx->getManager();
$modx->addPackage('extratextareas', $modx->getOption('core_path') . 'components/extratextareas/model/');

$manager->createObjectContainer(ExtraTextAreasField::class);
$manager->createObjectContainer(ExtraTextAreasValue::class);

return true;
