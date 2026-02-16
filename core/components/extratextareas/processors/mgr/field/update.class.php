<?php

class ExtraTextAreasFieldUpdateProcessor extends modObjectUpdateProcessor
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

        $this->setProperty('name', $name);
        $this->setProperty('caption', $caption);

        return parent::beforeSet();
    }
}

return ExtraTextAreasFieldUpdateProcessor::class;
