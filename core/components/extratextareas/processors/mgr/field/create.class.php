<?php

class ExtraTextAreasFieldCreateProcessor extends modObjectCreateProcessor
{

    public function initialize()
    {
        $corePath = $this->modx->getOption('extratextareas.core_path', null, $this->modx->getOption('core_path') . 'components/extratextareas/');
        require_once $corePath . 'src/ExtraTextAreas.php';
        new ExtraTextAreas($this->modx);

        return parent::initialize();
    }
    public $classKey = ExtraTextAreasField::class;
    public $objectType = 'extratextareas.field';

    public function beforeSet()
    {
        $name = trim((string) $this->getProperty('name'));
        $caption = trim((string) $this->getProperty('caption'));

        if ($name === '' || $caption === '') {
            return $this->modx->lexicon('extratextareas.field_err_required');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            return $this->modx->lexicon('extratextareas.field_err_name_format');
        }

        if ($this->modx->getCount(ExtraTextAreasField::class, ['name' => $name]) > 0) {
            return $this->modx->lexicon('extratextareas.field_err_name_exists');
        }

        $this->setProperty('name', $name);
        $this->setProperty('caption', $caption);
        $this->setProperty('active', (int) (bool) $this->getProperty('active', 0));
        $this->setProperty('rank', (int) $this->getProperty('rank', 0));

        return parent::beforeSet();
    }

    protected function getSaveFailureMessage(): string
    {
        $error = $this->modx->errorInfo();
        $message = $this->modx->lexicon('extratextareas.field_err_save');
        $details = [];

        if (is_array($error)) {
            $sqlState = (string) ($error[0] ?? '');
            $driverCode = (string) ($error[1] ?? '');
            $driverMessage = trim((string) ($error[2] ?? ''));

            $hasRealSqlError = !($sqlState === '00000' && $driverCode === '' && $driverMessage === '');
            if ($hasRealSqlError) {
                $parts = array_filter([$sqlState, $driverCode, $driverMessage], static fn($v) => $v !== '');
                $details[] = implode(' | ', $parts);
            }
        }

        if ($this->object && method_exists($this->object, 'getErrors')) {
            $objectErrors = (array) $this->object->getErrors();
            if (!empty($objectErrors)) {
                $details[] = 'validation: ' . json_encode($objectErrors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        if (empty($details)) {
            $details[] = 'no SQL details; check MODX error log';
        }

        $message .= ' [' . implode(' | ', $details) . ']';

        $this->modx->log(modX::LOG_LEVEL_ERROR,
            '[extratextareas] field create save failed: ' . print_r([
                'errorInfo' => $error,
                'properties' => $this->getProperties(),
                'objectClass' => $this->object ? get_class($this->object) : null,
            ], true)
        );

        return $message;
    }

    public function process()
    {
        $this->object = $this->modx->newObject($this->classKey);
        if (!$this->object) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[extratextareas] field create failed: cannot instantiate class ' . $this->classKey);
            return $this->failure('Model class unavailable: ' . $this->classKey);
        }
        $this->object->fromArray($this->getProperties());

        $beforeSave = $this->beforeSave();
        if ($beforeSave !== true) {
            return $this->failure($beforeSave);
        }

        if (!$this->object->save()) {
            return $this->failure($this->getSaveFailureMessage());
        }

        $this->afterSave();
        return $this->cleanup();
    }

}

return ExtraTextAreasFieldCreateProcessor::class;
