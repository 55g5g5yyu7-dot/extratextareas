<?php

/** @var modX $modx */
if (!isset($object) || !$object->xpdo) {
    return true;
}

$modx = $object->xpdo;
$prefix = (string) $modx->getOption('table_prefix', null, 'modx_');

$fieldTable = $prefix . 'extratextareas_fields';
$valueTable = $prefix . 'extratextareas_values';

$queries = [
    "CREATE TABLE IF NOT EXISTS `{$fieldTable}` (\n"
    . "  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
    . "  `name` VARCHAR(191) NOT NULL DEFAULT '',\n"
    . "  `caption` VARCHAR(255) NOT NULL DEFAULT '',\n"
    . "  `description` TEXT NULL,\n"
    . "  `active` TINYINT(1) NOT NULL DEFAULT 1,\n"
    . "  `rank` INT(10) NOT NULL DEFAULT 0,\n"
    . "  PRIMARY KEY (`id`),\n"
    . "  UNIQUE KEY `name` (`name`)\n"
    . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS `{$valueTable}` (\n"
    . "  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
    . "  `resource_id` INT(10) NOT NULL DEFAULT 0,\n"
    . "  `field_id` INT(10) NOT NULL DEFAULT 0,\n"
    . "  `content` MEDIUMTEXT NULL,\n"
    . "  `editor` VARCHAR(190) NOT NULL DEFAULT '',\n"
    . "  `editedon` DATETIME NULL,\n"
    . "  `editedby` INT(10) NOT NULL DEFAULT 0,\n"
    . "  PRIMARY KEY (`id`),\n"
    . "  UNIQUE KEY `resource_field` (`resource_id`,`field_id`)\n"
    . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($queries as $sql) {
    if (!$modx->exec($sql)) {
        $error = $modx->errorInfo();
        $modx->log(modX::LOG_LEVEL_ERROR, '[extratextareas] Resolver SQL failed: ' . $sql . ' ERROR: ' . print_r($error, true));
        return false;
    }
}

return true;
