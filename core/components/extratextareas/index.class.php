<?php

abstract class ExtraTextAreasManagerController extends modExtraManagerController
{
    /** @var ExtraTextAreas */
    protected $extratextareas;

    public function initialize()
    {
        $corePath = $this->modx->getOption('extratextareas.core_path', null, $this->modx->getOption('core_path') . 'components/extratextareas/');
        require_once $corePath . 'src/ExtraTextAreas.php';
        $this->extratextareas = new ExtraTextAreas($this->modx);

        $this->addCss($this->extratextareas->getConfig()['assetsUrl'] . 'css/mgr.css');
        $this->addJavascript($this->extratextareas->getConfig()['jsUrl'] . 'extratextareas.js');
        $this->addHtml('<script>Ext.onReady(function(){ExtraTextAreas.config = ' . $this->modx->toJSON($this->extratextareas->getConfig()) . ';});</script>');

        return parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['extratextareas:default'];
    }

    public function checkPermissions()
    {
        return $this->modx->hasPermission('components');
    }
}

class IndexManagerController extends ExtraTextAreasManagerController
{
    public static function getDefaultController(): string
    {
        return 'home';
    }
}
