ExtraTextAreas.grid.Fields = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'extratextareas-grid-fields',
        url: ExtraTextAreas.config.connectorUrl,
        baseParams: { action: 'mgr/field/getlist' },
        save_action: 'mgr/field/update',
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
        listeners: {
            render: {
                fn: function(grid) {
                    grid.getStore().on('exception', function(proxy, type, action, options, response) {
                        var body = response && response.responseText ? response.responseText : _('error');
                        MODx.msg.alert(_('error'), body);
                    });
                },
                scope: this
            }
        },
        tbar: [{
            text: _('extratextareas.field_create'),
            handler: this.createField,
            scope: this
        }, '-', {
            text: _('extratextareas.diagnostics_run'),
            handler: this.runDiagnostics,
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

    runDiagnostics: function() {
        MODx.Ajax.request({
            url: this.config.url,
            params: { action: 'mgr/diagnostics/run' },
            listeners: {
                success: {
                    fn: function(r) {
                        var log = r && r.object && r.object.log ? r.object.log : _('error');
                        MODx.msg.alert(_('extratextareas.diagnostics_title'), '<textarea readonly style=\"width:100%;min-height:320px;font-family:monospace\">' + Ext.util.Format.htmlEncode(log) + '</textarea>');
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        var log = r && r.message ? r.message : _('error');
                        MODx.msg.alert(_('extratextareas.diagnostics_title'), log);
                    },
                    scope: this
                }
            }
        });
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
