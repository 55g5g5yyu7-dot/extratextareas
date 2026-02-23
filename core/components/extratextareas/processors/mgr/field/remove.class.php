<?php

class ExtraTextAreasFieldRemoveProcessor extends modObjectRemoveProcessor
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
}

return ExtraTextAreasFieldRemoveProcessor::class;
