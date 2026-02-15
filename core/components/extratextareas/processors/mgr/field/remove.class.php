<?php

class ExtraTextAreasFieldRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = ExtraTextAreasField::class;
    public $objectType = 'extratextareas.field';
}

return ExtraTextAreasFieldRemoveProcessor::class;
