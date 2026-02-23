<?php

class ExtraTextAreasFieldGetListProcessor extends modObjectGetListProcessor
{

    public function initialize()
    {
        $corePath = $this->modx->getOption('extratextareas.core_path', null, $this->modx->getOption('core_path') . 'components/extratextareas/');
        require_once $corePath . 'src/ExtraTextAreas.php';
        new ExtraTextAreas($this->modx);

        return parent::initialize();
    }
    public $classKey = ExtraTextAreasField::class;
    public $defaultSortField = 'rank';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = $this->getProperty('query');
        if ($query) {
            $c->where([
                'caption:LIKE' => '%' . $query . '%',
                'OR:name:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }
}

return ExtraTextAreasFieldGetListProcessor::class;
