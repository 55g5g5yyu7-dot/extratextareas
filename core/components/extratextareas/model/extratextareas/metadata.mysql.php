<?php

$xpdo_meta_map = [
    'ExtraTextAreasField' => [
        'package' => 'extratextareas',
        'version' => '1.1',
        'table' => 'extratextareas_fields',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'fields' => [
            'name' => '',
            'caption' => '',
            'description' => '',
            'active' => 1,
            'rank' => 0,
        ],
        'fieldMeta' => [
            'name' => ['dbtype' => 'varchar', 'precision' => '191', 'phptype' => 'string', 'null' => false, 'default' => ''],
            'caption' => ['dbtype' => 'varchar', 'precision' => '255', 'phptype' => 'string', 'null' => false, 'default' => ''],
            'description' => ['dbtype' => 'text', 'phptype' => 'string', 'null' => true],
            'active' => ['dbtype' => 'tinyint', 'precision' => '1', 'phptype' => 'boolean', 'null' => false, 'default' => 1],
            'rank' => ['dbtype' => 'int', 'precision' => '10', 'phptype' => 'integer', 'null' => false, 'default' => 0],
        ],
        'indexes' => [
            'name' => ['alias' => 'name', 'primary' => false, 'unique' => true, 'type' => 'BTREE', 'columns' => ['name' => ['length' => '', 'collation' => 'A', 'null' => false]]],
        ],
        'aggregates' => [
            'Values' => ['class' => 'ExtraTextAreasValue', 'local' => 'id', 'foreign' => 'field_id', 'cardinality' => 'many', 'owner' => 'local'],
        ],
    ],
    'ExtraTextAreasValue' => [
        'package' => 'extratextareas',
        'version' => '1.1',
        'table' => 'extratextareas_values',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'fields' => [
            'resource_id' => 0,
            'field_id' => 0,
            'content' => '',
            'editor' => '',
            'editedon' => null,
            'editedby' => 0,
        ],
        'fieldMeta' => [
            'resource_id' => ['dbtype' => 'int', 'precision' => '10', 'phptype' => 'integer', 'null' => false, 'default' => 0],
            'field_id' => ['dbtype' => 'int', 'precision' => '10', 'phptype' => 'integer', 'null' => false, 'default' => 0],
            'content' => ['dbtype' => 'mediumtext', 'phptype' => 'string', 'null' => true],
            'editor' => ['dbtype' => 'varchar', 'precision' => '190', 'phptype' => 'string', 'null' => false, 'default' => ''],
            'editedon' => ['dbtype' => 'datetime', 'phptype' => 'datetime', 'null' => true],
            'editedby' => ['dbtype' => 'int', 'precision' => '10', 'phptype' => 'integer', 'null' => false, 'default' => 0],
        ],
        'indexes' => [
            'resource_field' => ['alias' => 'resource_field', 'primary' => false, 'unique' => true, 'type' => 'BTREE', 'columns' => [
                'resource_id' => ['length' => '', 'collation' => 'A', 'null' => false],
                'field_id' => ['length' => '', 'collation' => 'A', 'null' => false],
            ]],
        ],
        'composites' => [
            'Field' => ['class' => 'ExtraTextAreasField', 'local' => 'field_id', 'foreign' => 'id', 'cardinality' => 'one', 'owner' => 'foreign'],
        ],
    ],
];
