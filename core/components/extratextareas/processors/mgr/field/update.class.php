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
        $id = (int) $this->getProperty('id', 0);

        if ($name === '' || $caption === '') {
            return $this->modx->lexicon('extratextareas.field_err_required');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            return $this->modx->lexicon('extratextareas.field_err_name_format');
        }

        $c = $this->modx->newQuery(ExtraTextAreasField::class);
        $c->where(['name' => $name, 'id:!=' => $id]);
        if ($this->modx->getCount(ExtraTextAreasField::class, $c) > 0) {
            return $this->modx->lexicon('extratextareas.field_err_name_exists');
        }

        $this->setProperty('name', $name);
        $this->setProperty('caption', $caption);
        $this->setProperty('active', (int) (bool) $this->getProperty('active', 0));
        $this->setProperty('rank', (int) $this->getProperty('rank', 0));

        return parent::beforeSet();
    }
}

return ExtraTextAreasFieldUpdateProcessor::class;
