<?php

$corePath = $modx->getOption('extratextareas.core_path', null, $modx->getOption('core_path') . 'components/extratextareas/');
require_once $corePath . 'src/ExtraTextAreas.php';
$service = new ExtraTextAreas($modx);

switch ($modx->event->name) {
    case 'OnDocFormRender':
        if (empty($resource) || !$resource instanceof modResource) {
            return;
        }

        $fields = [];
        foreach ($service->getActiveFields() as $field) {
            $value = $modx->getObject(ExtraTextAreasValue::class, [
                'resource_id' => (int) $resource->get('id'),
                'field_id' => (int) $field->get('id'),
            ]);
            $fields[] = [
                'id' => (int) $field->get('id'),
                'caption' => $field->get('caption'),
                'content' => $value ? (string) $value->get('content') : '',
                'editor' => $value ? (string) $value->get('editor') : '',
            ];
        }

        $config = [
            'resourceId' => (int) $resource->get('id'),
            'fields' => $fields,
            'editors' => $service->detectEditorOptions(),
        ];

        $modx->controller->addLexiconTopic('extratextareas:default');
        $modx->controller->addJavascript($service->getConfig()['jsUrl'] . 'resource/panel.js');
        $modx->controller->addHtml('<script>window.ExtraTextAreasResourceConfig = ' . $modx->toJSON($config) . ';</script>');
        break;

    case 'OnDocFormSave':
        if (empty($resource) || !$resource instanceof modResource) {
            return;
        }

        foreach ($service->getActiveFields() as $field) {
            $id = (int) $field->get('id');
            $content = $_POST['eta_field_' . $id] ?? '';
            $editor = $_POST['eta_editor_' . $id] ?? '';

            $value = $modx->getObject(ExtraTextAreasValue::class, [
                'resource_id' => (int) $resource->get('id'),
                'field_id' => $id,
            ]);

            if (!$value) {
                $value = $modx->newObject(ExtraTextAreasValue::class);
                $value->fromArray([
                    'resource_id' => (int) $resource->get('id'),
                    'field_id' => $id,
                ]);
            }

            $value->fromArray([
                'content' => $content,
                'editor' => $editor,
                'editedon' => date('Y-m-d H:i:s'),
                'editedby' => (int) $modx->user->get('id'),
            ]);
            $value->save();
        }
        break;
}
