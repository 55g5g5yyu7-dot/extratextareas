(function() {
    if (!window.ExtraTextAreasResourceConfig) {
        return;
    }

    var cfg = window.ExtraTextAreasResourceConfig;
    var contentTab = Ext.getCmp('modx-resource-content');
    if (!contentTab) {
        return;
    }

    var items = [];
    Ext.each(cfg.fields, function(field) {
        items.push({
            xtype: 'fieldset',
            title: field.caption,
            collapsible: true,
            autoHeight: true,
            items: [{
                xtype: 'combo',
                fieldLabel: _('extratextareas.editor'),
                hiddenName: 'eta_editor_' + field.id,
                mode: 'local',
                triggerAction: 'all',
                editable: false,
                valueField: 'value',
                displayField: 'text',
                store: new Ext.data.JsonStore({
                    fields: ['value', 'text'],
                    data: cfg.editors
                }),
                value: field.editor || ''
            }, {
                xtype: 'textarea',
                fieldLabel: field.caption,
                name: 'eta_field_' + field.id,
                anchor: '100%',
                height: 240,
                value: field.content || ''
            }]
        });
    });

    if (items.length) {
        contentTab.add({
            xtype: 'panel',
            title: _('extratextareas.additional_fields'),
            layout: 'form',
            cls: 'main-wrapper',
            autoScroll: true,
            items: items
        });
        contentTab.doLayout();
    }
})();
