<?php

class ExtraTextAreasDiagnosticsRunProcessor extends modProcessor
{
    public function process()
    {
        $corePath = $this->modx->getOption('extratextareas.core_path', null, $this->modx->getOption('core_path') . 'components/extratextareas/');
        require_once $corePath . 'src/ExtraTextAreas.php';
        new ExtraTextAreas($this->modx);

        $log = [];
        $ok = true;

        $add = static function (array &$log, string $title, bool $state, string $details = ''): void {
            $icon = $state ? '✅' : '❌';
            $line = "{$icon} {$title}";
            if ($details !== '') {
                $line .= " — {$details}";
            }
            $log[] = $line;
        };

        $version = $this->modx->getVersionData();
        $add($log, 'MODX initialized', true, 'version ' . ($version['full_version'] ?? 'unknown'));

        $packageOk = $this->modx->addPackage('extratextareas', $corePath . 'model/');
        $add($log, 'Package map loaded', (bool) $packageOk, $corePath . 'model/');
        if (!$packageOk) {
            $ok = false;
        }

        $fieldClassExists = class_exists('ExtraTextAreasField');
        $valueClassExists = class_exists('ExtraTextAreasValue');
        $add($log, 'Class ExtraTextAreasField available', $fieldClassExists);
        $add($log, 'Class ExtraTextAreasValue available', $valueClassExists);
        if (!$fieldClassExists || !$valueClassExists) {
            $ok = false;
        }

        $prefix = (string) $this->modx->getOption('table_prefix', null, 'modx_');
        $fieldTable = $prefix . 'extratextareas_fields';
        $valueTable = $prefix . 'extratextareas_values';

        $manager = $this->modx->getManager();
        $fieldTableExists = $manager->tableExists($fieldTable);
        $valueTableExists = $manager->tableExists($valueTable);
        $add($log, 'Table exists: ' . $fieldTable, $fieldTableExists);
        $add($log, 'Table exists: ' . $valueTable, $valueTableExists);
        if (!$fieldTableExists || !$valueTableExists) {
            $ok = false;
        }

        try {
            $count = (int) $this->modx->getCount('ExtraTextAreasField');
            $add($log, 'Fields query', true, 'count=' . $count);
        } catch (Throwable $e) {
            $add($log, 'Fields query', false, $e->getMessage());
            $ok = false;
        }

        $processorFiles = [
            'getlist' => $corePath . 'processors/mgr/field/getlist.class.php',
            'create' => $corePath . 'processors/mgr/field/create.class.php',
            'update' => $corePath . 'processors/mgr/field/update.class.php',
            'remove' => $corePath . 'processors/mgr/field/remove.class.php',
        ];
        foreach ($processorFiles as $name => $file) {
            $exists = is_file($file);
            $add($log, 'Processor file: ' . $name, $exists, $file);
            if (!$exists) {
                $ok = false;
            }
        }

        $log[] = '';
        $log[] = $ok ? 'ИТОГ: все ключевые проверки прошли.' : 'ИТОГ: есть ошибки, смотрите строки с ❌.';

        return $this->success('', [
            'ok' => $ok,
            'log' => implode("\n", $log),
        ]);
    }
}

return ExtraTextAreasDiagnosticsRunProcessor::class;
