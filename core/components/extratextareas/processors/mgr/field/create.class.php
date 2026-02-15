<?php

class ExtraTextAreasFieldCreateProcessor extends modObjectCreateProcessor
{
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

return ExtraTextAreasFieldCreateProcessor::class;
