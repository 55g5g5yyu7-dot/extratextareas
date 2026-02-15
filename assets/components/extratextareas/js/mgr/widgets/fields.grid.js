ExtraTextAreas.grid.Fields = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'extratextareas-grid-fields',
        url: ExtraTextAreas.config.connectorUrl,
        baseParams: { action: 'mgr/field/getlist' },
        fields: ['id', 'name', 'caption', 'description', 'active', 'rank'],
        paging: true,
        autosave: true,
        columns: [
            { header: _('id'), dataIndex: 'id', width: 40 },
            { header: _('extratextareas.field_name'), dataIndex: 'name', editor: { xtype: 'textfield' }, width: 120 },
            { header: _('extratextareas.field_caption'), dataIndex: 'caption', editor: { xtype: 'textfield' }, width: 200 },
            { header: _('extratextareas.field_description'), dataIndex: 'description', editor: { xtype: 'textfield' }, width: 250 },
            { header: _('extratextareas.field_active'), dataIndex: 'active', editor: { xtype: 'combo-boolean' }, width: 80, renderer: Ext.util.Format.booleanRenderer },
            { header: _('extratextareas.field_rank'), dataIndex: 'rank', editor: { xtype: 'numberfield' }, width: 60 }
        ],
        tbar: [{
            text: _('extratextareas.field_create'),
            handler: this.createField,
            scope: this
        }]
    });

    ExtraTextAreas.grid.Fields.superclass.constructor.call(this, config);
};
Ext.extend(ExtraTextAreas.grid.Fields, MODx.grid.Grid, {
    createField: function(btn, e) {
        var w = MODx.load({
            xtype: 'modx-window',
            title: _('extratextareas.field_create'),
            width: 500,
            url: ExtraTextAreas.config.connectorUrl,
            baseParams: { action: 'mgr/field/create' },
            fields: [
                { xtype: 'textfield', name: 'name', fieldLabel: _('extratextareas.field_name'), anchor: '100%' },
                { xtype: 'textfield', name: 'caption', fieldLabel: _('extratextareas.field_caption'), anchor: '100%' },
                { xtype: 'textfield', name: 'description', fieldLabel: _('extratextareas.field_description'), anchor: '100%' },
                { xtype: 'numberfield', name: 'rank', fieldLabel: _('extratextareas.field_rank'), value: 0 },
                { xtype: 'xcheckbox', name: 'active', inputValue: 1, checked: true, fieldLabel: _('extratextareas.field_active') }
            ],
            listeners: { success: { fn: this.refresh, scope: this } }
        });
        w.show(e.target);
    },

    getMenu: function() {
        var m = [{
            text: _('extratextareas.field_remove'),
            handler: this.removeField,
            scope: this
        }];

        this.addContextMenuItem(m);
        return true;
    },

    removeField: function() {
        MODx.msg.confirm({
            title: _('extratextareas.field_remove'),
            text: _('extratextareas.field_remove_confirm'),
            url: this.config.url,
            params: { action: 'mgr/field/remove', id: this.menu.record.id },
            listeners: { success: { fn: this.refresh, scope: this } }
        });
    }
});
Ext.reg('extratextareas-grid-fields', ExtraTextAreas.grid.Fields);
