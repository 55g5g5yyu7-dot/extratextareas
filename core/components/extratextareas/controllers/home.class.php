<?php

class ExtraTextAreasHomeManagerController extends ExtraTextAreasManagerController
{
    public function getPageTitle()
    {
        return $this->modx->lexicon('extratextareas');
    }

    public function loadCustomCssJs()
    {
        $assetsUrl = $this->extratextareas->getConfig()['jsUrl'];
        $this->addJavascript($assetsUrl . 'widgets/fields.grid.js');
        $this->addJavascript($assetsUrl . 'widgets/home.panel.js');
        $this->addLastJavascript($assetsUrl . 'sections/home.js');
    }

    public function getTemplateFile()
    {
        return dirname(__DIR__) . '/templates/home.tpl';
    }
}
