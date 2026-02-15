<?php

class ExtraTextAreasFieldGetListProcessor extends modObjectGetListProcessor
{
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
