<?php

class ExtraTextAreas
{
    public const PKG_NAME = 'extratextareas';
    public const VERSION = '1.0.0';

    /** @var modX */
    protected $modx;

    /** @var array */
    protected $config = [];

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $corePath = $modx->getOption('extratextareas.core_path', $config, $modx->getOption('core_path') . 'components/extratextareas/');
        $assetsUrl = $modx->getOption('extratextareas.assets_url', $config, $modx->getOption('assets_url') . 'components/extratextareas/');

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/mgr/',
            'connectorUrl' => $assetsUrl . 'connector.php',
        ], $config);

        $this->modx->addPackage('extratextareas', $this->config['modelPath']);
        $this->modx->lexicon->load('extratextareas:default');
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getActiveFields(): array
    {
        $c = $this->modx->newQuery(ExtraTextAreasField::class);
        $c->where(['active' => 1]);
        $c->sortby('rank', 'ASC');
        $c->sortby('id', 'ASC');

        return $this->modx->getCollection(ExtraTextAreasField::class, $c);
    }

    public function detectEditorOptions(): array
    {
        $options = [
            ['value' => '', 'text' => $this->modx->lexicon('extratextareas.editor_plain')],
        ];

        $plugins = $this->modx->getCollection('modPlugin', ['disabled' => 0]);
        foreach ($plugins as $plugin) {
            $name = trim((string) $plugin->get('name'));
            if (!$name) {
                continue;
            }
            if (stripos($name, 'editor') === false && stripos($name, 'ace') === false && stripos($name, 'tiny') === false) {
                continue;
            }

            $options[] = [
                'value' => $name,
                'text' => $name,
            ];
        }

        return array_values(array_unique($options, SORT_REGULAR));
    }
}
